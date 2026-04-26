@extends('layouts.backend')

@section('title', isset($unit) ? 'Edit Unit' : 'Add Unit')

@section('content')
<div class="main-content">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card form-card shadow-lg border-0">
                <div class="card-body p-5">
                    <div class="text-center mb-5">
                        <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle mb-4" style="width: 80px; height: 80px;">
                            <i class="bi bi-rulers fs-1"></i>
                        </div>
                        <h3 class="fw-bold mb-1">{{ isset($unit) ? 'Edit Unit' : 'Add New Unit' }}</h3>
                        <p class="text-muted small">Manage measurement units for service items</p>
                    </div>

                    <form id="unit-form">
                        @csrf
                        @if(isset($unit))
                            @method('PUT')
                        @endif

                        <div class="mb-5">
                            <label class="form-label small fw-bold text-uppercase tracking-wider">Unit Name <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-type text-muted"></i></span>
                                <input type="text" id="name" name="name" class="form-control custom-input border-start-0" 
                                    value="{{ isset($unit) ? $unit->name : '' }}" placeholder="e.g. SQM, RMT, NOS" required>
                                <div class="invalid-feedback" id="name-error"></div>
                            </div>
                        </div>

                        <div class="d-flex gap-3">
                            <button type="submit" class="btn btn-custom px-5 py-3 fw-bold shadow-sm flex-grow-1">
                                {{ isset($unit) ? 'Update Unit' : 'Create Unit' }} <i class="bi bi-check2-circle ms-2"></i>
                            </button>
                            <a href="{{ route('units.index') }}" class="btn btn-white border px-4 py-3 small text-decoration-none">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(function() {
    $('#unit-form').on('submit', function(e) {
        e.preventDefault();
        const btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Saving...');

        const id = "{{ isset($unit) ? $unit->id : '' }}";
        const url = id ? `/master/units/${id}` : "{{ route('units.store') }}";
        const method = id ? 'PUT' : 'POST';

        fetch(url, {
            method: method,
            body: new FormData(this),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            }
        })
        .then(r => {
            if (r.status === 422) return r.json().then(x => { throw {validation: x}; });
            return r.json();
        })
        .then(d => {
            showAlert(d.message || 'Success');
            setTimeout(() => window.location.href = "{{ route('units.index') }}", 1000);
        })
        .catch(err => {
            btn.prop('disabled', false).html(id ? 'Update Unit <i class="bi bi-check2-circle ms-2"></i>' : 'Create Unit <i class="bi bi-check2-circle ms-2"></i>');
            if (err.validation && err.validation.errors) {
                for (const k in err.validation.errors) {
                    $(`#${k}`).addClass('is-invalid');
                    $(`#${k}-error`).text(err.validation.errors[k][0]).show();
                }
            } else {
                showAlert('An error occurred');
            }
        });
    });
});
</script>
@endpush
@endsection
