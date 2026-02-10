@extends('layouts.backend')
@section('title','Edit Enquiry')
@section('content')
<div class="card form-card shadow-lg border-0">
    <div class="card-body p-5">
        <form id="enquiry-edit-form">
            <input type="hidden" name="exclude_id" value="{{ $enquiry->id }}" />
            <div class="section-style mb-4">
                <div class="section-title">Contact Details</div>
                <div class="row mt-4">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Mobile Number</label>
                        <input type="text" class="form-control custom-input" id="mobile" name="mobile" value="{{ $enquiry->mobile }}" />
                        <div class="invalid-feedback" id="mobile-error"></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control custom-input" id="name" name="name" value="{{ $enquiry->name }}" />
                        <div class="invalid-feedback" id="name-error"></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" class="form-control custom-input" id="location" name="location" value="{{ $enquiry->location }}" />
                        <div class="invalid-feedback" id="location-error"></div>
                    </div>
                </div>
            </div>

            <div class="section-style mb-4">
                <div class="section-title">Enquiry Details</div>
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
                            <option selected disabled>Select source</option>
                            @foreach($sources as $s)
                                <option value="{{ $s->id }}" @if($enquiry->source_id == $s->id) selected @endif>{{ $s->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="source_id-error"></div>
                    </div>
                    <div class="col-md-4 mb-3">                
                        <label class="form-label">Status</label>
                        <select class="form-select custom-input" id="status" name="status">
                            <option selected disabled>Select status</option>
                            <option @if($enquiry->status=='Open') selected @endif>Open</option>
                            <option @if($enquiry->status=='In Progress') selected @endif>In Progress</option>
                            <option @if($enquiry->status=='Closed') selected @endif>Closed</option>
                        </select>
                        <div class="invalid-feedback" id="status-error"></div>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control custom-input" id="description" name="description" rows="3">{{ $enquiry->description }}</textarea>
                        <div class="invalid-feedback" id="description-error"></div>
                    </div>
                </div>
            </div>
            <div class="section-style mb-4">
                <div class="section-title">Follow-up</div>
                <div class="row mt-4">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Next Follow-up Date</label>
                        <input type="datetime-local" class="form-control custom-input" id="next_follow_up_at" name="next_follow_up_at" value="{{ optional($enquiry->next_follow_up_at)->format('Y-m-d\TH:i') }}" />
                        <div class="invalid-feedback" id="next_follow_up_at-error"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Reminder Notes</label>
                        <textarea class="form-control custom-input" id="reminder_notes" name="reminder_notes" rows="2">{{ $enquiry->reminder_notes }}</textarea>
                        <div class="invalid-feedback" id="reminder_notes-error"></div>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('enquiries.index') }}" class="btn btn-outline-secondary">Back</a>
                <button type="submit" class="btn btn-custom px-5 py-2" id="submitBtn">Submit</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/js/enquiry.js') }}"></script>
<script>$(function(){ if (window.Enquiry && typeof Enquiry.initEditForm === 'function') Enquiry.initEditForm('#enquiry-edit-form', {{ $enquiry->id }}); });</script>
@endpush

@endsection
