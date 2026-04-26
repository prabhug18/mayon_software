@extends('layouts.backend')

@section('title', 'Source List')

@section('content')
<div class="card theme-card shadow-sm border-0">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="section-title mb-0">
                <i class="bi bi-broadcast me-2"></i> Enquiry Sources
            </div>
            <a href="{{ route('sources.create') }}" class="btn btn-custom px-4">
                <i class="bi bi-plus-lg me-1"></i> Add Source
            </a>
        </div>
        
        <div class="table-responsive">
            <table id="SourceTable" class="table custom-table table-hover w-100">
                <thead>
                    <tr>
                        <th width="50">S.NO.</th>
                        <th>Source Name</th>
                        <th class="text-center">ACTION</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
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
