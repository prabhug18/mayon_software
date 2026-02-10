window.Project = (function(){
    let deleteTargetId = null;
    function initList(selector){
        const table = $(selector).DataTable({
            ajax: { url: '/projects', dataSrc: 'data' },
            columns: [
                { data: 'id' },
                { data: 'name' },
                { data: 'location' },
                { data: 'status' },
                { data: null, orderable: false, render: function(data,row){
                    return `<a href="/projects/${data.id}/edit" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>
                    <button class="btn btn-sm btn-outline-danger" onclick="Project.confirmDelete(${data.id})"><i class="bi bi-trash"></i></button>`;
                }}
            ]
        });
        window.ProjectTable = table;
    }

    function initForm(selector){
        const form = document.querySelector(selector);
        if (!form) return;

        // preview handler for file input in create
        const previewInput = form.querySelector('#logo_image');
        if (previewInput) {
            previewInput.addEventListener('change', function(){
                const file = this.files && this.files[0];
                const container = document.getElementById('logo_preview_container');
                const img = document.getElementById('logo_preview_img');
                if (file && img && container) {
                    const url = URL.createObjectURL(file);
                    img.src = url; img.style.display = ''; container.style.display = '';
                } else if (img && container) { img.src = ''; img.style.display = 'none'; container.style.display = 'none'; }
            });
        }

        form.addEventListener('submit', function(e){
            e.preventDefault();
            clearErrors();
            const fd = new FormData(form);
            const name = fd.get('name');
            const exclude_id = fd.get('exclude_id') || null;

            // check name first
            fetch('/projects/check-name', { method: 'POST', headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')||'', 'Accept': 'application/json' }, body: JSON.stringify({ name, exclude_id }) })
            .then(r=>r.json()).then(data=>{
                if (data.exists && data.trashed) {
                    // show restore modal
                    const body = document.getElementById('restoreModalBody');
                    body.textContent = 'A project with this name exists in Trash. Would you like to restore it?';
                    const restoreBtn = document.getElementById('restoreModalRestoreBtn');
                    restoreBtn.onclick = function(){
                        const restoreModal = bootstrap.Modal.getInstance(document.getElementById('restoreModal')) || new bootstrap.Modal(document.getElementById('restoreModal'));
                        fetch(`/projects/${data.id}/restore`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')||'', 'Accept': 'application/json' } }).then(r=>r.json()).then(d=>{ restoreModal.hide(); showAlert(d.message||'Restored'); setTimeout(()=>{ location.href='/projects'; }, 900); }).catch(()=>{ showAlert('Error restoring'); });
                    };
                    new bootstrap.Modal(document.getElementById('restoreModal')).show();
                    return;
                }

                // submit
                fetch('/projects', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')||'', 'Accept': 'application/json' }, body: fd })
                .then(r=>{ if (r.status==422) return r.json().then(x=>{ throw { validation: x }; }); return r.json(); })
                .then(d=>{ showAlert(d.message||'Created'); setTimeout(()=>{ window.location.href='/projects'; }, 900); })
                .catch(err=>{ if (err.validation && err.validation.errors) { const errors = err.validation.errors; for (const k in errors) { const el = document.getElementById(k + '-error'); if (el) el.textContent = errors[k][0]; const inp = document.getElementById(k); if (inp) inp.classList.add('is-invalid'); } } else showAlert('Error saving project'); });
            }).catch(()=>{ showAlert('Error checking name'); });
        });
    }

    function initEditForm(selector, id){
        const form = document.querySelector(selector);
        if (!form) return;

        // preview handler for file input in edit
        const previewInput = form.querySelector('#logo_image');
        if (previewInput) {
            previewInput.addEventListener('change', function(){
                const file = this.files && this.files[0];
                const container = document.getElementById('logo_preview_container');
                const img = document.getElementById('logo_preview_img');
                if (file && img && container) {
                    const url = URL.createObjectURL(file);
                    img.src = url; img.style.display = ''; container.style.display = '';
                }
            });
        }
        form.addEventListener('submit', function(e){
            e.preventDefault();
            clearErrors();
            const fd = new FormData(form);
            fetch(`/projects/${id}`, { method: 'POST', headers: { 'X-HTTP-Method-Override': 'PUT', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')||'', 'Accept': 'application/json' }, body: fd })
            .then(r=>{ if (r.status==422) return r.json().then(x=>{ throw { validation: x }; }); return r.json(); })
            .then(d=>{ showAlert(d.message||'Updated'); setTimeout(()=>{ window.location.href='/projects'; }, 900); })
            .catch(err=>{ if (err.validation && err.validation.errors) { const errors = err.validation.errors; for (const k in errors) { const el = document.getElementById(k + '-error'); if (el) el.textContent = errors[k][0]; const inp = document.getElementById(k); if (inp) inp.classList.add('is-invalid'); } } else showAlert('Error updating project'); });
        });
    }

    function confirmDelete(id){ deleteTargetId = id; new bootstrap.Modal(document.getElementById('deleteModal')).show(); document.getElementById('deleteModalConfirmBtn').onclick = performDelete; }

    function performDelete(){ if (!deleteTargetId) return; const modalEl = document.getElementById('deleteModal'); const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl); if (modal) modal.hide(); fetch(`/projects/${deleteTargetId}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' } }).then(r=>r.json()).then(data=>{ showAlert(data.message||'Deleted'); if (window.ProjectTable) window.ProjectTable.ajax.reload(); deleteTargetId = null; }).catch(()=>{ showAlert('Error deleting item'); }); }

    function clearErrors(){ document.querySelectorAll('.invalid-feedback').forEach(el=>el.textContent=''); document.querySelectorAll('.is-invalid').forEach(el=>el.classList.remove('is-invalid')); }

    return { initList, initForm, initEditForm, confirmDelete };
})();
