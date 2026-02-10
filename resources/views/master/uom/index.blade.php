@extends('layouts.backend')

@section('title', 'UOM List')

@section('content')
<div class="custom-box p-4">
    <div class="d-flex justify-content-end mb-0">
        <a href="{{ route('uoms.create') }}" class="add-btn-custom">+ Add UOM</a>
    </div>

    <div class="table-responsive mt-3">
        <table id="UomTable" class="table table-bordered table-hover table-striped custom-table mb-0">
            <thead>
                <tr>
                    <th>S.NO.</th>
                    <th>UOM</th>
                    <th>ACTION</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
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
