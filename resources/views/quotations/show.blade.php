@extends('layouts.backend')
@section('title','Quotation Details')
@section('content')
<div class="container">
    <div class="row">
        <div class="col-lg-12">
<div class="card theme-card shadow-sm border-0">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <div class="section-title mb-1">
                    <i class="bi bi-file-earmark-text me-2"></i> {{ $quotation->quotation_no }}
                </div>
                <div class="mt-2">
                    <span class="badge bg-{{ $quotation->status == 'APPROVED' ? 'success' : ($quotation->status == 'SENT' ? 'info' : 'secondary') }} px-3 py-2">
                        {{ $quotation->status }}
                    </span>
                    @if($quotation->revision_no > 0)
                        <span class="badge bg-warning text-dark px-3 py-2 ms-1">Revision {{ $quotation->revision_no }}</span>
                    @endif
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('quotations.pdf', $quotation->id) }}" class="btn btn-outline-danger" target="_blank">
                    <i class="bi bi-file-pdf me-1"></i> PDF
                </a>
                @if($quotation->status != 'REVISED')
                <form action="{{ route('quotations.revise', $quotation->id) }}" method="POST" class="d-inline" id="revise-form">
                    @csrf
                    <button type="submit" class="btn btn-outline-warning">
                        <i class="bi bi-arrow-repeat me-1"></i> Revise
                    </button>
                </form>
                <a href="{{ route('quotations.edit', $quotation->id) }}" class="btn btn-primary">
                    <i class="bi bi-pencil me-1"></i> Edit
                </a>
                @endif
                <a href="{{ route('quotations.index') }}" class="btn btn-outline-secondary">Back</a>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="p-3 bg-light rounded-3 h-100">
                    <div class="small text-muted mb-1 text-uppercase fw-bold ls-1">Company</div>
                    <div class="fw-bold">{{ $quotation->company->name }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 bg-light rounded-3 h-100">
                    <div class="small text-muted mb-1 text-uppercase fw-bold ls-1">Enquiry</div>
                    <div class="fw-bold">
                        @if($quotation->enquiry)
                            <a href="{{ route('enquiries.show', $quotation->enquiry->id) }}" class="text-decoration-none">
                                {{ $quotation->enquiry->name }}
                            </a>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 bg-light rounded-3 h-100">
                    <div class="small text-muted mb-1 text-uppercase fw-bold ls-1">Date & Validity</div>
                    <div class="fw-bold">{{ $quotation->quotation_date->format('d M Y') }}</div>
                    <div class="small text-muted">Valid till: {{ $quotation->valid_till ? $quotation->valid_till->format('d M Y') : '-' }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 bg-light rounded-3 h-100">
                    <div class="small text-muted mb-1 text-uppercase fw-bold ls-1">Information</div>
                    <div class="fw-bold">Type: {{ str_replace('_', ' ', $quotation->quotation_type) }}</div>
                    <div class="small text-muted">By: {{ $quotation->createdBy->name ?? 'System' }}</div>
                </div>
            </div>
        </div>

        @if($quotation->parent_quotation_id || $quotation->revisions->count() > 0)
        <div class="section-style mb-4 border-start border-4 border-info">
            <div class="section-title py-0 h6">
                <i class="bi bi-clock-history me-2"></i> Version History
            </div>
            <div class="mt-3">
                @if($quotation->parent_quotation_id)
                    <div class="mb-2">
                        <span class="text-muted">Parent:</span>
                        <a href="{{ route('quotations.show', $quotation->parent_quotation_id) }}" class="badge bg-light text-dark text-decoration-none border">
                            {{ $quotation->parentQuotation->quotation_no }}
                        </a>
                    </div>
                @endif
                @if($quotation->revisions->count() > 0)
                    <div>
                        <span class="text-muted">Revisions:</span>
                        @foreach($quotation->revisions as $rev)
                            <a href="{{ route('quotations.show', $rev->id) }}" class="badge bg-warning text-dark text-decoration-none ms-1">
                                {{ $rev->quotation_no }}
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
        @endif

        <div class="section-style mb-4">
            <div class="section-title">
                <i class="bi bi-list-ul me-2"></i> Line Items
            </div>
            <div class="table-responsive mt-3">
                <table class="table table-hover align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th width="50">#</th>
                            <th>Service & Item</th>
                            <th>Description</th>
                            <th width="80">Unit</th>
                            <th width="100">Qty</th>
                            <th width="120">Rate</th>
                            <th width="100">GST</th>
                            <th width="150" class="text-end">Line Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($quotation->items as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <div class="fw-bold">{{ $item->service->name }}</div>
                                <div class="small text-muted">{{ $item->serviceItem->item_name }}</div>
                            </td>
                            <td>{{ $item->description ?: '-' }}</td>
                            <td>{{ $item->unit }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>₹ {{ number_format($item->selling_rate, 2) }}</td>
                            <td>{{ $item->gst_percentage }}%</td>
                            <td class="text-end fw-bold">₹ {{ number_format($item->line_total, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row justify-content-end">
            <div class="col-md-4">
                <div class="card bg-light border-0">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal:</span>
                            <span class="fw-bold">₹ {{ number_format($quotation->subtotal, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">GST Total:</span>
                            <span class="fw-bold">₹ {{ number_format($quotation->gst_total, 2) }}</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between h5 mb-0">
                            <span class="fw-bold">Grand Total:</span>
                            <span class="fw-bold text-primary">₹ {{ number_format($quotation->grand_total, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($quotation->terms_content || $quotation->termsCondition)
        <div class="section-style mt-5">
            <div class="section-title">
                <i class="bi bi-shield-check me-2"></i> Terms & Conditions
            </div>
            <div class="mt-3 p-4 bg-light rounded-3">
                @if($quotation->terms_content)
                    {!! $quotation->terms_content !!}
                @else
                    <div class="fw-bold mb-2 text-primary">{{ $quotation->termsCondition->title }}</div>
                    {!! $quotation->termsCondition->content !!}
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(function(){
    $('#revise-form').on('submit', function(e){
        if(!confirm('Create a new revision of this quotation?')) {
            e.preventDefault();
        }
    });
});
</script>
@endpush

@endsection
