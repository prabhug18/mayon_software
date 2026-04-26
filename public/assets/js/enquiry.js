window.Enquiry = (function () {
    let deleteTargetId = null;
    function initList(selector) {
        const table = $(selector).DataTable({
            ajax: { url: '/enquiries', dataSrc: 'data' },
            columns: [
                { 
                    data: null,
                    render: function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                { 
                    data: null,
                    render: function (row) {
                        return `<strong>${row.name}</strong><br><small class="text-muted">${row.mobile || ''}</small>`;
                    }
                },
                { 
                    data: null,
                    render: function (row) {
                        const s = (row.service && row.service.name) || '-';
                        const i = (row.service_item && row.service_item.item_name) || '';
                        return `${s}${i ? '<br><small class="text-muted">' + i + '</small>' : ''}`;
                    }
                },
                { 
                    data: 'priority',
                    render: function (p) {
                        let cls = 'bg-info text-dark';
                        if (p === 'Urgent') cls = 'bg-danger';
                        else if (p === 'High') cls = 'bg-warning text-dark';
                        return `<span class="badge ${cls} extra-small">${p || 'Medium'}</span>`;
                    }
                },
                { 
                    data: 'status',
                    render: function (s) {
                        let cls = 'bg-secondary';
                        if (s === 'Won') cls = 'bg-success';
                        else if (s === 'Lost') cls = 'bg-danger';
                        else if (s === 'Open') cls = 'bg-primary';
                        return `<span class="badge ${cls} extra-small">${s || 'Open'}</span>`;
                    }
                },
                { 
                    data: null,
                    render: function (row) {
                        return (row.assigned_to && row.assigned_to.name) || '<span class="text-muted small">Unassigned</span>';
                    }
                },
                { 
                    data: 'next_follow_up_at',
                    render: function (dt) {
                        if (!dt) return '-';
                        try { const d = new Date(dt); return d.toLocaleString([], {dateStyle: 'medium', timeStyle: 'short'}); } catch (e) { return dt; }
                    }
                },
                {
                    data: null, orderable: false, render: function (data) {
                        return `<div class="d-flex gap-1 justify-content-center">
                            <a href="/enquiries/${data.id}" class="btn btn-sm btn-outline-info" title="View Details"><i class="bi bi-eye"></i></a>
                            <a href="/enquiries/${data.id}/edit" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                            <button class="btn btn-sm btn-outline-danger" onclick="Enquiry.confirmDelete(${data.id})" title="Delete"><i class="bi bi-trash"></i></button>
                        </div>`;
                    }
                }
            ]
        });
        window.EnquiryTable = table;
    }

    function initForm(selector) {
        const form = document.querySelector(selector); if (!form) return;

        const serviceSelect = form.querySelector('#service_id');
        const serviceItemSelect = form.querySelector('#service_item_id');
        if (serviceSelect && serviceItemSelect) {
            serviceSelect.addEventListener('change', function () {
                const serviceId = this.value;
                if (!serviceId) return;
                fetch(`/quotations/service-items?service_id=${serviceId}`)
                    .then(r => r.json())
                    .then(resp => {
                        serviceItemSelect.innerHTML = '<option selected disabled>Select Service Item</option>';
                        (resp.data || []).forEach(item => {
                            serviceItemSelect.innerHTML += `<option value="${item.id}">${item.item_name}</option>`;
                        });
                    });
            });
        }

        form.addEventListener('submit', function (e) {
            e.preventDefault(); clearErrors(); const fd = new FormData(form);
            const name = fd.get('name');
            fetch('/enquiries/check-name', { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '', 'Accept': 'application/json' }, body: JSON.stringify({ name }) })
                .then(r => r.json()).then(resp => {
                    if (resp.exists && resp.trashed) {
                        const el = document.getElementById('name-error'); if (el) el.textContent = 'An enquiry with this name already exists.'; const inp = document.getElementById('name'); if (inp) inp.classList.add('is-invalid'); return;
                    }
                    fetch('/enquiries', { method: 'POST', credentials: 'same-origin', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '', 'Accept': 'application/json' }, body: fd })
                        .then(r => { if (r.status == 422) return r.json().then(x => { throw { validation: x }; }); return r.json(); })
                        .then(d => { showAlert(d.message || 'Created'); setTimeout(() => { window.location.href = '/enquiries'; }, 900); })
                        .catch(err => { if (err.validation && err.validation.errors) { const errors = err.validation.errors; for (const k in errors) { const el = document.getElementById(k + '-error'); if (el) el.textContent = errors[k][0]; const inp = document.getElementById(k); if (inp) inp.classList.add('is-invalid'); } } else showAlert('Error saving enquiry'); });
                }).catch(() => { showAlert('Error checking name'); });
        });
    }

    function initEditForm(selector, id) {
        const form = document.querySelector(selector); if (!form) return;

        const serviceSelect = form.querySelector('#service_id');
        const serviceItemSelect = form.querySelector('#service_item_id');
        if (serviceSelect && serviceItemSelect) {
            const loadItems = (serviceId, selectedId = null) => {
                if (!serviceId) return;
                fetch(`/quotations/service-items?service_id=${serviceId}`)
                    .then(r => r.json())
                    .then(resp => {
                        serviceItemSelect.innerHTML = '<option disabled>Select Service Item</option>';
                        (resp.data || []).forEach(item => {
                            const sel = selectedId == item.id ? 'selected' : '';
                            serviceItemSelect.innerHTML += `<option value="${item.id}" ${sel}>${item.item_name}</option>`;
                        });
                    });
            };

            serviceSelect.addEventListener('change', function () { loadItems(this.value); });
            if (serviceSelect.value) {
                loadItems(serviceSelect.value, serviceItemSelect.dataset.selected);
            }
        }

        form.addEventListener('submit', function (e) {
            e.preventDefault(); clearErrors(); const fd = new FormData(form);
            fetch(`/enquiries/${id}`, { method: 'POST', credentials: 'same-origin', headers: { 'X-HTTP-Method-Override': 'PUT', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '', 'Accept': 'application/json' }, body: fd })
                .then(r => { if (r.status == 422) return r.json().then(x => { throw { validation: x }; }); return r.json(); })
                .then(d => { showAlert(d.message || 'Updated'); setTimeout(() => { window.location.href = '/enquiries'; }, 900); })
                .catch(err => { if (err.validation && err.validation.errors) { const errors = err.validation.errors; for (const k in errors) { const el = document.getElementById(k + '-error'); if (el) el.textContent = errors[k][0]; const inp = document.getElementById(k); if (inp) inp.classList.add('is-invalid'); } } else showAlert('Error updating enquiry'); });
        });
    }

    function confirmDelete(id) { deleteTargetId = id; new bootstrap.Modal(document.getElementById('deleteModal')).show(); document.getElementById('deleteModalConfirmBtn').onclick = performDelete; }
    
    // Alias for confirmDelete to match show page
    function deleteEnquiry(id) { confirmDelete(id); }

    function performDelete() { if (!deleteTargetId) return; const modalEl = document.getElementById('deleteModal'); const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl); if (modal) modal.hide(); fetch(`/enquiries/${deleteTargetId}`, { method: 'DELETE', credentials: 'same-origin', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' } }).then(r => r.json()).then(data => { showAlert(data.message || 'Deleted'); if (window.location.pathname.includes('/enquiries/')) { setTimeout(() => { window.location.href = '/enquiries'; }, 900); } else if (window.EnquiryTable) { window.EnquiryTable.ajax.reload(); } deleteTargetId = null; }).catch(() => { showAlert('Error deleting item'); }); }

    function clearErrors() { document.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = ''); document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid')); }

    function initShow(id) {
        if (!id) return;

        // Wire comment form
        const form = document.getElementById('comment-form');
        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault(); const body = document.getElementById('comment-body').value.trim(); if (!body) return; fetch(`/enquiries/${id}/comments`, { method: 'POST', credentials: 'same-origin', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '', 'Accept': 'application/json', 'Content-Type': 'application/json' }, body: JSON.stringify({ body }) })
                    .then(function (r) { if (r.status == 422) return r.json().then(x => { throw { validation: x }; }); if (!r.ok) return r.text().then(t => { throw new Error('HTTP ' + r.status + ': ' + t); }); return r.json(); })
                    .then(function (d) {
                        showAlert('Comment added');
                        document.getElementById('comment-body').value = '';
                        // Refresh to show in comments tab or just reload
                        location.reload(); 
                    })
                    .catch(function (err) { if (err && err.validation && err.validation.errors) { const errors = err.validation.errors; for (const k in errors) { const el = document.getElementById(k + '-error'); if (el) el.textContent = errors[k][0]; const inp = document.getElementById(k); if (inp) inp.classList.add('is-invalid'); } } else { showAlert('Error posting comment'); } });
            });
        }

        // Wire follow-up form
        const fuForm = document.getElementById('followup-form');
        if (fuForm) {
            fuForm.addEventListener('submit', function (e) {
                e.preventDefault();
                const scheduled = document.getElementById('followup-datetime').value;
                if (!scheduled) return showAlert('Please select a date and time');
                const notes = document.getElementById('followup-notes').value || '';
                fetch(`/enquiries/${id}/follow-ups`, {
                    method: 'POST', credentials: 'same-origin', headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'Accept': 'application/json', 'Content-Type': 'application/json'
                    }, body: JSON.stringify({ scheduled_at: scheduled, notes: notes })
                })
                    .then(function (r) { if (r.status == 422) return r.json().then(x => { throw { validation: x }; }); if (!r.ok) return r.text().then(t => { throw new Error('HTTP ' + r.status + ': ' + t); }); return r.json(); })
                    .then(function (d) {
                        showAlert('Follow-up updated');
                        setTimeout(() => location.reload(), 900);
                    })
                    .catch(function (err) { showAlert('Error updating follow-up'); });
            });
        }
    }

    return { initList, initForm, initEditForm, confirmDelete, deleteEnquiry, initShow };
})();

