<?php /* Blade template */ ?>
@extends('layouts.backend')

@section('title', 'Edit Project')


@section('content')
<div class="card form-card shadow-lg border-0">
    <div class="card-body p-5">
    <form id="project-edit-form" enctype="multipart/form-data">
            <div class="section-style mb-4">
                <div class="section-title">Project Details</div>
                <div class="row mt-5">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Project Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control custom-input" id="name" name="name" value="{{ $project->name }}" />
                        <div class="invalid-feedback" id="name-error"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" class="form-control custom-input" id="location" name="location" value="{{ $project->location }}" />
                        <div class="invalid-feedback" id="location-error"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select custom-input" id="status" name="status">
                            <option value="On Going" {{ $project->status == 'On Going' ? 'selected' : '' }}>On Going</option>
                            <option value="Completed" {{ $project->status == 'Completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                        <div class="invalid-feedback" id="status-error"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Address</label>
                        <input type="text" class="form-control custom-input" id="address" name="address" value="{{ $project->address }}" />
                        <div class="invalid-feedback" id="address-error"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Logo Image</label>
                        <input type="file" class="form-control custom-input" id="logo_image" name="logo_image" accept="image/*" />
                        <div class="invalid-feedback" id="logo_image-error"></div>
                        <div class="mt-2" id="logo_preview_container">
                            @if($project->logo_image)
                                <img id="logo_preview_img" src="{{ asset($project->logo_image) }}" alt="logo" style="max-height:80px;"/>
                            @else
                                <img id="logo_preview_img" src="" alt="preview" style="max-height:80px; display:none;"/>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6 mb-3 d-flex align-items-center gap-2 mt-3">
                        <a href="{{ route('projects.index') }}" class="btn btn-outline-secondary">Back</a>
                        <button type="submit" class="btn btn-custom px-5 py-2" id="submitBtn">Update</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/js/project.js') }}"></script>
<script>
    $(function(){ if (window.Project && typeof Project.initEditForm === 'function') Project.initEditForm('#project-edit-form', {{ $project->id }}); });
</script>
@endpush

@endsection
