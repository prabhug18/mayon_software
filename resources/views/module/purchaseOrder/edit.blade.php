@extends('layouts.backend')

@section('title','Edit Purchase Order')

@section('content')
<div class="main-content">
    <div class="card form-card shadow-lg border-0">
        <div class="card-body p-5">
            <form id="po-form">
                <input type="hidden" id="po_id" value="{{ $po->id }}" />
                <!-- Supplier search / add -->
                <div class="d-flex justify-content-center mb-3 align-items-center">
                    <div class="position-relative me-2" style="width: 350px;">
                        <input class="form-control custom-input" id="supplier_text" placeholder="Search supplier..." value="{{ optional($po->supplier)->name }}">
                        <div id="supplier-suggestions" class="list-group position-absolute w-100 shadow-sm mt-1 d-none" style="z-index:10;"></div>
                    </div>
                    <button type="button" class="add-btn-custom btn-sm" id="add-supplier-btn"> <i class="bi bi-plus-lg"></i> </button>
                </div>

                <!-- Customer details preview (cards) -->
                <div class="section-style mb-4">
                    <div class="section-title mb-3 fw-bold">Customer Details</div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="card shadow-sm rounded-4 p-3 text-center h-100">
                                <div class="fw-bold text-secondary mb-1">Company Name</div>
                                <div class="fs-5 fw-semibold" id="supplier_company">{{ optional($po->supplier)->name }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card shadow-sm rounded-4 p-3 text-center h-100">
                                <div class="fw-bold text-secondary mb-1">Contact Person</div>
                                <div class="fs-5 fw-semibold" id="supplier_contact">{{ optional($po->supplier)->contact_person }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card shadow-sm rounded-4 p-3 text-center h-100">
                                <div class="fw-bold text-secondary mb-1">Phone</div>
                                <div class="fs-5 fw-semibold" id="supplier_phone">{{ optional($po->supplier)->mobile }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card shadow-sm rounded-4 p-3 text-center h-100">
                                <div class="fw-bold text-secondary mb-1">GSTIN</div>
                                <div class="fs-5 fw-semibold" id="supplier_gst">{{ optional($po->supplier)->gst_no }}</div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="card shadow-sm rounded-4 p-3 h-100">
                                <div class="fw-bold text-secondary mb-1">Address</div>
                                <div class="fs-5 fw-semibold" id="supplier_address">{{ optional($po->supplier)->address_line1 }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Other details -->
                <div class="section-style mb-4">
                    <div class="section-title">Other Details</div>
                    <div class="row mb-3">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">PO Number</label>
                            <input type="text" id="po_number" name="po_number" class="form-control custom-input" style="max-width:300px;" value="{{ $po->po_number }}" />
                            <div class="invalid-feedback" id="po_number-error"></div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Date</label>
                            <input type="date" id="date" name="date" class="form-control custom-input" style="max-width:300px;" value="{{ $po->date ? $po->date->format('Y-m-d') : '' }}" />
                            <div class="invalid-feedback" id="date-error"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Company Name</label>
                            <select name="company_id" class="form-select custom-input me-2" style="max-width:300px;">
                                <option value="">Choose Company</option>
                                @foreach($companies as $comp)
                                    <option value="{{ $comp->id }}" @if($po->company_id == $comp->id) selected @endif>{{ $comp->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Projects</label>
                            <select name="project_id" id="project_id" class="form-select custom-input me-2" style="max-width:300px;">
                                <option value="">Choose Project</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}" @if($po->project_id==$project->id) selected @endif>{{ $project->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Site Engineers</label>
                            <select name="site_engineer_id" id="site_engineer_id" class="form-select custom-input me-2" style="max-width:300px;">
                                <option value="">Choose Site Engineer</option>
                                @foreach(\App\Models\User::whereHas('roles', function($q){ $q->where('name','Site Engineer'); })->get() as $se)
                                    <option value="{{ $se->id }}" @if($po->site_engineer_id == $se->id) selected @endif>{{ $se->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Product / Items -->
                <div class="section-style mb-4">
                    <div id="productContainer" class="mb-4">
                        <div class="section-title">Add Product</div>
                        <div class="d-flex justify-content-center mb-3 align-items-center">
                            <div style="position:relative; max-width:300px; width:100%;">
                                <input id="product_text" class="form-control custom-input" placeholder="Search product by name, category or uom..." autocomplete="off" />
                                <div id="product-suggestions" class="list-group position-absolute w-100 shadow-sm mt-1 d-none" style="z-index:10;"></div>
                                <input type="hidden" id="product_id" />
                            </div>
                            <button type="button" class="add-btn-custom btn-sm" onclick="submitProduct()">Add Product</button>
                        </div>
                    </div>
                </div>

                <div class="section-style mb-4">
                    <div id="quotationContainer"></div>
                </div>

                <div class="text-center">
                    <a href="{{ route('purchaseOrders.index') }}" class="btn btn-outline-secondary me-2">Back</a>
                    <button type="submit" class="add-btn-custom btn-sm">Submit</button>
                </div>

                <input type="hidden" id="supplier_id" name="supplier_id" value="{{ $po->supplier_id }}" />
            </form>
        </div>
    </div>

    <!-- Supplier modal (kept) -->
    <div class="modal fade" id="supplierModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header"><h5 class="modal-title">Add Supplier</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
          <div class="modal-body">
            <form id="supplier-modal-form">
              <div class="row">
                <div class="col-md-6 mb-3"><label>Name</label><input type="text" id="modal_name" name="name" class="form-control" required /><div class="invalid-feedback" id="modal_name-error"></div></div>
                <div class="col-md-6 mb-3"><label>Contact Person</label><input type="text" id="modal_contact_person" name="contact_person" class="form-control" /><div class="invalid-feedback" id="modal_contact_person-error"></div></div>
              </div>
              <div class="row">
                <div class="col-md-6 mb-3"><label>Mobile</label><input type="text" id="modal_mobile" name="mobile" class="form-control" /><div class="invalid-feedback" id="modal_mobile-error"></div></div>
                <div class="col-md-6 mb-3"><label>Email</label><input type="email" id="modal_email" name="email" class="form-control" /><div class="invalid-feedback" id="modal_email-error"></div></div>
              </div>
              <div class="mb-3"><label>Location</label><input type="text" id="modal_location" name="location" class="form-control" /><div class="invalid-feedback" id="modal_location-error"></div></div>
            </form>
          </div>
          <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button id="modal-save-supplier" class="btn btn-custom">Save</button></div>
        </div>
      </div>
    </div>

    @push('scripts')
    <script src="{{ asset('assets/js/purchaseOrder.js') }}"></script>
    <script>
        // provide existing items for edit page
        window.PO_EDIT_ITEMS = {!! json_encode($po->items->map(function($it){ return ['description'=>$it->description,'quantity'=>$it->quantity,'unit_price'=>$it->unit_price,'total'=>$it->total]; })) !!};
    </script>
    @endpush

</div>

@endsection
