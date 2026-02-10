<?php /* Blade template */ ?>
@extends('layouts.backend')

@section('title', 'Projects')

@section('content')
<div class="card">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Projects</h4>
            <a href="{{ route('projects.create') }}" class="add-btn-custom">Add Project</a>
        </div>
        <table id="ProjectTable" class="table table-bordered table-hover table-striped custom-table mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>


@push('scripts')
<script src="{{ asset('assets/js/project.js') }}"></script>
<script>
    $(function(){ if (window.Project && typeof Project.initList === 'function') Project.initList('#ProjectTable'); });
</script>
@endpush

@endsection
