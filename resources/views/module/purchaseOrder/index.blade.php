@extends('layouts.backend')

@section('title','Purchase Orders')

@section('content')
<div class="card theme-card shadow-sm border-0">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="section-title mb-0">
                <i class="bi bi-cart-check me-2"></i> Purchase Orders
            </div>
            <a href="{{ route('purchaseOrders.create') }}" class="btn btn-custom px-4">
                <i class="bi bi-plus-lg me-1"></i> Add Purchase Order
            </a>
        </div>
        
        <div class="table-responsive">
            <table id="po-table" class="table custom-table table-hover w-100">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>PO Number</th>
                        <th>Supplier</th>
                        <th>Project</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

{{-- Expose currency symbol to JS so client-side table renders amounts with symbol --}}
<script>window.currencySymbol = {!! json_encode(config('app.currency_symbol', '₹')) !!};</script>

@push('scripts')
<script src="{{ asset('assets/js/purchaseOrder.js') }}"></script>
@endpush

@endsection
