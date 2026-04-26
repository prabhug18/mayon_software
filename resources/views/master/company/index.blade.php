@extends('layouts.backend')
@section('title','Companies')
@section('content')
<div class="card theme-card shadow-sm border-0">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="section-title mb-0">
                <i class="bi bi-building me-2"></i> Companies
            </div>
            <a href="{{ route('companies.create') }}" class="btn btn-custom px-4">
                <i class="bi bi-plus-lg me-1"></i> Add Company
            </a>
        </div>
        
        <div class="table-responsive">
            <table id="companyTable" class="table custom-table table-hover w-100">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Contact Person</th>
                        <th>Mobile</th>
                        <th>Logo</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/js/company.js') }}"></script>
<script>$(function(){ if (window.Company && typeof Company.initList === 'function') Company.initList('#companyTable'); });</script>
@endpush

@endsection
