@extends('layouts.backend')
@section('title','Enquiry List')
@section('content')
<div class="card theme-card shadow-sm border-0">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="section-title mb-0">
                <i class="bi bi-person-lines-fill me-2"></i> Enquiries
            </div>
            <a href="{{ route('enquiries.create') }}" class="btn btn-custom px-4">
                <i class="bi bi-plus-lg me-1"></i> New Enquiry
            </a>
        </div>
        
        <div class="table-responsive">
            <table id="EnquiryTable" class="table custom-table table-hover w-100">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name / Mobile</th>
                        <th>Service & Item</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Assigned To</th>
                        <th>Next Follow-up</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/js/enquiry.js') }}"></script>
<script>$(function(){ if (window.Enquiry && typeof Enquiry.initList === 'function') Enquiry.initList('#EnquiryTable'); });</script>
@endpush

@endsection
