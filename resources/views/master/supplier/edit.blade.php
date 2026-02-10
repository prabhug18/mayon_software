@extends('layouts.backend')
@section('title','Edit Supplier')
@section('content')
<div class="card form-card shadow-lg border-0">
    <div class="card-body p-5">
        <form id="supplier-edit-form" enctype="multipart/form-data">
            <div class="section-style mb-4">
                <div class="section-title">Supplier Details</div>
                <div class="row mt-5">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Supplier Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control custom-input" id="name" name="name" value="{{ $supplier->name }}" />
                        <div class="invalid-feedback" id="name-error"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Contact Person</label>
                        <input type="text" class="form-control custom-input" id="contact_person" name="contact_person" value="{{ $supplier->contact_person }}" />
                        <div class="invalid-feedback" id="contact_person-error"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Mobile Number</label>
                        <input type="text" class="form-control custom-input" id="mobile" name="mobile" value="{{ $supplier->mobile }}" />
                        <div class="invalid-feedback" id="mobile-error"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Alternate Number</label>
                        <input type="text" class="form-control custom-input" id="alternate_number" name="alternate_number" value="{{ $supplier->alternate_number }}" />
                        <div class="invalid-feedback" id="alternate_number-error"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" class="form-control custom-input" id="location" name="location" value="{{ $supplier->location }}" />
                        <div class="invalid-feedback" id="location-error"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email ID</label>
                        <input type="email" class="form-control custom-input" id="email" name="email" value="{{ $supplier->email }}" />
                        <div class="invalid-feedback" id="email-error"></div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Address Line 1</label>
                        <input type="text" class="form-control custom-input" id="address_line1" name="address_line1" value="{{ $supplier->address_line1 }}" />
                        <div class="invalid-feedback" id="address_line1-error"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Address Line 2</label>
                        <input type="text" class="form-control custom-input" id="address_line2" name="address_line2" value="{{ $supplier->address_line2 }}" />
                        <div class="invalid-feedback" id="address_line2-error"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">City</label>
                        <input type="text" class="form-control custom-input" id="city" name="city" value="{{ $supplier->city }}" />
                        <div class="invalid-feedback" id="city-error"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">PIN Code</label>
                        <input type="text" class="form-control custom-input" id="pincode" name="pincode" value="{{ $supplier->pincode }}" />
                        <div class="invalid-feedback" id="pincode-error"></div>
                    </div>                  

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Logo</label>
                        <input type="file" class="form-control custom-input" id="logo" name="logo" accept="image/*" />
                        <div class="invalid-feedback" id="logo-error"></div>
                        <div class="mt-2" id="logo_preview_container">
                            @if($supplier->logo)
                                <img id="logo_preview_img" src="{{ asset($supplier->logo) }}" alt="logo" style="max-height:80px;"/>
                            @else
                                <img id="logo_preview_img" src="" alt="preview" style="max-height:80px; display:none;"/>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">GST No</label>
                        <input type="text" class="form-control custom-input" id="gst_no" name="gst_no" value="{{ $supplier->gst_no }}" />
                        <div class="invalid-feedback" id="gst_no-error"></div>
                    </div>

                    <div class="col-md-6 mb-3 d-flex align-items-center gap-2 mt-3">
                        <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary">Back</a>
                        <button type="submit" class="btn btn-custom px-5 py-2" id="submitBtn">Update</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/js/supplier.js') }}"></script>
<script>$(function(){ if (window.Supplier && typeof Supplier.initEditForm === 'function') Supplier.initEditForm('#supplier-edit-form', {{ $supplier->id }}); });</script>
@endpush

@endsection
