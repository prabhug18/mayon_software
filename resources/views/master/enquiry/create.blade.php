@extends('layouts.backend')
@section('title','New Enquiry')
@section('content')
<div class="card form-card shadow-lg border-0">
    <div class="card-body p-5">
        <form id="enquiry-form">
            <div class="section-style mb-4">
                <div class="section-title">
                    <i class="bi bi-person-lines-fill me-2"></i> Client Information
                </div>
                <div class="row mt-4">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Client Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control custom-input" id="name" name="name" placeholder="Contact Person Name" required />
                        <div class="invalid-feedback" id="name-error"></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-control custom-input" id="email" name="email" placeholder="client@example.com" />
                        <div class="invalid-feedback" id="email-error"></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Mobile Number</label>
                        <input type="text" class="form-control custom-input" id="mobile" name="mobile" placeholder="e.g. 9876543210" />
                        <div class="invalid-feedback" id="mobile-error"></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">GSTIN</label>
                        <input type="text" class="form-control custom-input" id="gstin" name="gstin" placeholder="GST Number" />
                        <div class="invalid-feedback" id="gstin-error"></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Location / City</label>
                        <input type="text" class="form-control custom-input" id="location" name="location" placeholder="City / Area" />
                        <div class="invalid-feedback" id="location-error"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Full Address</label>
                    <textarea class="form-control custom-input" id="address" name="address" rows="2" placeholder="Site or Office Address..."></textarea>
                    <div class="invalid-feedback" id="address-error"></div>
                </div>
            </div>

            <div class="section-style mb-4">
                <div class="section-title">
                    <i class="bi bi-info-circle me-2"></i> Enquiry Details
                </div>
                <div class="row mt-4">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Enquiry Type</label>
                        <select class="form-select custom-input" id="enquiry_type_id" name="enquiry_type_id">
                            <option selected disabled>Select Enquiry Type</option>
                            @foreach($enquiryTypes as $et)
                                <option value="{{ $et->id }}">{{ $et->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="enquiry_type_id-error"></div>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Source</label>
                        <select class="form-select custom-input" id="source_id" name="source_id">
                            <option selected disabled>Select Source</option>
                            @foreach($sources as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="source_id-error"></div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Project (Optional)</label>
                        <select class="form-select custom-input" id="project_id" name="project_id">
                            <option selected disabled>Select Project</option>
                            @foreach($projects as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="project_id-error"></div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Service Category</label>
                        <select class="form-select custom-input" id="service_id" name="service_id">
                            <option selected disabled>Select Service</option>
                            @foreach($services as $category => $catServices)
                                <optgroup label="{{ $category ?: 'General' }}">
                                    @foreach($catServices as $service)
                                        <option value="{{ $service->id }}">{{ $service->name }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="service_id-error"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Specific Item / Work</label>
                        <select class="form-select custom-input" id="service_item_id" name="service_item_id">
                            <option selected disabled>Select Service Item</option>
                        </select>
                        <div class="invalid-feedback" id="service_item_id-error"></div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Requirements Description</label>
                    <textarea class="form-control custom-input" id="description" name="description" rows="3" placeholder="Provide more details about the enquiry..."></textarea>
                    <div class="invalid-feedback" id="description-error"></div>
                </div>
            </div>

            <div class="section-style mb-4">
                <div class="section-title">
                    <i class="bi bi-flag me-2"></i> Tracking & Assignment
                </div>
                <div class="row mt-4">
                    <div class="col-md-4 mb-3">                
                        <label class="form-label">Current Status</label>
                        <select class="form-select custom-input" id="status" name="status">
                            <option value="Open" selected>Open</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Quotation Sent">Quotation Sent</option>
                            <option value="Won">Won</option>
                            <option value="Lost">Lost</option>
                            <option value="Closed">Closed</option>
                        </select>
                        <div class="invalid-feedback" id="status-error"></div>
                    </div>
                    <div class="col-md-4 mb-3">                
                        <label class="form-label">Priority</label>
                        <select class="form-select custom-input" id="priority" name="priority">
                            <option value="Low">Low</option>
                            <option value="Medium" selected>Medium</option>
                            <option value="High">High</option>
                            <option value="Urgent">Urgent</option>
                        </select>
                        <div class="invalid-feedback" id="priority-error"></div>
                    </div>
                    <div class="col-md-4 mb-3">                
                        <label class="form-label">Assign To Staff</label>
                        <select class="form-select custom-input" id="assigned_to" name="assigned_to">
                            <option value="" selected>Unassigned</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="assigned_to-error"></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Next Follow-up Date</label>
                        <input type="datetime-local" class="form-control custom-input" id="next_follow_up_at" name="next_follow_up_at" />
                        <div class="invalid-feedback" id="next_follow_up_at-error"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Follow-up Notes</label>
                        <textarea class="form-control custom-input" id="reminder_notes" name="reminder_notes" rows="2" placeholder="Notes for the next follow-up..."></textarea>
                        <div class="invalid-feedback" id="reminder_notes-error"></div>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-custom px-5 py-2" id="submitBtn">Create Enquiry</button>
                <a href="{{ route('enquiries.index') }}" class="btn btn-outline-secondary px-4 py-2">Back</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/js/enquiry.js') }}"></script>
<script>$(function(){ if (window.Enquiry && typeof Enquiry.initForm === 'function') Enquiry.initForm('#enquiry-form'); });</script>
@endpush

@endsection
