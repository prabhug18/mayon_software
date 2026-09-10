@extends('layouts.backend')
@section('title','Enquiry List')
@section('content')
<div class="card theme-card shadow-sm border-0">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="section-title mb-0">
                <i class="bi bi-person-lines-fill me-2"></i> Enquiries
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('enquiries.excel.import') }}" class="btn btn-outline-success px-3">
                    <i class="bi bi-file-earmark-excel me-1"></i> Import Excel Leads
                </a>
                <a href="{{ route('enquiries.import') }}" class="btn btn-outline-primary px-3">
                    <i class="bi bi-facebook me-1"></i> Import Facebook Leads
                </a>
                <a href="{{ route('enquiries.create') }}" class="btn btn-custom px-3">
                    <i class="bi bi-plus-lg me-1"></i> New Enquiry
                </a>
            </div>
        </div>

        <div class="row g-3 mb-4 filter-section bg-light p-3 rounded shadow-sm">
            <div class="col-md-3 col-sm-6">
                <label class="form-label small fw-bold">Filter by Source</label>
                <select id="source_filter" class="form-select form-select-sm border-primary">
                    <option value="all">All Sources</option>
                    @if(isset($sources))
                        @foreach($sources as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div class="col-md-2 col-sm-6">
                <label class="form-label small fw-bold">Filter by Status</label>
                <select id="status_filter" class="form-select form-select-sm border-primary">
                    <option value="all">All Statuses</option>
                    @if(isset($statuses))
                        @foreach($statuses as $st)
                            <option value="{{ $st }}">{{ $st }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div class="col-md-3 col-sm-6">
                <label class="form-label small fw-bold">Filter by Service</label>
                <select id="service_filter" class="form-select form-select-sm border-primary">
                    <option value="all">All Services</option>
                    @if(isset($services))
                        @foreach($services as $srv)
                            <option value="{{ $srv->id }}">{{ $srv->name }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div class="col-md-2 col-sm-6">
                <label class="form-label small fw-bold">Filter by Period</label>
                <select id="date_filter" class="form-select form-select-sm border-primary">
                    <option value="all">All Time</option>
                    <option value="7days">Last 7 Days</option>
                    <option value="30days">Last 30 Days</option>
                    <option value="3months">Last 3 Months</option>
                    <option value="custom">Custom Range</option>
                </select>
            </div>
            <div class="col-md-2 col-sm-6">
                <label class="form-label small fw-bold">Filter by Year</label>
                <select id="year_filter" class="form-select form-select-sm border-primary">
                    <option value="all">All Years</option>
                    @php $currentYear = date('Y'); @endphp
                    @for($y = $currentYear; $y >= $currentYear - 5; $y--)
                        <option value="{{ $y }}" {{ $y == $currentYear ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-12 custom-date-container d-none pt-2 border-top">
                <div class="row g-2 align-items-center">
                    <div class="col-auto"><span class="small fw-bold text-muted">Custom Date Range:</span></div>
                    <div class="col-md-3 col-6">
                        <input type="date" id="from_date" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3 col-6">
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
