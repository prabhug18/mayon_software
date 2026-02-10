window.Supplier = (function(){
    let deleteTargetId = null;

    function initList(selector){
        const table = $(selector).DataTable({ ajax: { url: '/suppliers', dataSrc: 'data' }, columns: [ { data: 'id' }, { data: 'name' }, { data: 'contact_person' }, { data: 'mobile' }, { data: 'logo', render: function(d){ return d ? '<img src="'+window.location.origin+'/'+d+'" style="max-height:40px;"/>' : ''; } }, { data: null, orderable:false, render: function(r){ return `<a href="/suppliers/${r.id}/edit" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a><button class="btn btn-sm btn-outline-danger" onclick="Supplier.confirmDelete(${r.id})"><i class="bi bi-trash"></i></button>`; } } ] });
        window.SupplierTable = table;
    }

    function clearErrors(){ document.querySelectorAll('.invalid-feedback').forEach(el=>el.textContent=''); document.querySelectorAll('.is-invalid').forEach(el=>el.classList.remove('is-invalid')); }

    function initForm(selector){
        const form = document.querySelector(selector); if (!form) return;

        const previewInput = form.querySelector('#logo');
        if (previewInput){ previewInput.addEventListener('change', function(){ const f = this.files && this.files[0]; const container = document.getElementById('logo_preview_container'); const img = document.getElementById('logo_preview_img'); if (f && img && container){ const url = URL.createObjectURL(f); img.src = url; img.style.display = ''; container.style.display = ''; } }); }

        form.addEventListener('submit', function(e){ e.preventDefault(); clearErrors(); const fd = new FormData(form); const name = fd.get('name');
            fetch('/suppliers/check-name', { method:'POST', headers:{ 'Content-Type':'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')||'', 'Accept':'application/json' }, body: JSON.stringify({ name }) })
            .then(r=>r.json()).then(resp=>{ if (resp.exists){ const err = document.getElementById('name-error'); if (err) err.textContent = 'Supplier name already exists.'; const inp = form.querySelector('[name="name"]'); if (inp) inp.classList.add('is-invalid'); return; }
                fetch('/suppliers', { method:'POST', headers:{ 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')||'', 'Accept':'application/json' }, body: fd })
                .then(r=>{ if (r.status==422) return r.json().then(x=>{ throw { validation: x }; }); return r.json(); })
                .then(d=>{ showAlert(d.message||'Supplier created'); setTimeout(()=>{ window.location.href = '/suppliers'; }, 900); })
                .catch(err=>{ if (err.validation && err.validation.errors){ for (const k in err.validation.errors){ const el = document.getElementById(k+'-error'); if (el) el.textContent = err.validation.errors[k][0]; const inp = form.querySelector('[name="'+k+'"]'); if (inp) inp.classList.add('is-invalid'); } } else showAlert('Error saving supplier'); });
            }).catch(()=>{ showAlert('Error validating name'); });
        });
    }

    function initEditForm(selector, id){ const form = document.querySelector(selector); if (!form) return;
        const previewInput = form.querySelector('#logo');
        if (previewInput){ previewInput.addEventListener('change', function(){ const f = this.files && this.files[0]; const container = document.getElementById('logo_preview_container'); const img = document.getElementById('logo_preview_img'); if (f && img && container){ const url = URL.createObjectURL(f); img.src = url; img.style.display = ''; container.style.display = ''; } }); }
        form.addEventListener('submit', function(e){ e.preventDefault(); clearErrors(); const fd = new FormData(form); fetch(`/suppliers/${id}`, { method:'POST', headers:{ 'X-HTTP-Method-Override':'PUT', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')||'', 'Accept':'application/json' }, body: fd }).then(r=>{ if (r.status==422) return r.json().then(x=>{ throw { validation: x }; }); return r.json(); }).then(d=>{ showAlert(d.message||'Supplier updated'); setTimeout(()=>{ window.location.href = '/suppliers'; }, 900); }).catch(err=>{ if (err.validation && err.validation.errors){ for (const k in err.validation.errors){ const el = document.getElementById(k+'-error'); if (el) el.textContent = err.validation.errors[k][0]; const inp = form.querySelector('[name="'+k+'"]'); if (inp) inp.classList.add('is-invalid'); } } else showAlert('Error updating supplier'); }); }); }

    function confirmDelete(id){ deleteTargetId = id; new bootstrap.Modal(document.getElementById('deleteModal')).show(); document.getElementById('deleteModalConfirmBtn').onclick = performDelete; }
    function performDelete(){ if (!deleteTargetId) return; const modalEl = document.getElementById('deleteModal'); const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl); if (modal) modal.hide(); fetch(`/suppliers/${deleteTargetId}`, { method:'DELETE', headers:{ 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')||'', 'Accept':'application/json' } }).then(r=>r.json()).then(data=>{ showAlert(data.message||'Deleted'); if (window.SupplierTable) window.SupplierTable.ajax.reload(); deleteTargetId = null; }).catch(()=>{ showAlert('Error deleting supplier'); }); }

    return { initList, initForm, initEditForm, confirmDelete };
})();
