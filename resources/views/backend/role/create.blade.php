@extends('layouts.backend')

@section('title', 'Add Role')

@section('content')
<div class="card form-card shadow-lg border-0">
    <div class="card-body p-5">
        <form id="role-form">
            <div class="section-style mb-4">
                <div class="section-title">Role Details</div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Role Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control custom-input" id="name" name="name" placeholder="e.g. Manager" required />
                        <div class="invalid-feedback" id="name-error"></div>
                    </div>
                </div>
            </div>

            <div class="section-style mb-4">
                <div class="section-title">Assign Permissions</div>
                <div class="row">
                    @foreach($permissions as $group => $groupPermissions)
                        <div class="col-md-4 mb-4">
                            <h6 class="text-primary fw-bold text-capitalize mb-3 border-bottom pb-2">{{ $group }} Management</h6>
                            @foreach($groupPermissions as $permission)
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->name }}" id="perm_{{ $permission->id }}">
                                    <label class="form-check-label" for="perm_{{ $permission->id }}">
                                        {{ $permission->name }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="text-center d-flex justify-content-center gap-2 mt-4">
                <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">Back</a>
                <button type="submit" class="btn btn-custom px-5 py-2">Create Role</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('role-form').addEventListener('submit', function(e) {
    e.preventDefault();
    let formData = new FormData(this);
    
    // Clear previous errors
    document.getElementById('name-error').textContent = '';
    document.getElementById('name').classList.remove('is-invalid');

    fetch('{{ route('roles.store') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.status === 'success') {
            const body = document.getElementById('successModalBody');
            body.textContent = data.message || 'Role created successfully.';
            const successModal = new bootstrap.Modal(document.getElementById('successModal'));
            successModal.show();
            setTimeout(() => { window.location.href = '{{ route('roles.index') }}'; }, 1200);
        } else {
            if(data.errors && data.errors.name) {
                document.getElementById('name-error').textContent = data.errors.name[0];
                document.getElementById('name').classList.add('is-invalid');
            }
        }
    });
});
</script>
@endpush
@endsection
