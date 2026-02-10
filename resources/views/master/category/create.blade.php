@extends('layouts.backend')

@section('title', 'Add Category')

@section('content')
<div class="card form-card shadow-lg border-0">
    <div class="card-body p-5">
        <form id="category-form">
            <div class="section-style mb-4">
                <div class="section-title">Category Details</div>
                <div class="row mt-5">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Category Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control custom-input" id="name" name="name" placeholder="Enter Category name" />
                        <div class="invalid-feedback" id="name-error"></div>
                    </div>
                    <div class="col-md-6 mb-3 d-flex align-items-center gap-2 mt-3">
                        <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary">Back</a>
                        <button type="submit" class="btn btn-custom px-5 py-2" id="submitBtn">Submit</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/js/category.js') }}"></script>
<script>
    $(function(){
        if (window.Category && typeof Category.initForm === 'function') {
            Category.initForm('#category-form');
        }
    });
</script>
@endpush

@endsection
