@extends('layouts.backend')

@section('title','Edit Purchase Order')

@section('content')
<div class="main-content">
    <div class="card form-card shadow-lg border-0">
        <div class="card-body p-5">
            <form id="po-form">
                <input type="hidden" id="po_id" value="{{ $po->id }}" />
                
                <!-- Supplier search / add -->
                <div class="d-flex justify-content-center mb-5 align-items-center">
                    <div class="position-relative me-2" style="width: 350px;">
                        <input class="form-control custom-input" id="supplier_text" placeholder="Search supplier by name..." value="{{ optional($po->supplier)->name }}">
                        <div id="supplier-suggestions" class="list-group position-absolute w-100 shadow-sm mt-1 d-none" style="z-index:100;"></div>
                    </div>
                    <button type="button" class="btn btn-custom px-3 py-2" id="add-supplier-btn" title="Add New Supplier">
                        <i class="bi bi-person-plus-fill"></i>
                    </button>
                </div>

                <!-- Supplier details preview -->
                <div class="section-style mb-4">
                    <div class="section-title">
                        <i class="bi bi-truck me-2"></i> Supplier Details
                    </div>
                    <div class="row g-4 mt-2">
                        <div class="col-md-3">
                            <div class="p-3 rounded-4 bg-light bg-opacity-50 text-center h-100 border">
                                <small class="text-muted d-block mb-1">Company Name</small>
                                <div class="fw-bold" id="supplier_company">{{ optional($po->supplier)->name }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 rounded-4 bg-light bg-opacity-50 text-center h-100 border">
                                <small class="text-muted d-block mb-1">Contact Person</small>
                                <div class="fw-bold" id="supplier_contact">{{ optional($po->supplier)->contact_person }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 rounded-4 bg-light bg-opacity-50 text-center h-100 border">
                                <small class="text-muted d-block mb-1">Phone</small>
                                <div class="fw-bold" id="supplier_phone">{{ optional($po->supplier)->mobile }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 rounded-4 bg-light bg-opacity-50 text-center h-100 border">
                                <small class="text-muted d-block mb-1">GSTIN</small>
                                <div class="fw-bold" id="supplier_gst">{{ optional($po->supplier)->gst_no }}</div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="p-3 rounded-4 bg-light bg-opacity-50 h-100 border">
                                <small class="text-muted d-block mb-1">Address</small>
                                <div class="fw-bold" id="supplier_address">{{ optional($po->supplier)->address_line1 }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Other details -->
                <div class="section-style mb-4">
                    <div class="section-title">
                        <i class="bi bi-info-circle me-2"></i> PO Information
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">PO Number</label>
                            <input type="text" id="po_number" name="po_number" class="form-control custom-input" value="{{ $po->po_number }}" />
                            <div class="invalid-feedback" id="po_number-error"></div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" id="date" name="date" class="form-control custom-input" value="{{ $po->date ? $po->date->format('Y-m-d') : '' }}" />
                            <div class="invalid-feedback" id="date-error"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Issuing Company <span class="text-danger">*</span></label>
                            <select name="company_id" class="form-select custom-input me-2">
                                <option value="">Choose Company</option>
                                @foreach($companies as $comp)
                                    <option value="{{ $comp->id }}" @if($po->company_id == $comp->id) selected @endif>{{ $comp->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Project Name <span class="text-danger">*</span></label>
                            <select name="project_id" id="project_id" class="form-select custom-input me-2">
                                <option value="">Choose Project</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}" @if($po->project_id==$project->id) selected @endif>{{ $project->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Site Engineer <span class="text-danger">*</span></label>
                            <select name="site_engineer_id" id="site_engineer_id" class="form-select custom-input me-2">
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
                    <div class="section-title">
                        <i class="bi bi-box-seam me-2"></i> Item Selection
                    </div>
                    <div id="productContainer" class="mt-4 mb-2">
                        <div class="row justify-content-center g-2 align-items-center">
                            <div class="col-md-6">
                                <div class="position-relative">
                                    <input id="product_text" class="form-control custom-input" placeholder="Search product by name, category or uom..." autocomplete="off" />
                                    <div id="product-suggestions" class="list-group position-absolute w-100 shadow mt-1 d-none" style="z-index:100;"></div>
                                    <input type="hidden" id="product_id" />
                                </div>
                            </div>
                            <div class="col-auto">
                                <button type="button" class="btn btn-primary px-4" onclick="submitProduct()">
                                    <i class="bi bi-plus-circle me-1"></i> Add Item
                                </button>
                            </div>
                        </div>
                    </div>

                    <div id="quotationContainer" class="table-responsive mt-4"></div>
                </div>

                <div class="col-md-12 mb-3 mt-5">
                    <hr class="text-muted opacity-25 mb-4">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-custom px-5" id="submitBtn">
                            <i class="bi bi-check2-circle me-1"></i> Update Purchase Order
                        </button>
                        <a href="{{ route('purchaseOrders.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                    </div>
                </div>

                <input type="hidden" id="supplier_id" name="supplier_id" value="{{ $po->supplier_id }}" />
            </form>
        </div>
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
