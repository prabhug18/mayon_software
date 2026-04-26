@extends('layouts.backend')
@section('title','Quotations')
@section('content')
<div class="card theme-card shadow-sm border-0">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="section-title mb-0">
                <i class="bi bi-file-earmark-text me-2"></i> Quotations
            </div>
            <a href="{{ route('quotations.create') }}" class="btn btn-custom px-4">
                <i class="bi bi-plus-lg me-1"></i> New Quotation
            </a>
        </div>
        
        <div class="table-responsive">
            <table id="QuotationTable" class="table custom-table table-hover w-100">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Quotation No</th>
                        <th>Company</th>
                        <th>Enquiry</th>
                        <th>Date</th>
                        <th>Valid Till</th>
                        <th>Type</th>
                        <th>Grand Total</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/js/quotation.js') }}"></script>
<script>$(function(){ if (window.Quotation && typeof Quotation.initList === 'function') Quotation.initList('#QuotationTable'); });</script>
@endpush

@endsection
