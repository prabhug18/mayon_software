<?php /* Blade template */ ?>
@extends('layouts.backend')

@section('title', 'Projects')

@section('content')
<div class="card theme-card shadow-sm border-0">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="section-title mb-0">
                <i class="bi bi-geo-alt me-2"></i> Projects
            </div>
            <a href="{{ route('projects.create') }}" class="btn btn-custom px-4">
                <i class="bi bi-plus-lg me-1"></i> Add Project
            </a>
        </div>
        
        <div class="table-responsive">
            <table id="ProjectTable" class="table custom-table table-hover w-100">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>Name</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>


@push('scripts')
<script src="{{ asset('assets/js/project.js') }}"></script>
<script>
    $(function(){ if (window.Project && typeof Project.initList === 'function') Project.initList('#ProjectTable'); });
</script>
@endpush

@endsection
