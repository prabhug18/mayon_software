@extends('layouts.backend')
@section('title','Create Quotation')

@push('head')
<!-- Summernote Lite CSS -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<style>
    .note-editor.note-frame {
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        background-color: #fff;
    }
</style>
@endpush
@section('content')
<div class="card form-card shadow-lg border-0">
    <div class="card-body p-5">
        <form id="quotation-form">
            <div class="section-style mb-4">
                <div class="section-title">
                    <i class="bi bi-info-circle me-2"></i> Basic Information
                </div>
                <div class="row mt-4">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Company <span class="text-danger">*</span></label>
                        <select class="form-select custom-input" id="company_id" name="company_id" required>
                            <option value="">Select Company</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="company_id-error"></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Quotation Number</label>
                        <input type="text" class="form-control" id="quotation_no" name="quotation_no" readonly placeholder="Auto-generated" style="background-color: #f8f9fa; border: 1px dashed #dee2e6;" />
                        <div class="invalid-feedback" id="quotation_no-error"></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Enquiry (Optional)</label>
                        <select class="form-select custom-input" id="enquiry_id" name="enquiry_id">
                            <option value="">Select Enquiry</option>
                            @foreach($enquiries as $enq)
                                <option value="{{ $enq->id }}" {{ isset($enquiry) && $enquiry->id == $enq->id ? 'selected' : '' }}>
                                    {{ $enq->name }} - {{ $enq->mobile }}
                                </option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="enquiry_id-error"></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Customer Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control custom-input" id="customer_name" name="customer_name" placeholder="Enter Customer Name" required />
                        <div class="invalid-feedback" id="customer_name-error"></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Kind Att :</label>
                        <input type="text" class="form-control custom-input" id="kind_att" name="kind_att" placeholder="e.g. Mr. John Doe" />
                        <div class="invalid-feedback" id="kind_att-error"></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Customer Address / Location <span class="text-danger">*</span></label>
                        <textarea class="form-control custom-input" id="customer_address" name="customer_address" rows="1" placeholder="Enter Customer Address" required></textarea>
                        <div class="invalid-feedback" id="customer_address-error"></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Quotation Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control custom-input" id="quotation_date" name="quotation_date" value="{{ date('Y-m-d') }}" required />
                        <div class="invalid-feedback" id="quotation_date-error"></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Valid Till</label>
                        <input type="date" class="form-control custom-input" id="valid_till" name="valid_till" value="{{ date('Y-m-d', strtotime('+30 days')) }}" />
                        <div class="invalid-feedback" id="valid_till-error"></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Quotation Type <span class="text-danger">*</span></label>
                        <select class="form-select custom-input" id="quotation_type" name="quotation_type" required>
                            <option value="OWN">Own Work</option>
                            <option value="THIRD_PARTY">Third Party</option>
                            <option value="MIXED">Mixed</option>
                        </select>
                        <div class="invalid-feedback" id="quotation_type-error"></div>
                    </div>
                </div>
            </div>

            <div class="section-style mb-4">
                <div class="section-title">
                    <i class="bi bi-list-check me-2"></i> Line Items
                </div>
                <div class="table-responsive mt-3">
                    <table class="table table-hover align-middle" id="items-table">
                        <thead class="bg-light">
                            <tr>
                                <th width="180">Service</th>
                                <th width="180">Item</th>
                                <th width="80">Unit</th>
                                <th width="80">Qty</th>
                                <th width="100">Base Cost</th>
                                <th width="120">Margin Type</th>
                                <th width="100">Margin</th>
                                <th width="110">Selling Rate</th>
                                <th width="80">GST %</th>
                                <th width="120">Line Total</th>
                                <th width="50" class="text-center">#</th>
                            </tr>
                        </thead>
                        <tbody id="items-tbody">
                            <!-- Items will be added here dynamically -->
                        </tbody>
                    </table>
                </div>
                <div class="mt-2">
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" id="add-item-btn">
                        <i class="bi bi-plus-lg me-1"></i> Add New Item
                    </button>
                </div>
            </div>

            <div class="section-style mb-4">
                <div class="section-title">
                    <i class="bi bi-calculator me-2"></i> Calculation Summary
                </div>
                <div class="row mt-4">
                    <div class="col-md-4 offset-md-8">
                        <div class="card bg-light border-0">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Subtotal:</span>
                                    <span id="subtotal-display" class="fw-bold">₹ 0.00</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">GST Total:</span>
                                    <span id="gst-total-display" class="fw-bold">₹ 0.00</span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <span class="h6 mb-0 fw-bold">Grand Total:</span>
                                    <span class="h6 mb-0 fw-bold text-primary" id="grand-total-display">₹ 0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section-style mb-4">
                <div class="section-title">
                    <i class="bi bi-gear me-2"></i> Additional Settings
                </div>
                <div class="row mt-4">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Terms & Conditions</label>
                        <select class="form-select custom-input" id="terms_condition_id" name="terms_condition_id">
                            <option value="">Select Terms & Conditions</option>
                            @foreach($termsConditions as $tc)
                                <option value="{{ $tc->id }}">{{ $tc->title }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="terms_condition_id-error"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select custom-input" id="status" name="status">
                            <option value="DRAFT" selected>Draft</option>
                            <option value="SENT">Sent</option>
                            <option value="APPROVED">Approved</option>
                        </select>
                        <div class="invalid-feedback" id="status-error"></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 mb-3">
                        <label class="form-label">Editable Terms & Conditions Content</label>
                        <textarea id="terms_content" name="terms_content" class="form-control"></textarea>
                        <div class="invalid-feedback" id="terms_content-error"></div>
                        <small class="text-muted">Select a template above to load its content, then customize it for this quotation.</small>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('quotations.index') }}" class="btn btn-outline-secondary">Back</a>
                <button type="submit" class="btn btn-custom px-5 py-2" id="submitBtn">Create Quotation</button>
            </div>
        </form>
    </div>
</div>

<!-- Item Row Template -->
<template id="item-row-template">
    <tr class="item-row item-main-row">
        <td>
            <div class="row-select-container">
                <select class="form-select form-select-sm service-select" name="items[INDEX][service_id]" required>
                    <option value="">Select Service</option>
                    @foreach($services as $category => $categoryServices)
                        <optgroup label="{{ $category ?: 'Other' }}">
                            @foreach($categoryServices as $service)
                                <option value="{{ $service->id }}">{{ $service->name }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
                <input type="text" class="form-control form-control-sm manual-service-input d-none" name="items[INDEX][manual_service_name]" placeholder="Manual Service" />
                <div class="mt-1">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input manual-toggle" type="checkbox" id="manual_INDEX">
                        <label class="form-check-label small" for="manual_INDEX">Manual</label>
                    </div>
                </div>
            </div>
        </td>
        <td>
            <select class="form-select form-select-sm service-item-select" name="items[INDEX][service_item_id]" required>
                <option value="">Select Item</option>
            </select>
            <input type="text" class="form-control form-control-sm manual-item-input d-none" name="items[INDEX][manual_item_name]" placeholder="Manual Item" />
        </td>
        <td>
            <select class="form-select form-select-sm unit-input" name="items[INDEX][unit]" required>
                <option value="SQM">SQM</option>
                <option value="RMT">RMT</option>
                <option value="SFT">SFT</option>
                <option value="NOS">NOS</option>
                <option value="LS">LS</option>
            </select>
        </td>
        <td>
            <input type="number" class="form-control form-control-sm quantity-input" name="items[INDEX][quantity]" value="1" step="0.01" min="0.01" required />
        </td>
        <td>
            <input type="number" class="form-control form-control-sm base-cost-input" name="items[INDEX][base_cost]" value="0" step="0.01" min="0" />
        </td>
        <td>
            <select class="form-select form-select-sm margin-type-select" name="items[INDEX][margin_type]" required>
                <option value="PERCENTAGE">Percentage</option>
                <option value="FIXED">Fixed</option>
            </select>
        </td>
        <td>
            <input type="number" class="form-control form-control-sm margin-value-input" name="items[INDEX][margin_value]" value="0" step="0.01" min="0" required />
        </td>
        <td>
            <input type="number" class="form-control form-control-sm selling-rate-input" name="items[INDEX][selling_rate]" value="0" step="0.01" min="0" required />
        </td>
        <td>
            <input type="number" class="form-control form-control-sm gst-input" name="items[INDEX][gst_percentage]" value="18" step="0.01" min="0" max="100" required />
        </td>
        <td>
            <input type="text" class="form-control form-control-sm line-total-input" readonly value="0.00" />
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-danger remove-item-btn">×</button>
        </td>
    </tr>
    <tr class="item-row item-desc-row">
        <td colspan="11">
            <textarea class="form-control form-control-sm description-input" name="items[INDEX][description]" rows="2" placeholder="Item Description..."></textarea>
        </td>
    </tr>
</template>

@push('scripts')
<!-- Summernote Lite JS -->
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
<script src="{{ asset('assets/js/quotation.js') }}"></script>
<script>
$(function(){ 
    if (window.Quotation && typeof Quotation.initCreateForm === 'function') {
        Quotation.initCreateForm('#quotation-form');
    }
});
</script>
@endpush

@endsection
