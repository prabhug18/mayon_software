@extends('layouts.backend')

@section('title', 'Category List')

@section('content')
<div class="custom-box p-4">
    <div class="d-flex justify-content-end mb-0">
        <a href="{{ route('categories.create') }}" class="add-btn-custom">+ Add Category</a>
    </div>

    <div class="table-responsive mt-3">
        <table id="CategoryTable" class="table table-bordered table-hover table-striped custom-table mb-0">
            <thead>
                <tr>
                    <th>S.NO.</th>
                    <th>Category Name</th>
                    <th>ACTION</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
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
