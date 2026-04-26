@extends('layouts.backend')
@section('title','Edit Vendor')
@section('content')
<div class="card form-card shadow-lg border-0">
    <div class="card-body p-5">
        <form id="vendor-form" method="POST" action="{{ route('vendors.update', $vendor->id) }}">
            @csrf
            @method('PUT')
            <div class="section-style mb-4">
                <div class="section-title">
                    <i class="bi bi-people me-2"></i> Vendor Details
                </div>
                <div class="row mt-5">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Vendor Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control custom-input" id="name" name="name" value="{{ $vendor->name }}" required />
                        <div class="invalid-feedback" id="name-error"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="gstin" class="form-label">GSTIN</label>
                        <input type="text" class="form-control custom-input" id="gstin" name="gstin" value="{{ $vendor->gstin }}" />
                        <div class="invalid-feedback" id="gstin-error"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="contact_person" class="form-label">Contact Person</label>
                        <input type="text" class="form-control custom-input" id="contact_person" name="contact_person" value="{{ $vendor->contact_person }}" />
                        <div class="invalid-feedback" id="contact_person-error"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" class="form-control custom-input" id="phone" name="phone" value="{{ $vendor->phone }}" />
                        <div class="invalid-feedback" id="phone-error"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control custom-input" id="email" name="email" value="{{ $vendor->email }}" />
                        <div class="invalid-feedback" id="email-error"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control custom-input" id="address" name="address" rows="2">{{ $vendor->address }}</textarea>
                        <div class="invalid-feedback" id="address-error"></div>
                    </div>
                    <div class="col-md-12 mb-3">
                        <div class="form-check form-switch mt-2">
                            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ $vendor->is_active ? 'checked' : '' }} />
                            <label class="form-check-label" for="is_active">Active Vendor</label>
                        </div>
                    </div>
                    <div class="col-md-12 mb-3 mt-4">
                        <hr class="text-muted opacity-25 mb-4" />
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-custom px-5">
                                <i class="bi bi-check2-circle me-1"></i> Update Vendor
                            </button>
                            <a href="{{ route('vendors.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
$(function(){
    $('#vendor-form').on('submit', function(e){
        e.preventDefault();
        const fd = new FormData(this);
        fetch('{{ route("vendors.update", $vendor->id) }}', {
            method: 'POST',
            body: fd,
            headers: { 'X-HTTP-Method-Override': 'PUT', 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'), 'Accept':'application/json' }
        })
        .then(r => { if(r.status==422) return r.json().then(x => { throw {validation:x}; }); return r.json(); })
        .then(d => { showAlert(d.message||'Updated'); setTimeout(() => window.location.href='{{ route("vendors.index") }}', 900); })
        .catch(err => {
            if(err.validation && err.validation.errors){
                for(const k in err.validation.errors){
                    $('#'+k+'-error').text(err.validation.errors[k][0]);
                    $('#'+k).addClass('is-invalid');
                }
            } else showAlert('Error updating vendor');
        });
    });
});
</script>
@endpush

@endsection
