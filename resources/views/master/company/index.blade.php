@extends('layouts.backend')
@section('title','Companies')
@section('content')
<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5>Companies</h5>
            <a href="{{ route('companies.create') }}" class="btn btn-custom">Add Company</a>
        </div>
        <table class="table" id="companyTable">
            <thead><tr><th>ID</th><th>Name</th><th>Contact Person</th><th>Mobile</th><th>Logo</th><th>Actions</th></tr></thead>
            <tbody></tbody>
        </table>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/js/company.js') }}"></script>
<script>$(function(){ if (window.Company && typeof Company.initList === 'function') Company.initList('#companyTable'); });</script>
@endpush

@endsection
