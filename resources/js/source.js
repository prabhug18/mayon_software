window.Source = (function(){
	function showAlert(message) {
		const body = document.getElementById('successModalBody');
		if (body) {
			body.textContent = message;
			const modal = new bootstrap.Modal(document.getElementById('successModal'));
			modal.show();
			setTimeout(() => { const m = bootstrap.Modal.getInstance(document.getElementById('successModal')); if (m) m.hide(); }, 1400);
		} else {
			alert(message);
		}
	}

	function initList(tableSelector) {
		const table = $(tableSelector).DataTable({
			ajax: {
				url: '/sources',
				dataSrc: 'data'
			},
			columns: [
				{ data: 'id' },
				{ data: 'name' },
				{ data: null, orderable: false, render: function(row) {
					return `
						<a href="/sources/${row.id}/edit" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil-square"></i></a>
						<button class="btn btn-sm btn-outline-danger" onclick="Source.confirmDelete(${row.id}, '${escapeHtml(row.name)}')"><i class="bi bi-trash"></i></button>
					`;
				}}
			]
		});

		window.SourceTable = table;
	}

	function escapeHtml(unsafe) {
		return (''+unsafe).replace(/[&<>"]/g, function(m) { return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[m]; });
	}

	let deleteTargetId = null;

	function confirmDelete(id, name) {
		deleteTargetId = id;
		const modalEl = document.getElementById('deleteModal');
		if (modalEl) {
			const body = modalEl.querySelector('.modal-body');
			if (body) body.textContent = `Are you sure you want to delete "${name}"? This action can be undone from trash.`;
			const modal = new bootstrap.Modal(modalEl);
			modal.show();
		} else {
			if (confirm('Are you sure you want to delete this item?')) performDelete();
		}
	}

	function performDelete() {
		if (!deleteTargetId) return;
		const modalEl = document.getElementById('deleteModal');
		const modal = bootstrap.Modal.getInstance(modalEl);
		if (modal) modal.hide();

		fetch(`/sources/${deleteTargetId}`, {
			method: 'DELETE',
			headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '', 'Accept':'application/json' }
		}).then(r => r.json()).then(data => {
			showAlert(data.message || 'Deleted');
			if (window.SourceTable) window.SourceTable.ajax.reload();
			deleteTargetId = null;
		}).catch(e => { showAlert('Error deleting item'); });
	}

	function clearErrors() {
		document.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
		document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
	}

	function initForm(formSelector) {
		const form = document.querySelector(formSelector);
		if (!form) return;
		form.addEventListener('submit', function(e){
			e.preventDefault();
			clearErrors();
			const name = form.querySelector('[name="name"]').value.trim();
			let hasError = false;
			if (!name) {
				const err = document.getElementById('name-error');
				if (err) err.textContent = 'Source name is required.';
				const inp = form.querySelector('[name="name"]'); if (inp) inp.classList.add('is-invalid');
				hasError = true;
			}
			if (hasError) return;

			const fd = new FormData(form);
			fetch('/sources', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')||'', 'Accept':'application/json' }, body: fd })
			.then(r => r.json())
			.then(data => {
				if (data.status === 'success') {
					showAlert(data.message || 'Source created');
					setTimeout(() => { window.location.href = '/sources'; }, 900);
				} else if (data.errors) {
					for (const k in data.errors) {
						const err = document.getElementById(k + '-error');
						if (err) err.textContent = data.errors[k][0];
						const inp = form.querySelector('[name="' + k + '"]'); if (inp) inp.classList.add('is-invalid');
					}
				}
			}).catch(e => { showAlert('An error occurred'); });
		});
	}

	function initEditForm(formSelector, id) {
		const form = document.querySelector(formSelector);
		if (!form) return;
		form.addEventListener('submit', function(e){
			e.preventDefault();
			clearErrors();
			const name = form.querySelector('[name="name"]').value.trim();
			if (!name) {
				const err = document.getElementById('name-error'); if (err) err.textContent = 'Source name is required.';
				const inp = form.querySelector('[name="name"]'); if (inp) inp.classList.add('is-invalid');
				return;
			}

			const fd = new FormData(form);
			fetch(`/sources/${id}`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')||'', 'Accept':'application/json', 'X-HTTP-Method-Override':'PUT' }, body: fd })
			.then(r => r.json()).then(data => {
				if (data.status === 'success') {
					showAlert(data.message || 'Updated');
					setTimeout(() => { window.location.href = '/sources'; }, 900);
				} else if (data.errors) {
					for (const k in data.errors) {
						const err = document.getElementById(k + '-error'); if (err) err.textContent = data.errors[k][0];
						const inp = form.querySelector('[name="' + k + '"]'); if (inp) inp.classList.add('is-invalid');
					}
				}
			}).catch(e => { showAlert('An error occurred'); });
		});
	}

	return { initList, initForm, initEditForm, confirmDelete, performDelete };
})();

