@extends('layouts.backend')

@section('title', 'Enquiry Type List')

@section('content')
<div class="custom-box p-4">
    <div class="d-flex justify-content-end mb-0">
        <a href="{{ route('enquiry-types.create') }}" class="add-btn-custom">+ Add Enquiry Type</a>
    </div>

    <div class="table-responsive mt-3">
        <table id="EnquiryTypeTable" class="table table-bordered table-hover table-striped custom-table mb-0">
            <thead>
                <tr>
                    <th>S.NO.</th>
                    <th>Enquiry Type</th>
                    <th>ACTION</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/js/enquiryType.js') }}"></script>
<script>
    $(function(){ if (window.EnquiryType && typeof EnquiryType.initList === 'function') EnquiryType.initList('#EnquiryTypeTable'); });
</script>
@endpush

@endsection
