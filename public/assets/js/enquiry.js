window.Enquiry = (function(){
    let deleteTargetId = null;
    function initList(selector){
        const table = $(selector).DataTable({
            ajax: { url: '/enquiries', dataSrc: 'data' },
            columns: [
                { data: 'id' },
                { data: 'mobile' },
                { data: 'name' },
                { // project column - defensive render
                    data: null,
                    render: function(row){
                        return (row.project && row.project.name) || (row.project_name) || '';
                    }
                },
                { // next follow-up
                    data: 'next_follow_up_at',
                    render: function(dt){
                        if (!dt) return '-';
                        try { const d = new Date(dt); return d.toLocaleString(); } catch(e){ return dt; }
                    }
                },
                { // enquiry type column - defensive render
                    data: null,
                    render: function(row){
                        return (row.enquiryType && row.enquiryType.name) || (row.enquiry_type && row.enquiry_type.name) || '';
                    }
                },
                { data: 'status' },
                { data: null, orderable: false, render: function(data,row){
                    return `<a href="/enquiries/${data.id}/edit" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>
                    <button class="btn btn-sm btn-outline-danger" onclick="Enquiry.confirmDelete(${data.id})"><i class="bi bi-trash"></i></button>`;
                }}
            ]
        });
        window.EnquiryTable = table;
    }

    function initForm(selector){
        const form = document.querySelector(selector); if (!form) return;
        form.addEventListener('submit', function(e){
            e.preventDefault(); clearErrors(); const fd = new FormData(form);
            const name = fd.get('name');
            // check name
            fetch('/enquiries/check-name', { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')||'', 'Accept':'application/json' }, body: JSON.stringify({ name }) })
            .then(r=>r.json()).then(resp=>{
                if (resp.exists && resp.trashed) { // show validation only (no restore flow)
                    const el = document.getElementById('name-error'); if (el) el.textContent = 'An enquiry with this name already exists.'; const inp = document.getElementById('name'); if (inp) inp.classList.add('is-invalid'); return;
                }
                fetch('/enquiries', { method: 'POST', credentials: 'same-origin', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')||'', 'Accept':'application/json' }, body: fd })
                .then(r=>{ if (r.status==422) return r.json().then(x=>{ throw { validation: x }; }); return r.json(); })
                .then(d=>{ showAlert(d.message||'Created'); setTimeout(()=>{ window.location.href='/enquiries'; },900); })
                .catch(err=>{ if (err.validation && err.validation.errors) { const errors = err.validation.errors; for (const k in errors) { const el = document.getElementById(k + '-error'); if (el) el.textContent = errors[k][0]; const inp = document.getElementById(k); if (inp) inp.classList.add('is-invalid'); } } else showAlert('Error saving enquiry'); });
            }).catch(()=>{ showAlert('Error checking name'); });
        });
    }

    function initEditForm(selector, id){
        const form = document.querySelector(selector); if (!form) return;
        form.addEventListener('submit', function(e){ e.preventDefault(); clearErrors(); const fd = new FormData(form);
            fetch(`/enquiries/${id}`, { method: 'POST', credentials: 'same-origin', headers: { 'X-HTTP-Method-Override': 'PUT', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')||'', 'Accept':'application/json' }, body: fd })
            .then(r=>{ if (r.status==422) return r.json().then(x=>{ throw { validation: x }; }); return r.json(); })
            .then(d=>{ showAlert(d.message||'Updated'); setTimeout(()=>{ window.location.href='/enquiries'; },900); })
            .catch(err=>{ if (err.validation && err.validation.errors) { const errors = err.validation.errors; for (const k in errors) { const el = document.getElementById(k + '-error'); if (el) el.textContent = errors[k][0]; const inp = document.getElementById(k); if (inp) inp.classList.add('is-invalid'); } } else showAlert('Error updating enquiry'); });
        });
    }

    function confirmDelete(id){ deleteTargetId = id; new bootstrap.Modal(document.getElementById('deleteModal')).show(); document.getElementById('deleteModalConfirmBtn').onclick = performDelete; }

    function performDelete(){ if (!deleteTargetId) return; const modalEl = document.getElementById('deleteModal'); const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl); if (modal) modal.hide(); fetch(`/enquiries/${deleteTargetId}`, { method: 'DELETE', credentials: 'same-origin', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept':'application/json' } }).then(r=>r.json()).then(data=>{ showAlert(data.message||'Deleted'); if (window.EnquiryTable) window.EnquiryTable.ajax.reload(); deleteTargetId = null; }).catch(()=>{ showAlert('Error deleting item'); }); }

    function clearErrors(){ document.querySelectorAll('.invalid-feedback').forEach(el=>el.textContent=''); document.querySelectorAll('.is-invalid').forEach(el=>el.classList.remove('is-invalid')); }

    // Show view initializer: load enquiry details and comments, wire comment form
    function initShow(id){
        if (!id) return;
        // If the server has already rendered values into the page, skip the AJAX fetch
        // to avoid overwriting content or triggering a login redirect that navigates away.
        var skipFetch = false;
        try {
            var nameEl = document.getElementById('enq-name');
            if (nameEl && nameEl.textContent && nameEl.textContent.trim() !== '' && nameEl.textContent.trim() !== 'Enquiry Details') {
                skipFetch = true; // server-rendered content exists — skip the remote fetch but still wire form
            }
        } catch (e) {}

        if (!skipFetch) {
            fetch(`/enquiries/${id}`, { credentials: 'same-origin', headers: { 'Accept':'application/json' } })
                .then(function(r){
                    if (r.status === 401) {
                        try { showAlert('Session expired. Redirecting to login...'); } catch(e){}
                        setTimeout(function(){ window.location.href = '/login'; }, 900);
                        throw new Error('Unauthenticated');
                    }
                    if (!r.ok) return r.text().then(function(t){ throw new Error('HTTP ' + r.status + ': ' + t); });
                    return r.json().catch(function(){ return r.text().then(function(t){ throw new Error('Invalid JSON response: ' + t); }); });
                })
                .then(function(resp){
                    const data = resp.data || resp;
                    try { document.getElementById('enq-name').textContent = data.name || ''; } catch(e){}
                    try { document.getElementById('enq-mobile').textContent = data.mobile || ''; } catch(e){}
                    try { document.getElementById('enq-location').textContent = data.location || ''; } catch(e){}
                    try { document.getElementById('enq-type').textContent = data.enquiryType?.name || ''; } catch(e){}
                    try { document.getElementById('enq-project').textContent = data.project?.name || ''; } catch(e){}
                    try { document.getElementById('enq-status').textContent = data.status || ''; } catch(e){}
                    try { document.getElementById('enq-source').textContent = data.source?.name || ''; } catch(e){}
                    try { document.getElementById('enq-description').textContent = data.description || ''; } catch(e){}
                    try { document.getElementById('enq-next-follow').textContent = data.next_follow_up_at ? new Date(data.next_follow_up_at).toLocaleString() : '-'; } catch(e){}
                    try { document.getElementById('enq-reminder-notes').textContent = data.reminder_notes || '-'; } catch(e){}

                    // render comments
                    const list = document.getElementById('comments-list'); if (list) list.innerHTML = '';
                    (data.comments || []).forEach(c=>{
                        const el = document.createElement('div'); el.className = 'card mb-2 p-2';
                        el.innerHTML = `<div class="small text-muted">${c.user?.name || 'System'} &middot; ${new Date(c.created_at).toLocaleString()}</div><div class="mt-1">${c.body}</div>`;
                        if (list) list.appendChild(el);
                    });
                })
                .catch(()=>{ showAlert('Unable to load enquiry'); });
        }

        const form = document.getElementById('comment-form');
        if (form){
            form.addEventListener('submit', function(e){ e.preventDefault(); const body = document.getElementById('comment-body').value.trim(); if (!body) return; fetch(`/enquiries/${id}/comments`, { method: 'POST', credentials: 'same-origin', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')||'', 'Accept':'application/json', 'Content-Type':'application/json' }, body: JSON.stringify({ body }) })
                .then(function(r){ if (r.status==422) return r.json().then(x=>{ throw { validation: x }; }); if (!r.ok) return r.text().then(t=>{ throw new Error('HTTP ' + r.status + ': ' + t); }); return r.json(); })
                .then(function(d){
                    showAlert('Comment added');
                    document.getElementById('comment-body').value = '';
                    // append the new comment to the list from the response (d.data)
                    try {
                        const comment = d.data || d;
                        const list = document.getElementById('comments-list');
                        if (list && comment) {
                            const el = document.createElement('div'); el.className = 'card mb-2 p-2';
                            const author = (comment.user && comment.user.name) || 'You';
                            const time = comment.created_at ? new Date(comment.created_at).toLocaleString() : new Date().toLocaleString();
                            el.innerHTML = `<div class="small text-muted">${author} &middot; ${time}</div><div class="mt-1">${(comment.body)||''}</div>`;
                            list.insertBefore(el, list.firstChild);
                        }
                    } catch (e) { if (window.Enquiry && typeof Enquiry.initShow === 'function') Enquiry.initShow(id); }
                })
                .catch(function(err){ console.error('Comment post error:', err); if (err && err.validation && err.validation.errors) { const errors = err.validation.errors; for (const k in errors) { const el = document.getElementById(k + '-error'); if (el) el.textContent = errors[k][0]; const inp = document.getElementById(k); if (inp) inp.classList.add('is-invalid'); } } else { showAlert('Error posting comment: ' + (err.message || 'unknown')); } });
            });
        }

        // follow-up form: allow adding next follow-up from the show page
        const fuForm = document.getElementById('followup-form');
        if (fuForm) {
            fuForm.addEventListener('submit', function(e){
                e.preventDefault();
                const scheduled = document.getElementById('followup-datetime').value;
                if (!scheduled) return showAlert('Please select a date and time');
                const notes = document.getElementById('followup-notes').value || '';
                fetch(`/enquiries/${id}/follow-ups`, {
                    method: 'POST', credentials: 'same-origin', headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')||'',
                        'Accept':'application/json', 'Content-Type':'application/json'
                    }, body: JSON.stringify({ scheduled_at: scheduled, notes: notes })
                })
                .then(function(r){ if (r.status==422) return r.json().then(x=>{ throw { validation: x }; }); if (!r.ok) return r.text().then(t=>{ throw new Error('HTTP ' + r.status + ': ' + t); }); return r.json(); })
                .then(function(d){
                    showAlert('Follow-up added');
                    const f = d.data || d;
                    // update next follow-up display
                    try { document.getElementById('enq-next-follow').textContent = f.scheduled_at ? new Date(f.scheduled_at).toLocaleString() : '-'; } catch(e){}
                    // prepend to follow-ups list (match Blade list-group markup)
                    try {
                        const list = document.getElementById('followups-list');
                        if (list && f) {
                                const el = document.createElement('div');
                                el.className = 'list-group-item py-2';
                                const author = (f.user && f.user.name) || 'You';
                                const time = f.scheduled_at ? new Date(f.scheduled_at).toLocaleString() : new Date().toLocaleString();
                                el.setAttribute('data-scheduled', f.scheduled_at || new Date().toISOString());
                                el.setAttribute('data-user', author);
                                el.setAttribute('data-notes', (f.notes||'').toString());
                                el.innerHTML = `<div class="d-flex justify-content-between align-items-center"><div class="fw-semibold small">${time}</div><div class="small text-muted">${author}</div></div>` +
                                               `<div class="text-muted mt-1 small">${(f.notes)||''}</div>`;
                                // If the list had a single 'No follow-ups' placeholder, remove it
                                const first = list.firstElementChild;
                                if (first && first.textContent && first.textContent.trim() === 'No follow-ups') list.removeChild(first);

                                // avoid duplicate: check existing items for same scheduled + user + notes
                                let duplicate = false;
                                const newTs = new Date(el.dataset.scheduled).getTime();
                                for (const child of Array.from(list.children)) {
                                    const cs = child.dataset && child.dataset.scheduled ? new Date(child.dataset.scheduled).getTime() : null;
                                    const cu = child.dataset && child.dataset.user ? child.dataset.user : '';
                                    const cn = child.dataset && child.dataset.notes ? child.dataset.notes : '';
                                    if (cs !== null && cs === newTs && cu === author && cn === (f.notes||'')) { duplicate = true; break; }
                                }
                                if (duplicate) return;

                                // insert in descending order by scheduled datetime
                                let inserted = false;
                                for (const child of Array.from(list.children)) {
                                    const cs = child.dataset && child.dataset.scheduled ? new Date(child.dataset.scheduled).getTime() : null;
                                    if (cs === null) continue;
                                    if (cs < newTs) { list.insertBefore(el, child); inserted = true; break; }
                                }
                                if (!inserted) list.appendChild(el);
                        }
                    } catch (e) {}
                    // update quick info last follow-up
                    try { const lastEl = document.querySelector('.quick-last-follow'); if (lastEl) lastEl.textContent = f.scheduled_at ? new Date(f.scheduled_at).toLocaleString() : '-'; } catch(e){}
                    document.getElementById('followup-datetime').value = '';
                    document.getElementById('followup-notes').value = '';
                })
                .catch(function(err){ console.error('Follow-up post error:', err); if (err && err.validation && err.validation.errors) { const errors = err.validation.errors; showAlert('Validation error'); } else { showAlert('Error adding follow-up: ' + (err.message||'unknown')); } });
            });
        }
    }

    return { initList, initForm, initEditForm, confirmDelete, initShow };
})();
