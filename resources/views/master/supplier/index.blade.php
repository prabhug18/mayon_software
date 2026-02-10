@extends('layouts.backend')
@section('title','Suppliers')
@section('content')
<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5>Suppliers</h5>
            <div>
                <a href="{{ route('suppliers.create') }}" class="btn btn-custom">Add Supplier</a>               
            </div>
        </div>
        <table class="table" id="supplierTable">
            <thead><tr><th>ID</th><th>Name</th><th>Contact Person</th><th>Mobile</th><th>Logo</th><th>Actions</th></tr></thead>
            <tbody></tbody>
        </table>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/js/supplier.js') }}"></script>
<script>$(function(){ if (window.Supplier && typeof Supplier.initList === 'function') Supplier.initList('#supplierTable'); });</script>
@endpush

@endsection
