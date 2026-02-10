@extends('layouts.backend')
@section('title','Enquiry Details')
@section('content')
<div class="container">
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h4 class="mb-1" id="enq-name">{{ $enquiry->name ?? 'Enquiry Details' }}</h4>
                            
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('enquiries.edit', $enquiry->id) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                            <a href="{{ route('enquiries.index') }}" class="btn btn-sm btn-outline-secondary">Back</a>
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col-md-6">
                            <div class="mb-2"><strong>Mobile</strong><div id="enq-mobile" class="mt-1">{{ $enquiry->mobile ?? '-' }}</div></div>
                            <div class="mb-2"><strong>Type</strong><div id="enq-type" class="mt-1">@if($enquiry->enquiryType)<span class="badge bg-info text-dark">{{ $enquiry->enquiryType->name }}</span>@else<span class="text-muted">-</span>@endif</div></div>
                            <div class="mb-2"><strong>Reminder Notes</strong><div id="enq-reminder-notes" class="mt-2 text-muted">{{ $enquiry->reminder_notes ?? '-' }}</div></div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2"><strong>Location</strong><div id="enq-location" class="mt-1">{{ $enquiry->location ?? '-' }}</div></div>
                            <div class="mb-2"><strong>Next Follow-up</strong><div id="enq-next-follow" class="mt-1">{{ $enquiry->next_follow_up_at ? $enquiry->next_follow_up_at->format('M j, Y H:i') : '-' }}</div></div>
                            <div class="mb-2"><strong>Description</strong><div id="enq-description" class="mt-2 text-muted">{{ $enquiry->description ?? '-' }}</div></div>
                            <div class="mb-2"><strong>Status</strong><div id="enq-status" class="mt-1">@if($enquiry->status)<span class="badge bg-secondary">{{ $enquiry->status }}</span>@else<span class="text-muted">-</span>@endif</div></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                    <div class="card-body">
                    <h5 class="mb-3">Comments</h5>
                    <div id="comments-list" class="mb-3">
                        @foreach($enquiry->comments as $c)
                            <div class="card mb-2 p-2">
                                <div class="small text-muted">{{ optional($c->user)->name ?? 'System' }} &middot; {{ $c->created_at->format('M j, Y H:i') }}</div>
                                <div class="mt-1">{{ $c->body }}</div>
                            </div>
                        @endforeach
                    </div>
                    <form id="comment-form">
                        <div class="mb-2">
                            <textarea class="form-control" id="comment-body" name="body" rows="3" placeholder="Add a comment..."></textarea>
                        </div>
                        <button class="btn btn-primary" type="submit">Post Comment</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-3">
                    <div class="card-body">
                    <h6 class="mb-3">Quick Info</h6>
                    <p class="mb-1"><strong>Created:</strong> <span>{{ optional($enquiry->created_at)->format('M j, Y H:i') }}</span></p>
                    <p class="mb-1"><strong>Updated:</strong> <span>{{ optional($enquiry->updated_at)->diffForHumans() }}</span></p>
                    @php $lastFollow = $enquiry->followUps->sortByDesc('scheduled_at')->first(); @endphp
                    @if($lastFollow)
                        <p class="mb-1"><strong>Last Follow-up:</strong> <span class="quick-last-follow">{{ $lastFollow->scheduled_at->format('M j, Y H:i') }}</span></p>
                    @endif
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <h6 class="mb-3">Follow-up History</h6>
                    <div id="followups-list" class="list-group">
                        @forelse($enquiry->followUps->sortByDesc('scheduled_at') as $f)
                            <div class="list-group-item py-2" data-scheduled="{{ $f->scheduled_at->toIso8601String() }}" data-user="{{ optional($f->user)->name ?? 'System' }}" data-notes="{{ e($f->notes) }}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="fw-semibold small">{{ $f->scheduled_at->format('M j, Y H:i') }}</div>
                                    <div class="small text-muted">{{ optional($f->user)->name ?? 'System' }}</div>
                                </div>
                                @if($f->notes)
                                    <div class="text-muted mt-1 small">{{ $f->notes }}</div>
                                @endif
                            </div>
                        @empty
                            <div class="list-group-item py-2 small text-muted">No follow-ups</div>
                        @endforelse
                    </div>

                    <hr />
                    <form id="followup-form">
                        <div class="mb-2">
                            <label class="form-label">Schedule Next Follow-up</label>
                            <input type="datetime-local" id="followup-datetime" name="scheduled_at" class="form-control" />
                        </div>
                        <div class="mb-2">
                            <textarea id="followup-notes" name="notes" class="form-control" rows="2" placeholder="Optional note"></textarea>
                        </div>
                        <button class="btn btn-primary" type="submit">Add Follow-up</button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h6 class="mb-3">Actions</h6>
                    <a href="{{ route('enquiries.edit', $enquiry->id) }}" class="btn btn-block btn-outline-primary mb-2">Edit Enquiry</a>
                    <a href="{{ route('enquiries.index') }}" class="btn btn-block btn-outline-secondary">All Enquiries</a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/js/enquiry.js') }}"></script>
<script>$(function(){ if (window.Enquiry && typeof Enquiry.initShow === 'function') Enquiry.initShow({{ $enquiry->id }}); });</script>
@endpush

@endsection
