@extends('layouts.backend')

@section('title', 'User List')

@section('content')
    <div class="custom-box p-4">
        <div class="d-flex justify-content-end mb-0">
            <a href="{{ route('users.create') }}" class="add-btn-custom">+ Add User</a>
        </div>

        <div class="table-responsive mt-3">
            <table id="UserTable" class="table table-bordered table-hover table-striped custom-table mb-0">
                <thead>
                    <tr>
                        <th>S.NO.</th>
                        <th>USER NAME</th>
                        <th>EMAIL</th>
                        <th>ROLE</th>
                        <th>ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Rows loaded via AJAX -->
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
    <script>
    $(document).ready(function () {
        const table = $('#UserTable').DataTable({
            responsive: true,
            pageLength: 10,
            ajax: {
                url: '{{ route('users.index') }}',
                dataSrc: 'data'
            },
            columns: [
                { data: 'id' },
                { data: 'name' },
                { data: 'email' },
                { data: 'role', render: function(data) { return data.join(', '); } },
                { data: null, orderable: false, render: function(row) {
                    return `
                        <a href="${'{{ url("users") }}'}/${row.id}/edit" class='btn btn-sm btn-warning'>Edit</a>
                        <button class='btn btn-sm btn-danger' onclick='confirmDelete(${row.id})'>Delete</button>
                    `;
                }}
            ]
        });
        let deleteTargetId = null;

        window.confirmDelete = function(id) {
            deleteTargetId = id;
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        }

        window.performDelete = function() {
            if(!deleteTargetId) return;
            // hide confirmation modal first
            const confirmModalEl = document.getElementById('deleteModal');
            const confirmModal = bootstrap.Modal.getInstance(confirmModalEl);
            if(confirmModal) confirmModal.hide();

            fetch(`{{ url('users') }}/${deleteTargetId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                // show success modal instead of alert
                const successBody = document.getElementById('successModalBody');
                successBody.textContent = data.message || 'User deleted successfully.';
                const successModal = new bootstrap.Modal(document.getElementById('successModal'));
                successModal.show();

                // reload table and reset target
                table.ajax.reload();
                deleteTargetId = null;

                // auto-hide success modal after 1.5s
                setTimeout(() => {
                    const m = bootstrap.Modal.getInstance(document.getElementById('successModal'));
                    if(m) m.hide();
                }, 1500);
            })
            .catch(err => {
                const successBody = document.getElementById('successModalBody');
                successBody.textContent = 'An error occurred while deleting the user.';
                const successModal = new bootstrap.Modal(document.getElementById('successModal'));
                successModal.show();
            });
        }
    });
    </script>
    @endpush
    
    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            Are you sure you want to delete this user? This action cannot be undone.
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-danger" onclick="performDelete()">Delete</button>
          </div>
        </div>
      </div>
    
    </div>
@endsection
