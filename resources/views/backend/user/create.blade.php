@extends('layouts.backend')

@section('title', 'Add User')

@section('content')
    <div class="card form-card shadow-lg border-0">
        <div class="card-body p-5">
            <form id="user-form">
                <div class="section-style mb-4">
                    <div class="section-title">User Details</div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control custom-input" id="name" name="name" required />
                            <div class="invalid-feedback" id="name-error"></div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control custom-input" id="email" name="email" required />
                            <div class="invalid-feedback" id="email-error"></div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Mobile</label>
                            <input type="text" class="form-control custom-input" id="mobile" name="mobile" placeholder="e.g. +14155552671" />
                            <div class="invalid-feedback" id="mobile-error"></div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="d-flex justify-content-between">
                                <label class="form-label">Role <span class="text-danger">*</span></label>
                                <a href="{{ route('roles.index') }}" class="small text-decoration-none"><i class="bi bi-gear me-1"></i>Manage Roles</a>
                            </div>
                            <select class="form-select custom-input" id="role" name="role" required>
                                <option value="" disabled selected>Select Role</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->name }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="role-error"></div>
                        </div>
                    </div>
                </div>

                <div class="section-style mb-4">
                    <div class="section-title">Login Credentials</div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control custom-input" id="password" name="password" required minlength="6" autocomplete="new-password" />
                            <div class="invalid-feedback" id="password-error"></div>
                        </div>
                    </div>
                </div>

                <div class="text-center d-flex justify-content-center gap-2">
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Back</a>
                    <button type="submit" class="btn btn-custom px-5 py-2">Create</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    document.getElementById('user-form').addEventListener('submit', function(e) {
        e.preventDefault();
        let formData = new FormData(this);
        // Clear previous errors
        ['name', 'email', 'mobile', 'password', 'role'].forEach(function(field) {
            document.getElementById(field + '-error').textContent = '';
            document.getElementById(field).classList.remove('is-invalid');
        });
        fetch('{{ route('users.store') }}', {
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
                body.textContent = data.message || 'User created successfully.';
                const successModal = new bootstrap.Modal(document.getElementById('successModal'));
                successModal.show();
                setTimeout(() => { window.location.href = '{{ route('users.index') }}'; }, 1200);
            } else {
                if(data.errors) {
                    for (const field in data.errors) {
                        document.getElementById(field + '-error').textContent = data.errors[field][0];
                        document.getElementById(field).classList.add('is-invalid');
                    }
                }
            }
        });
    });
    </script>
    @endpush
@endsection
