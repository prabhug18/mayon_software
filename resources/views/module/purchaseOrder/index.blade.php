@extends('layouts.backend')

@section('title','Purchase Orders')

@section('content')
<div class="card shadow-lg border-0">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Purchase Orders</h4>
            <a href="{{ route('purchaseOrders.create') }}" class="btn btn-custom">Add Purchase Order</a>
        </div>
        <table class="table" id="po-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>PO Number</th>
                    <th>Supplier</th>
                    <th>Project</th>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

{{-- Expose currency symbol to JS so client-side table renders amounts with symbol --}}
<script>window.currencySymbol = {!! json_encode(config('app.currency_symbol', '₹')) !!};</script>

@push('scripts')
<script src="{{ asset('assets/js/purchaseOrder.js') }}"></script>
@endpush

@endsection
