@extends('layouts.backend')
@section('title','Suppliers')
@section('content')
<div class="card theme-card shadow-sm border-0">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="section-title mb-0">
                <i class="bi bi-truck me-2"></i> Suppliers
            </div>
            <a href="{{ route('suppliers.create') }}" class="btn btn-custom px-4">
                <i class="bi bi-plus-lg me-1"></i> Add Supplier
            </a>
        </div>
        
        <div class="table-responsive">
            <table id="supplierTable" class="table custom-table table-hover w-100">
                <thead>
                    <tr>
                        <th width="50">ID</th>
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
<script src="{{ asset('assets/js/supplier.js') }}"></script>
<script>$(function(){ if (window.Supplier && typeof Supplier.initList === 'function') Supplier.initList('#supplierTable'); });</script>
@endpush

@endsection
