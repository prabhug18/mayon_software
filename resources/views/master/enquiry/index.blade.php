@extends('layouts.backend')
@section('title','Enquiry List')
@section('content')
<div class="card theme-card shadow-sm border-0">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="section-title mb-0">
                <i class="bi bi-person-lines-fill me-2"></i> Enquiries
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('enquiries.import') }}" class="btn btn-outline-primary px-4">
                    <i class="bi bi-facebook me-1"></i> Import Facebook Leads
                </a>
                <a href="{{ route('enquiries.create') }}" class="btn btn-custom px-4">
                    <i class="bi bi-plus-lg me-1"></i> New Enquiry
                </a>
            </div>
        </div>

        <div class="row g-3 mb-4 filter-section bg-light p-3 rounded shadow-sm">
            <div class="col-md-4">
                <label class="form-label small fw-bold">Filter by Period</label>
                <select id="date_filter" class="form-select form-select-sm border-primary">
                    <option value="all">All Time</option>
                    <option value="7days">Last 7 Days</option>
                    <option value="30days">Last 30 Days</option>
                    <option value="3months">Last 3 Months</option>
                    <option value="custom">Custom Date Range</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Filter by Year</label>
                <select id="year_filter" class="form-select form-select-sm border-primary">
                    <option value="all">All Years</option>
                    @php $currentYear = date('Y'); @endphp
                    @for($y = $currentYear; $y >= $currentYear - 5; $y--)
                        <option value="{{ $y }}" {{ $y == $currentYear ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-6 custom-date-container d-none">
                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label small fw-bold">From</label>
                        <input type="date" id="from_date" class="form-control form-control-sm">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold">To</label>
                        <input type="date" id="to_date" class="form-control form-control-sm">
                    </div>
                </div>
            </div>
        </div>
        
        <div class="table-responsive">
            <table id="EnquiryTable" class="table custom-table table-hover w-100">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name / Mobile</th>
                        <th>Service & Item</th>
                        <th>Source</th>
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
