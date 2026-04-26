@extends('layouts.backend')

@section('title', 'Roles & Permissions')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">{{ $heading }}</h4>
    <a href="{{ route('roles.create') }}" class="btn btn-custom">
        <i class="bi bi-plus-lg me-1"></i> Add New Role
    </a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="bg-light">
                    <tr>
                        <th width="50">#</th>
                        <th width="200">Role Name</th>
                        <th>Permissions</th>
                        <th width="150" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($roles as $index => $role)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <span class="fw-bold">{{ $role->name }}</span>
                        </td>
                        <td>
                            @foreach($role->permissions as $permission)
                                <span class="badge bg-soft-primary text-primary me-1 mb-1">{{ $permission->name }}</span>
                            @endforeach
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @if($role->name !== 'Admin')
                                <button type="button" class="btn btn-sm btn-outline-danger delete-btn" data-id="{{ $role->id }}" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.bg-soft-primary {
    background-color: rgba(13, 110, 253, 0.1);
}
</style>

@push('scripts')
<script>
$(function() {
    $('.delete-btn').on('click', function() {
        const id = $(this).data('id');
        $('#deleteModal').modal('show');
        $('#deleteModalConfirmBtn').off('click').on('click', function() {
            fetch(`{{ url('roles') }}/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    location.reload();
                } else {
                    alert(data.message || 'Error deleting role');
                }
            });
        });
    });
});
</script>
@endpush
@endsection
