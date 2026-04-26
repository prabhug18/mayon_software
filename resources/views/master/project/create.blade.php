<?php /* Blade template */ ?>
@extends('layouts.backend')

@section('title', 'Add Project')


@section('content')
<div class="card form-card shadow-lg border-0">
    <div class="card-body p-5">
    <form id="project-form" enctype="multipart/form-data">
            <div class="section-style mb-4">
                <div class="section-title">
                    <i class="bi bi-geo-alt me-2"></i> Project Details
                </div>
                <div class="row mt-5">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Project Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control custom-input" id="name" name="name" placeholder="Enter Project Name" />
                        <div class="invalid-feedback" id="name-error"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" class="form-control custom-input" id="location" name="location" placeholder="Enter Location" />
                        <div class="invalid-feedback" id="location-error"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select custom-input" id="status" name="status">
                            <option value="On Going">On Going</option>
                            <option value="Completed">Completed</option>
                        </select>
                        <div class="invalid-feedback" id="status-error"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Address</label>
                        <input type="text" class="form-control custom-input" id="address" name="address" placeholder="Enter Address" />
                        <div class="invalid-feedback" id="address-error"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Logo Image</label>
                        <input type="file" class="form-control custom-input" id="logo_image" name="logo_image" accept="image/*" />
                        <div class="invalid-feedback" id="logo_image-error"></div>
                        <div class="mt-2" id="logo_preview_container" style="display:none;"><img id="logo_preview_img" src="" alt="preview" style="max-height:80px;"/></div>
                    </div>
                    <div class="col-md-12 mb-3 mt-4">
                        <hr class="text-muted opacity-25 mb-4">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-custom px-5" id="submitBtn">
                                <i class="bi bi-check2-circle me-1"></i> Create Project
                            </button>
                            <a href="{{ route('projects.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/js/project.js') }}"></script>
<script>
    $(function(){ if (window.Project && typeof Project.initForm === 'function') Project.initForm('#project-form'); });
</script>
@endpush

@endsection
