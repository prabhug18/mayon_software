@extends('layouts.backend')
@section('title','Enquiry Details')
@section('content')
<div class="container">
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <h4 class="mb-0" id="enq-name">{{ $enquiry->name }}</h4>
                                <span class="badge {{ $enquiry->priority == 'Urgent' ? 'bg-danger' : ($enquiry->priority == 'High' ? 'bg-warning text-dark' : 'bg-info text-dark') }}">
                                    {{ $enquiry->priority }} Priority
                                </span>
                            </div>
                            <p class="text-muted mb-0"><i class="bi bi-geo-alt me-1"></i> {{ $enquiry->location ?? 'No location' }}</p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('enquiries.edit', $enquiry->id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil me-1"></i> Edit
                            </a>
                            <a href="{{ route('enquiries.index') }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Back
                            </a>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <h6 class="text-uppercase text-muted small fw-bold mb-3">Client Contact</h6>
                            <div class="mb-2">
                                <strong>Mobile:</strong> <span class="ms-1">{{ $enquiry->mobile ?? '-' }}</span>
                            </div>
                            <div class="mb-2">
                                <strong>Email:</strong> <span class="ms-1">{{ $enquiry->email ?? '-' }}</span>
                            </div>
                            <div class="mb-2">
                                <strong>GSTIN:</strong> <span class="ms-1">{{ $enquiry->gstin ?? '-' }}</span>
                            </div>
                            <div class="mb-2">
                                <strong>Address:</strong>
                                <div class="mt-1 text-muted small">{{ $enquiry->address ?? '-' }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-uppercase text-muted small fw-bold mb-3">Service Details</h6>
                            <div class="mb-2">
                                <strong>Category:</strong> <span class="ms-1">{{ optional($enquiry->service)->name ?? '-' }}</span>
                            </div>
                            <div class="mb-2">
                                <strong>Item/Work:</strong> <span class="ms-1">{{ optional($enquiry->serviceItem)->name ?? '-' }}</span>
                            </div>
                            <div class="mb-2">
                                <strong>Type:</strong> <span class="ms-1">{{ optional($enquiry->enquiryType)->name ?? '-' }}</span>
                            </div>
                            <div class="mb-2">
                                <strong>Status:</strong> 
                                <span class="badge bg-secondary ms-1">{{ $enquiry->status }}</span>
                            </div>
                            @if($enquiry->fb_timeline)
                            <div class="mb-2">
                                <strong>Timeline:</strong> <span class="ms-1 text-primary fw-bold">{{ $enquiry->fb_timeline }}</span>
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-uppercase text-muted small fw-bold mb-2">Requirements Description</h6>
                        <div class="p-3 bg-light rounded small">{{ $enquiry->description ?? 'No description provided.' }}</div>
                    </div>
                </div>
            </div>

            <!-- Timeline & Activity -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <ul class="nav nav-tabs card-header-tabs" id="enquiryTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="timeline-tab" data-bs-toggle="tab" data-bs-target="#timeline" type="button" role="tab">Timeline & Activity</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="comments-tab" data-bs-toggle="tab" data-bs-target="#comments" type="button" role="tab">Comments ({{ $enquiry->comments->count() }})</button>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-4">
                    <div class="tab-content" id="enquiryTabContent">
                        <!-- Timeline Tab -->
                        <div class="tab-pane fade show active" id="timeline" role="tabpanel">
                            <div class="timeline-container">
                                @forelse($activities as $activity)
                                    <div class="d-flex gap-3 mb-4">
                                        <div class="flex-shrink-0">
                                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                <i class="bi {{ $activity->description == 'created' ? 'bi-plus-circle' : 'bi-pencil-square' }}"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 border-bottom pb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="fw-bold small">{{ $activity->causer->name ?? 'System' }}</span>
                                                <span class="text-muted extra-small">{{ $activity->created_at->format('M j, Y h:i A') }}</span>
                                            </div>
                                            <div class="small text-dark">
                                                {{ ucfirst($activity->description) }} the enquiry
                                                @if($activity->description == 'updated' && !empty($activity->changes['attributes']))
                                                    <ul class="mt-2 mb-0 extra-small text-muted">
                                                        @foreach($activity->changes['attributes'] as $key => $value)
                                                            @if($key != 'updated_at' && $key != 'created_at')
                                                                <li>
                                                                    @php
                                                                        $formatVal = function($k, $v) {
                                                                            if ($v === null || $v === '') return 'empty';
                                                                            if (is_string($v) && (str_ends_with($k, '_at') || str_ends_with($k, 'date') || preg_match('/^\d{4}-\d{2}-\d{2}T/', $v))) {
                                                                                try {
                                                                                    return \Carbon\Carbon::parse($v)->format('M j, Y h:i A');
                                                                                } catch (\Exception $e) {}
                                                                            }
                                                                            return is_array($v) ? json_encode($v) : $v;
                                                                        };
                                                                        $oldVal = $formatVal($key, $activity->changes['old'][$key] ?? null);
                                                                        $newVal = $formatVal($key, $value ?? null);
                                                                    @endphp
                                                                    <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> 
                                                                    @if($oldVal !== 'empty')
                                                                        <span class="text-decoration-line-through text-danger">{{ $oldVal }}</span> 
                                                                        <i class="bi bi-arrow-right mx-1"></i> 
                                                                    @endif
                                                                    <span class="{{ $newVal === 'empty' ? 'text-muted fst-italic' : 'text-success' }}">{{ $newVal === 'empty' ? 'Removed' : $newVal }}</span>
                                                                </li>
                                                            @endif
                                                        @endforeach
                                                    </ul>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-4 text-muted small">No activity recorded yet.</div>
                                @endforelse
                            </div>
                        </div>

                        <!-- Comments Tab -->
                        <div class="tab-pane fade" id="comments" role="tabpanel">
                            <div id="comments-list" class="mb-4">
                                @forelse($enquiry->comments->sortByDesc('created_at') as $c)
                                    <div class="card mb-3 border-0 bg-light shadow-none">
                                        <div class="card-body p-3">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="fw-bold small">{{ optional($c->user)->name ?? 'System' }}</span>
                                                <span class="text-muted extra-small">{{ $c->created_at->format('M j, Y h:i A') }}</span>
                                            </div>
                                            <div class="small text-dark">{{ $c->body }}</div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-4 text-muted small">No comments yet.</div>
                                @endforelse
                            </div>
                            <form id="comment-form">
                                <div class="mb-3">
                                    <textarea class="form-control custom-input" id="comment-body" name="body" rows="3" placeholder="Add a comment or internal note..." required></textarea>
                                </div>
                                <div class="text-end">
                                    <button class="btn btn-custom px-4" type="submit">Post Comment</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Assignment Card -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <h6 class="text-uppercase text-muted small fw-bold mb-3">Assignment & Source</h6>
                    <div class="mb-3">
                        <label class="small text-muted mb-1">Assigned To</label>
                        <div class="d-flex align-items-center gap-2">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                {{ strtoupper(substr($enquiry->assignedTo->name ?? 'U', 0, 1)) }}
                            </div>
                            <span class="fw-bold small">{{ $enquiry->assignedTo->name ?? 'Unassigned' }}</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="small text-muted mb-1">Lead Source</label>
                        <div class="small fw-bold"><i class="bi bi-box-arrow-in-right me-1"></i> {{ optional($enquiry->source)->name ?? 'Unknown' }}</div>
                    </div>
                    @if($enquiry->fb_created_at)
                    <div class="mb-3">
                        <label class="small text-muted mb-1">Lead Creation Date (FB)</label>
                        <div class="small fw-bold"><i class="bi bi-facebook me-1"></i> {{ $enquiry->fb_created_at->format('M j, Y H:i') }}</div>
                    </div>
                    @endif
                    <div>
                        <label class="small text-muted mb-1">Enquiry Date (System)</label>
                        <div class="small fw-bold"><i class="bi bi-calendar-check me-1"></i> {{ optional($enquiry->created_at)->format('M j, Y H:i') }}</div>
                    </div>
                </div>
            </div>

            <!-- Follow-up Card -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="text-uppercase text-muted small fw-bold mb-0">Next Follow-up</h6>
                        @if($enquiry->next_follow_up_at)
                            <span class="badge {{ $enquiry->next_follow_up_at->isPast() ? 'bg-danger' : 'bg-success' }} extra-small">
                                {{ $enquiry->next_follow_up_at->isPast() ? 'Overdue' : 'Scheduled' }}
                            </span>
                        @endif
                    </div>
                    <div class="bg-light p-3 rounded mb-3">
                        @if($enquiry->next_follow_up_at)
                            <div class="fw-bold small text-primary mb-1">{{ $enquiry->next_follow_up_at->format('M j, Y h:i A') }}</div>
                            <div class="text-muted extra-small">{{ $enquiry->reminder_notes ?? 'No reminder notes.' }}</div>
                        @else
                            <div class="text-muted small text-center py-2">Not scheduled</div>
                        @endif
                    </div>

                    <form id="followup-form">
                        <div class="mb-3">
                            <label class="form-label small">Reschedule Follow-up</label>
                            <input type="datetime-local" id="followup-datetime" name="scheduled_at" class="form-control custom-input form-control-sm" required />
                        </div>
                        <div class="mb-3">
                            <textarea id="followup-notes" name="notes" class="form-control custom-input form-control-sm" rows="2" placeholder="Notes for next follow-up"></textarea>
                        </div>
                        <button class="btn btn-custom btn-sm w-100" type="submit">Update Follow-up</button>
                    </form>
                </div>
            </div>

            <!-- Actions Card -->
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h6 class="text-uppercase text-muted small fw-bold mb-3">Quick Actions</h6>
                    <div class="d-grid gap-2">
                        <a href="{{ route('quotations.create', ['enquiry' => $enquiry->id]) }}" class="btn btn-success">
                            <i class="bi bi-file-earmark-plus me-1"></i> Create Quotation
                        </a>
                        <a href="{{ route('enquiries.edit', $enquiry->id) }}" class="btn btn-outline-primary">
                            <i class="bi bi-pencil me-1"></i> Edit Details
                        </a>
                        <button type="button" class="btn btn-outline-danger" onclick="Enquiry.deleteEnquiry({{ $enquiry->id }})">
                            <i class="bi bi-trash me-1"></i> Delete Enquiry
                        </button>
                    </div>
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
