@extends('layouts.backend')
@section('title','Add Company')
@section('content')
<div class="card form-card shadow-lg border-0">
    <div class="card-body p-5">
        <form id="company-form" enctype="multipart/form-data">
            <div class="section-style mb-4">
                <div class="section-title">
                    <i class="bi bi-building me-2"></i> Company Details
                </div>
                <div class="row mt-5">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Company Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control custom-input" id="name" name="name" />
                        <div class="invalid-feedback" id="name-error"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Contact Person</label>
                        <input type="text" class="form-control custom-input" id="contact_person" name="contact_person" />
                        <div class="invalid-feedback" id="contact_person-error"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">PO Prefix <span class="text-muted small">(optional)</span></label>
                        <input type="text" class="form-control custom-input" id="po_prefix" name="po_prefix" placeholder="E.g. PO-ABC-" />
                        <div class="invalid-feedback" id="po_prefix-error"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Mobile Number</label>
                        <input type="text" class="form-control custom-input" id="mobile" name="mobile" />
                        <div class="invalid-feedback" id="mobile-error"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email ID</label>
                        <input type="email" class="form-control custom-input" id="email" name="email" />
                        <div class="invalid-feedback" id="email-error"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Address</label>
                        <input type="text" class="form-control custom-input" id="address" name="address" />
                        <div class="invalid-feedback" id="address-error"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">GST No</label>
                        <input type="text" class="form-control custom-input" id="gst_no" name="gst_no" />
                        <div class="invalid-feedback" id="gst_no-error"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Logo <span class="text-danger">*</span></label>
                        <input type="file" class="form-control custom-input" id="logo" name="logo" accept="image/*" />
                        <div class="invalid-feedback" id="logo-error"></div>
                        <div class="mt-2" id="logo_preview_container" style="display:none;"><img id="logo_preview_img" src="" alt="preview" style="max-height:80px;"/></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Authorized Signatory Image (optional)</label>
                        <input type="file" class="form-control custom-input" id="authorized_image" name="authorized_image" accept="image/*" />
                        <div class="invalid-feedback" id="authorized_image-error"></div>
                        <div class="mt-2" id="authorized_preview_container" style="display:none;"><img id="authorized_preview_img" src="" alt="authorized preview" style="max-height:80px;"/></div>
                    </div>
                    <div class="col-md-12 mb-3 mt-4">
                        <hr class="text-muted opacity-25 mb-4">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-custom px-5" id="submitBtn">
                                <i class="bi bi-check2-circle me-1"></i> Create Company
                            </button>
                            <a href="{{ route('companies.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/js/company.js') }}"></script>
<script>$(function(){ if (window.Company && typeof Company.initForm === 'function') Company.initForm('#company-form'); });</script>
@endpush

@endsection
