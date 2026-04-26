@extends('layouts.backend')
@section('title','Edit Product')
@section('content')
<div class="card form-card shadow-lg border-0">
    <div class="card-body p-5">
        <form id="product-edit-form" enctype="multipart/form-data">
            <div class="section-style mb-4">
                <div class="section-title">
                    <i class="bi bi-box-seam me-2"></i> Basic Information
                </div>
                <div class="row mt-4">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Product Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control custom-input" id="name" name="name" value="{{ $product->name }}" />
                        <div class="invalid-feedback" id="name-error"></div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Category</label>
                        <select class="form-select custom-input" id="category_id" name="category_id">
                            <option selected disabled>Select Category</option>
                            @foreach($categories as $c)
                                <option value="{{ $c->id }}" @if($product->category_id == $c->id) selected @endif>{{ $c->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="category_id-error"></div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">UOM</label>
                        <select class="form-select custom-input" id="uom_id" name="uom_id">
                            <option selected disabled>Select UOM</option>
                            @foreach($uoms as $u)
                                <option value="{{ $u->id }}" @if($product->uom_id == $u->id) selected @endif>{{ $u->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="uom_id-error"></div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Main Product Image</label>
                        <input type="file" class="form-control custom-input" id="main_image" name="main_image" accept="image/*" />
                        <div class="invalid-feedback" id="main_image-error"></div>
                        <div class="mt-2" id="main_image_preview_container">
                            @if($product->main_image)
                                <img id="main_image_preview_img" src="{{ asset($product->main_image) }}" style="max-height:80px;"/>
                            @else
                                <img id="main_image_preview_img" src="" style="max-height:80px; display:none;"/>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-12 mb-3 mt-4">
                        <hr class="text-muted opacity-25 mb-4">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-custom px-5" id="submitBtn">
                                <i class="bi bi-check2-circle me-1"></i> Update Product
                            </button>
                            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/js/products.js') }}"></script>
<script>$(function(){ if (window.Product && typeof Product.initEditForm === 'function') Product.initEditForm('#product-edit-form', {{ $product->id }}); });</script>
@endpush

@endsection
