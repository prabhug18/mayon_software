@extends('layouts.backend')
@section('title','Products')
@section('content')
<div class="card theme-card shadow-sm border-0">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="section-title mb-0">
                <i class="bi bi-box-seam me-2"></i> Products
            </div>
            <a href="{{ route('products.create') }}" class="btn btn-custom px-4">
                <i class="bi bi-plus-lg me-1"></i> Add Product
            </a>
        </div>
        
        <div class="table-responsive">
            <table id="ProductTable" class="table custom-table table-hover w-100">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>UOM</th>
                        <th>Image</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/js/products.js') }}"></script>
<script>$(function(){ if (window.Product && typeof Product.initList === 'function') Product.initList('#ProductTable'); });</script>
@endpush

@endsection
