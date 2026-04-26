@extends('layouts.backend')

@section('title', 'Category List')

@section('content')
<div class="card theme-card shadow-sm border-0">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="section-title mb-0">
                <i class="bi bi-tag me-2"></i> Categories
            </div>
            <a href="{{ route('categories.create') }}" class="btn btn-custom px-4">
                <i class="bi bi-plus-lg me-1"></i> Add Category
            </a>
        </div>
        
        <div class="table-responsive">
            <table id="CategoryTable" class="table custom-table table-hover w-100">
                <thead>
                    <tr>
                        <th width="50">S.NO.</th>
                        <th>Category Name</th>
                        <th class="text-center">ACTION</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/js/category.js') }}"></script>
<script>
    $(function(){
        if (window.Category && typeof Category.initList === 'function') {
            Category.initList('#CategoryTable');
        }
    });
</script>
@endpush

@endsection
