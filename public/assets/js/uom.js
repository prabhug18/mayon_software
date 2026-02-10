window.Uom = (function(){
    function showAlert(message) {
        const body = document.getElementById('successModalBody');
        if (body) {
            body.textContent = message;
            const modal = new bootstrap.Modal(document.getElementById('successModal'));
            modal.show();
            setTimeout(() => { const m = bootstrap.Modal.getInstance(document.getElementById('successModal')); if (m) m.hide(); }, 1400);
        } else { alert(message); }
    }

    function initList(tableSelector) {
        const table = $(tableSelector).DataTable({
            ajax: { url: '/uoms', dataSrc: 'data' },
            columns: [ { data: 'id' }, { data: 'name' }, { data: null, orderable: false, render: function(row) {
                return `
                    <a href="/uoms/${row.id}/edit" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil-square"></i></a>
                    <button class="btn btn-sm btn-outline-danger" onclick="Uom.confirmDelete(${row.id})"><i class="bi bi-trash"></i></button>
                `;
            }}]
        });
        window.UomTable = table;
    }

    let deleteTargetId = null;

    function confirmDelete(id) {
        deleteTargetId = id;
        const modalEl = document.getElementById('deleteModal');
        if (modalEl) {
            let name = '';
            try { if (window.UomTable) { const rows = window.UomTable.rows().data(); for (let i=0;i<rows.length;i++) if (rows[i].id==id) { name = rows[i].name; break; } } } catch(e){}
            const body = modalEl.querySelector('.modal-body'); if (body) body.textContent = name ? `Are you sure you want to delete "${name}"?` : 'Are you sure you want to delete this item?';
            try { const modal = new bootstrap.Modal(modalEl); modal.show(); } catch(e) { if (confirm(body.textContent||'Are you sure?')) performDelete(); }
        } else { if (confirm('Are you sure you want to delete this item?')) performDelete(); }
    }

    document.addEventListener('DOMContentLoaded', function(){ const btn = document.getElementById('deleteModalConfirmBtn'); if (btn) btn.addEventListener('click', function(){ performDelete(); }); });

    function performDelete() {
        if (!deleteTargetId) return;
        const modalEl = document.getElementById('deleteModal'); const modal = bootstrap.Modal.getInstance(modalEl); if (modal) modal.hide();
        fetch(`/uoms/${deleteTargetId}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '', 'Accept':'application/json' } })
        .then(r=>r.json()).then(data=>{ showAlert(data.message || 'Deleted'); if (window.UomTable) window.UomTable.ajax.reload(); deleteTargetId = null; }).catch(()=>{ showAlert('Error deleting item'); });
    }

    function clearErrors() { document.querySelectorAll('.invalid-feedback').forEach(el=>el.textContent=''); document.querySelectorAll('.is-invalid').forEach(el=>el.classList.remove('is-invalid')); }

    function initForm(formSelector) {
        const form = document.querySelector(formSelector); if (!form) return;
        form.addEventListener('submit', function(e){ e.preventDefault(); clearErrors(); const name = form.querySelector('[name="name"]').value.trim(); if (!name) { const err = document.getElementById('name-error'); if (err) err.textContent = 'UOM name is required.'; const inp = form.querySelector('[name="name"]'); if (inp) inp.classList.add('is-invalid'); return; }
            fetch('/uoms/check-name', { method: 'POST', headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')||'', 'Accept':'application/json' }, body: JSON.stringify({ name }) })
            .then(r=>r.json()).then(resp=>{
                if (resp.exists) {
                    if (resp.trashed) {
                        const restoreModal = new bootstrap.Modal(document.getElementById('restoreModal'));
                        document.getElementById('restoreModalBody').textContent = 'A UOM named "' + name + '" exists in Trash. Restore it?';
                        const restoreBtn = document.getElementById('restoreModalRestoreBtn');
                        restoreBtn.onclick = function(){ fetch('/uoms/' + resp.id + '/restore', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')||'', 'Accept':'application/json' } }).then(r=>r.json()).then(data=>{ restoreModal.hide(); showAlert(data.message || 'Restored'); setTimeout(()=>{ window.location.href = '/uoms'; }, 800); }).catch(()=>{ showAlert('Error restoring'); }); };
                        restoreModal.show(); return;
                    } else { const err = document.getElementById('name-error'); if (err) err.textContent = 'UOM name already exists.'; const inp = form.querySelector('[name="name"]'); if (inp) inp.classList.add('is-invalid'); return; }
                }
                const fd = new FormData(form);
                fetch('/uoms', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')||'', 'Accept':'application/json' }, body: fd })
                .then(r=>r.json()).then(data=>{ if (data.status === 'success') { showAlert(data.message||'UOM created'); setTimeout(()=>window.location.href='/uoms',900); } else if (data.errors) { for (const k in data.errors) { const err = document.getElementById(k + '-error'); if (err) err.textContent = data.errors[k][0]; const inp = form.querySelector('[name="'+k+'"]'); if (inp) inp.classList.add('is-invalid'); } } }).catch(()=>{ showAlert('An error occurred'); });
            }).catch(()=>{ showAlert('Error validating name'); });
        });
    }

    function initEditForm(formSelector, id) {
        const form = document.querySelector(formSelector); if (!form) return;
        form.addEventListener('submit', function(e){ e.preventDefault(); clearErrors(); const name = form.querySelector('[name="name"]').value.trim(); if (!name) { const err = document.getElementById('name-error'); if (err) err.textContent = 'UOM name is required.'; const inp = form.querySelector('[name="name"]'); if (inp) inp.classList.add('is-invalid'); return; }
            fetch('/uoms/check-name', { method: 'POST', headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')||'', 'Accept':'application/json' }, body: JSON.stringify({ name, exclude_id: id }) })
            .then(r=>r.json()).then(resp=>{
                if (resp.exists) {
                    if (resp.trashed) { const restoreModal = new bootstrap.Modal(document.getElementById('restoreModal')); document.getElementById('restoreModalBody').textContent = 'A UOM named "' + name + '" exists in Trash. Restore it?'; const restoreBtn = document.getElementById('restoreModalRestoreBtn'); restoreBtn.onclick = function(){ fetch('/uoms/' + resp.id + '/restore', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')||'', 'Accept':'application/json' } }).then(r=>r.json()).then(data=>{ restoreModal.hide(); showAlert(data.message || 'Restored'); setTimeout(()=>{ window.location.href = '/uoms'; }, 800); }).catch(()=>{ showAlert('Error restoring'); }); }; restoreModal.show(); return; }
                    else { const err = document.getElementById('name-error'); if (err) err.textContent = 'UOM name already exists.'; const inp = form.querySelector('[name="name"]'); if (inp) inp.classList.add('is-invalid'); return; }
                }
                const fd = new FormData(form);
                fetch(`/uoms/${id}`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')||'', 'Accept':'application/json', 'X-HTTP-Method-Override':'PUT' }, body: fd })
                .then(r=>r.json()).then(data=>{ if (data.status === 'success') { showAlert(data.message || 'Updated'); setTimeout(()=>{ window.location.href = '/uoms'; }, 900); } else if (data.errors) { for (const k in data.errors) { const err = document.getElementById(k + '-error'); if (err) err.textContent = data.errors[k][0]; const inp = form.querySelector('[name="'+k+'"]'); if (inp) inp.classList.add('is-invalid'); } } }).catch(()=>{ showAlert('An error occurred'); });
            }).catch(()=>{ showAlert('Error validating name'); });
        });
    }

    return { initList, initForm, initEditForm, confirmDelete, performDelete };
})();
