@extends('layouts.backend')
@section('title','Add Supplier')
@section('content')
<div class="card form-card shadow-lg border-0">
    <div class="card-body p-5">
        <form id="supplier-form" enctype="multipart/form-data">
            <div class="section-style mb-4">
                <div class="section-title">
                    <i class="bi bi-truck me-2"></i> Supplier Details
                </div>
                <div class="row mt-5">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Supplier Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control custom-input" id="name" name="name" />
                        <div class="invalid-feedback" id="name-error"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Contact Person</label>
                        <input type="text" class="form-control custom-input" id="contact_person" name="contact_person" />
                        <div class="invalid-feedback" id="contact_person-error"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Mobile Number</label>
                        <input type="text" class="form-control custom-input" id="mobile" name="mobile" />
                        <div class="invalid-feedback" id="mobile-error"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Alternate Number</label>
                        <input type="text" class="form-control custom-input" id="alternate_number" name="alternate_number" />
                        <div class="invalid-feedback" id="alternate_number-error"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" class="form-control custom-input" id="location" name="location" />
                        <div class="invalid-feedback" id="location-error"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email ID</label>
                        <input type="email" class="form-control custom-input" id="email" name="email" />
                        <div class="invalid-feedback" id="email-error"></div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Address Line 1</label>
                        <input type="text" class="form-control custom-input" id="address_line1" name="address_line1" />
                        <div class="invalid-feedback" id="address_line1-error"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Address Line 2</label>
                        <input type="text" class="form-control custom-input" id="address_line2" name="address_line2" />
                        <div class="invalid-feedback" id="address_line2-error"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">City</label>
                        <input type="text" class="form-control custom-input" id="city" name="city" />
                        <div class="invalid-feedback" id="city-error"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">PIN Code</label>
                        <input type="text" class="form-control custom-input" id="pincode" name="pincode" />
                        <div class="invalid-feedback" id="pincode-error"></div>
                    </div>

                  

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Logo</label>
                        <input type="file" class="form-control custom-input" id="logo" name="logo" accept="image/*" />
                        <div class="invalid-feedback" id="logo-error"></div>
                        <div class="mt-2" id="logo_preview_container" style="display:none;"><img id="logo_preview_img" src="" alt="preview" style="max-height:80px;"/></div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">GST No</label>
                        <input type="text" class="form-control custom-input" id="gst_no" name="gst_no" />
                        <div class="invalid-feedback" id="gst_no-error"></div>
                    </div>

                    <div class="col-md-12 mb-3 mt-4">
                        <hr class="text-muted opacity-25 mb-4">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-custom px-5" id="submitBtn">
                                <i class="bi bi-check2-circle me-1"></i> Create Supplier
                            </button>
                            <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/js/supplier.js') }}"></script>
<script>$(function(){ if (window.Supplier && typeof Supplier.initForm === 'function') Supplier.initForm('#supplier-form'); });</script>
@endpush

@endsection
