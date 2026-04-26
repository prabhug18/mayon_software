@extends('layouts.backend')
@section('title','Edit Enquiry')
@section('content')
<div class="card form-card shadow-lg border-0">
    <div class="card-body p-5">
        <form id="enquiry-edit-form">
            <input type="hidden" name="exclude_id" value="{{ $enquiry->id }}" />
            <div class="section-style mb-4">
                <div class="section-title">
                    <i class="bi bi-person-lines-fill me-2"></i> Client Information
                </div>
                <div class="row mt-4">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Client Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control custom-input" id="name" name="name" value="{{ $enquiry->name }}" placeholder="Contact Person Name" required />
                        <div class="invalid-feedback" id="name-error"></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-control custom-input" id="email" name="email" value="{{ $enquiry->email }}" placeholder="client@example.com" />
                        <div class="invalid-feedback" id="email-error"></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Mobile Number</label>
                        <input type="text" class="form-control custom-input" id="mobile" name="mobile" value="{{ $enquiry->mobile }}" placeholder="e.g. 9876543210" />
                        <div class="invalid-feedback" id="mobile-error"></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">GSTIN</label>
                        <input type="text" class="form-control custom-input" id="gstin" name="gstin" value="{{ $enquiry->gstin }}" placeholder="GST Number" />
                        <div class="invalid-feedback" id="gstin-error"></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Location / City</label>
                        <input type="text" class="form-control custom-input" id="location" name="location" value="{{ $enquiry->location }}" placeholder="City / Area" />
                        <div class="invalid-feedback" id="location-error"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Full Address</label>
                    <textarea class="form-control custom-input" id="address" name="address" rows="2" placeholder="Site or Office Address...">{{ $enquiry->address }}</textarea>
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
                                <option value="{{ $et->id }}" @if($enquiry->enquiry_type_id == $et->id) selected @endif>{{ $et->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="enquiry_type_id-error"></div>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Source</label>
                        <select class="form-select custom-input" id="source_id" name="source_id">
                            <option selected disabled>Select Source</option>
                            @foreach($sources as $s)
                                <option value="{{ $s->id }}" @if($enquiry->source_id == $s->id) selected @endif>{{ $s->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="source_id-error"></div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Project (Optional)</label>
                        <select class="form-select custom-input" id="project_id" name="project_id">
                            <option selected disabled>Select Project</option>
                            @foreach($projects as $p)
                                <option value="{{ $p->id }}" @if($enquiry->project_id == $p->id) selected @endif>{{ $p->name }}</option>
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
                                        <option value="{{ $service->id }}" @if($enquiry->service_id == $service->id) selected @endif>{{ $service->name }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="service_id-error"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Specific Item / Work</label>
                        <select class="form-select custom-input" id="service_item_id" name="service_item_id" data-selected="{{ $enquiry->service_item_id }}">
                            <option selected disabled>Select Service Item</option>
                        </select>
                        <div class="invalid-feedback" id="service_item_id-error"></div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Requirements Description</label>
                    <textarea class="form-control custom-input" id="description" name="description" rows="3" placeholder="Provide more details about the enquiry...">{{ $enquiry->description }}</textarea>
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
                            <option value="Open" @if($enquiry->status=='Open') selected @endif>Open</option>
                            <option value="In Progress" @if($enquiry->status=='In Progress') selected @endif>In Progress</option>
                            <option value="Quotation Sent" @if($enquiry->status=='Quotation Sent') selected @endif>Quotation Sent</option>
                            <option value="Won" @if($enquiry->status=='Won') selected @endif>Won</option>
                            <option value="Lost" @if($enquiry->status=='Lost') selected @endif>Lost</option>
                            <option value="Closed" @if($enquiry->status=='Closed') selected @endif>Closed</option>
                        </select>
                        <div class="invalid-feedback" id="status-error"></div>
                    </div>
                    <div class="col-md-4 mb-3">                
                        <label class="form-label">Priority</label>
                        <select class="form-select custom-input" id="priority" name="priority">
                            <option value="Low" @if($enquiry->priority=='Low') selected @endif>Low</option>
                            <option value="Medium" @if($enquiry->priority=='Medium') selected @endif>Medium</option>
                            <option value="High" @if($enquiry->priority=='High') selected @endif>High</option>
                            <option value="Urgent" @if($enquiry->priority=='Urgent') selected @endif>Urgent</option>
                        </select>
                        <div class="invalid-feedback" id="priority-error"></div>
                    </div>
                    <div class="col-md-4 mb-3">                
                        <label class="form-label">Assign To Staff</label>
                        <select class="form-select custom-input" id="assigned_to" name="assigned_to">
                            <option value="" @if(!$enquiry->assigned_to) selected @endif>Unassigned</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" @if($enquiry->assigned_to == $user->id) selected @endif>{{ $user->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="assigned_to-error"></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Next Follow-up Date</label>
                        <input type="datetime-local" class="form-control custom-input" id="next_follow_up_at" name="next_follow_up_at" value="{{ optional($enquiry->next_follow_up_at)->format('Y-m-d\TH:i') }}" />
                        <div class="invalid-feedback" id="next_follow_up_at-error"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Follow-up Notes</label>
                        <textarea class="form-control custom-input" id="reminder_notes" name="reminder_notes" rows="2" placeholder="Notes for the next follow-up...">{{ $enquiry->reminder_notes }}</textarea>
                        <div class="invalid-feedback" id="reminder_notes-error"></div>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-custom px-5 py-2" id="submitBtn">Update Enquiry</button>
                <a href="{{ route('enquiries.index') }}" class="btn btn-outline-secondary px-4 py-2">Back</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/js/enquiry.js') }}"></script>
<script>$(function(){ if (window.Enquiry && typeof Enquiry.initEditForm === 'function') Enquiry.initEditForm('#enquiry-edit-form', {{ $enquiry->id }}); });</script>
@endpush

@endsection
