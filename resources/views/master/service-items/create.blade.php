@extends('layouts.backend')

@section('title', isset($serviceItem) ? 'Edit Service Item' : 'Create Service Item')

@section('content')
<div class="main-content">
    <div class="card form-card shadow-lg border-0">
        <div class="card-body p-5">
            <div class="text-center mb-5">
                <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle mb-4" style="width: 80px; height: 80px;">
                    <i class="bi bi-box-seam fs-1"></i>
                </div>
                <h3 class="fw-bold mb-1">{{ isset($serviceItem) ? 'Edit Service Item' : 'Create Service Item' }}</h3>
                <p class="text-muted small">Define service-specific items and their default pricing</p>
            </div>

            <form id="service-item-form">
                @csrf
                @if(isset($serviceItem))
                    @method('PUT')
                @endif

                <div class="section-style mb-4">
                    <div class="section-title">
                        <i class="bi bi-info-circle me-2"></i> Basic Information
                    </div>
                    <div class="row g-4 mt-2">
                        <div class="col-md-6">
                            <label class="form-label">Service <span class="text-danger">*</span></label>
                            <select class="form-select custom-input" id="service_id" name="service_id" required>
                                <option value="">Select Service</option>
                                @foreach($services as $service)
                                    <option value="{{ $service->id }}" {{ (isset($serviceItem) && $serviceItem->service_id == $service->id) ? 'selected' : '' }}>{{ $service->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="service_id-error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Item Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control custom-input" id="item_name" name="item_name" value="{{ $serviceItem->item_name ?? '' }}" required>
                            <div class="invalid-feedback" id="item_name-error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Unit <span class="text-danger">*</span></label>
                            <select class="form-select custom-input" id="unit_id" name="unit_id" required>
                                <option value="">Select Unit</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}" {{ (isset($serviceItem) && $serviceItem->unit_id == $unit->id) ? 'selected' : '' }}>{{ $unit->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="unit_id-error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">HSN/SAC Code</label>
                            <input type="text" class="form-control custom-input" id="hsn_sac_code" name="hsn_sac_code" value="{{ $serviceItem->hsn_sac_code ?? '' }}">
                            <div class="invalid-feedback" id="hsn_sac_code-error"></div>
                        </div>
                    </div>
                </div>

                <div class="section-style mb-4">
                    <div class="section-title">
                        <i class="bi bi-cash-coin me-2"></i> Pricing & Status
                    </div>
                    <div class="row g-4 mt-2">
                        <div class="col-md-4">
                            <label class="form-label">Default Rate</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">₹</span>
                                <input type="number" step="0.01" class="form-control custom-input border-start-0" id="default_rate" name="default_rate" value="{{ $serviceItem->default_rate ?? '' }}">
                            </div>
                            <div class="invalid-feedback" id="default_rate-error"></div>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="form-check form-switch mb-2 ms-2">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ (!isset($serviceItem) || $serviceItem->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold ms-2" for="is_active">Active Status</label>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control custom-input" id="description" name="description" rows="3">{{ $serviceItem->description ?? '' }}</textarea>
                            <div class="invalid-feedback" id="description-error"></div>
                        </div>
                    </div>
                </div>

                <div class="mt-5 d-flex gap-3 justify-content-center">
                    <button type="submit" class="btn btn-custom px-5 py-3 fw-bold shadow-sm">
                        {{ isset($serviceItem) ? 'Update Service Item' : 'Create Service Item' }} <i class="bi bi-check2-circle ms-2"></i>
                    </button>
                    <a href="{{ route('service-items.index') }}" class="btn btn-white border px-4 py-3 small text-decoration-none">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(function(){
    $('#service-item-form').on('submit', function(e){
        e.preventDefault();
        const btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Saving...');
        
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').hide();

        const id = "{{ $serviceItem->id ?? '' }}";
        const url = id ? `/service-items/${id}` : "{{ route('service-items.store') }}";
        
        fetch(url, {
            method: id ? 'PUT' : 'POST',
            body: new FormData(this),
            headers: { 
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'), 
                'Accept':'application/json' 
            }
        })
        .then(r => { 
            if(r.status==422) return r.json().then(x => { throw {validation:x}; }); 
            return r.json(); 
        })
        .then(d => { 
            showAlert(d.message||'Created'); 
            setTimeout(() => window.location.href='{{ route("service-items.index") }}', 900); 
        })
        .catch(err => {
            btn.prop('disabled', false).html(id ? 'Update Service Item <i class="bi bi-check2-circle ms-2"></i>' : 'Create Service Item <i class="bi bi-check2-circle ms-2"></i>');
            if(err.validation && err.validation.errors){
                for(const k in err.validation.errors){
                    $(`#${k}`).addClass('is-invalid');
                    $(`#${k}-error`).text(err.validation.errors[k][0]).show();
                }
            } else showAlert('Error processing request');
        });
    });
});
</script>
@endpush
@endsection
