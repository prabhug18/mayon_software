@extends('layouts.backend')
@section('title','Enquiry List')
@section('content')
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between mb-3">
            <h5>Enquiries</h5>
            <a href="{{ route('enquiries.create') }}" class="add-btn-custom">+ New Enquiry</a>
        </div>
        <table id="EnquiryTable" class="table table-bordered table-hover table-striped custom-table mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Mobile</th>
                    <th>Name</th>
                    <th>Project</th>
                    <th>Next Follow-up</th>
                    <th>Enquiry Type</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/js/enquiry.js') }}"></script>
<script>$(function(){ if (window.Enquiry && typeof Enquiry.initList === 'function') Enquiry.initList('#EnquiryTable'); });</script>
@endpush

@endsection
