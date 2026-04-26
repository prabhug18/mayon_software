@extends('layouts.backend')
@section('title','Create Service')
@section('content')
<div class="card form-card shadow-lg border-0">
    <div class="card-body p-5">
        <div class="section-style mb-4">
            <div class="section-title">
                <i class="bi bi-tools me-2"></i> Create New Service
            </div>
            <form id="service-form" method="POST" action="{{ route('services.store') }}">
                @csrf
                <div class="row mt-4">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Service Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control custom-input" id="name" name="name" placeholder="e.g. Flooring Installation" required>
                        <div class="invalid-feedback" id="name-error"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="category" class="form-label">Category</label>
                        <input type="text" class="form-control custom-input" id="category" name="category" placeholder="e.g. Installation">
                        <div class="invalid-feedback" id="category-error"></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="default_gst_percentage" class="form-label">Default GST %</label>
                        <input type="number" step="0.01" class="form-control custom-input" id="default_gst_percentage" name="default_gst_percentage" value="18">
                        <div class="invalid-feedback" id="default_gst_percentage-error"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="is_active" class="form-label d-block">Status</label>
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                            <label class="form-check-label" for="is_active">Active Service</label>
                        </div>
                    </div>
                </div>
                <div class="mb-4">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control custom-input" id="description" name="description" rows="3" placeholder="Provide a brief description of the service..."></textarea>
                    <div class="invalid-feedback" id="description-error"></div>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-custom px-5 py-2">Create Service</button>
                    <a href="{{ route('services.index') }}" class="btn btn-outline-secondary px-4 py-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(function(){
    $('#service-form').on('submit', function(e){
        e.preventDefault();
        const fd = new FormData(this);
        fetch('{{ route("services.store") }}', {
            method: 'POST',
            body: fd,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'), 'Accept':'application/json' }
        })
        .then(r => { if(r.status==422) return r.json().then(x => { throw {validation:x}; }); return r.json(); })
        .then(d => { showAlert(d.message||'Created'); setTimeout(() => window.location.href='{{ route("services.index") }}', 900); })
        .catch(err => {
            if(err.validation && err.validation.errors){
                for(const k in err.validation.errors){
                    $('#'+k+'-error').text(err.validation.errors[k][0]);
                    $('#'+k).addClass('is-invalid');
                }
            } else showAlert('Error creating service');
        });
    });
});
</script>
@endpush

@endsection
