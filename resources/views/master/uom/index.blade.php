@extends('layouts.backend')

@section('title', 'UOM List')

@section('content')
<div class="card theme-card shadow-sm border-0">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="section-title mb-0">
                <i class="bi bi-rulers me-2"></i> Units of Measure (UOM)
            </div>
            <a href="{{ route('uoms.create') }}" class="btn btn-custom px-4">
                <i class="bi bi-plus-lg me-1"></i> Add UOM
            </a>
        </div>
        
        <div class="table-responsive">
            <table id="UomTable" class="table custom-table table-hover w-100">
                <thead>
                    <tr>
                        <th width="50">S.NO.</th>
                        <th>UOM</th>
                        <th class="text-center">ACTION</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/js/uom.js') }}"></script>
<script>
    $(function(){
        if (window.Uom && typeof Uom.initList === 'function') {
            Uom.initList('#UomTable');
        }
    });
</script>
@endpush

@endsection
