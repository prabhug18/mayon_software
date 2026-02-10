@extends('layouts.backend')
@section('title','Products')
@section('content')
<div class="card shadow-sm">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Products</h5>
            <div>
                <a href="{{ route('products.create') }}" class="btn btn-custom">+ Add Product</a>
            </div>
        </div>
        <table id="ProductTable" class="table table-bordered table-hover custom-table mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>UOM</th>
                    <th>Image</th>
                    <th>Actions</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/js/products.js') }}"></script>
<script>$(function(){ if (window.Product && typeof Product.initList === 'function') Product.initList('#ProductTable'); });</script>
@endpush

@endsection
