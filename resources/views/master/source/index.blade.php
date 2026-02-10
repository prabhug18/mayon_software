@extends('layouts.backend')

@section('title', 'Source List')

@section('content')
<div class="custom-box p-4">
    <div class="d-flex justify-content-end mb-0">
        <a href="{{ route('sources.create') }}" class="add-btn-custom">+ Add Source</a>
    </div>

    <div class="table-responsive mt-3">
        <table id="SourceTable" class="table table-bordered table-hover table-striped custom-table mb-0">
            <thead>
                <tr>
                    <th>S.NO.</th>
                    <th>Source Name</th>
                    <th>ACTION</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/js/source.js') }}"></script>
<script>
    $(function(){
        if (window.Source && typeof Source.initList === 'function') {
            Source.initList('#SourceTable');
        }
    });
</script>
@endpush

@endsection
