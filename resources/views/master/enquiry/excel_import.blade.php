@extends('layouts.backend')

@section('title', 'Import Excel Leads')

@push('head')
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
.stepper { display: flex; align-items: center; justify-content: center; position: relative; }
.step { display: flex; flex-direction: column; align-items: center; z-index: 2; flex: 1; }
.step-icon { width: 44px; height: 44px; border-radius: 50%; background: #e9ecef; color: #6c757d; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.1rem; margin-bottom: 8px; transition: all 0.3s ease; }
.step-label { font-size: 13px; font-weight: 600; color: #6c757d; transition: all 0.3s ease; }
.step-line { flex: 1; height: 3px; background: #e9ecef; margin-bottom: 28px; transition: all 0.3s ease; }
.step.active .step-icon { background: #0d6efd; color: white; transform: scale(1.1); box-shadow: 0 4px 12px rgba(13, 110, 253, 0.35); }
.step.active .step-label { color: #0d6efd; font-weight: 700; }
.step.completed .step-icon { background: #198754; color: white; }
.step.completed + .step-line { background: #198754; }

.upload-area {
    cursor: pointer;
    transition: all 0.25s ease;
    border: 2px dashed #93c5fd !important;
    background-color: #f8fafc !important;
}
.upload-area:hover, .upload-area.dragover {
    background-color: #eff6ff !important;
    border-color: #2563eb !important;
    transform: translateY(-2px);
}

.mapping-card { transition: all 0.2s ease; border: 1px solid #e2e8f0; }
.mapping-card:hover { border-color: #cbd5e1; box-shadow: 0 4px 12px rgba(0,0,0,0.04); }

.sample-badge {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 4px 10px;
    font-size: 0.8rem;
    color: #334155;
    display: inline-block;
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.match-badge {
    font-size: 0.75rem;
    padding: 0.3em 0.7em;
    border-radius: 6px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
}
.match-badge-auto {
    background-color: #dcfce7;
    color: #15803d;
    border: 1px solid #86efac;
}
.match-badge-user {
    background-color: #dbeafe;
    color: #1d4ed8;
    border: 1px solid #93c5fd;
}
.match-badge-unmapped {
    background-color: #f1f5f9;
    color: #64748b;
    border: 1px solid #e2e8f0;
}
.select-matched {
    border-color: #22c55e !important;
    background-color: #f0fdf4 !important;
    color: #15803d !important;
    font-weight: 500;
}
.select-user-matched {
    border-color: #3b82f6 !important;
    background-color: #eff6ff !important;
    color: #1d4ed8 !important;
    font-weight: 500;
}

.x-small { font-size: 0.75rem; }
.tracking-wider { letter-spacing: 0.08em; }
</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <div class="row justify-content-center">
        <div class="col-xl-11">

            <!-- Breadcrumb / Back Button -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-1"><i class="bi bi-file-earmark-excel text-success me-2"></i> Import Excel & CSV Leads</h4>
                    <p class="text-muted small mb-0">Upload client spreadsheets, match columns dynamically, and import leads into the CRM</p>
                </div>
                <a href="{{ route('enquiries.index') }}" class="btn btn-outline-secondary px-4 rounded-pill">
                    <i class="bi bi-arrow-left me-1"></i> Back to Enquiries
                </a>
            </div>

            <!-- Download Template Banner Card -->
            <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: #f0fdf4; border: 1px solid #bbf7d0 !important; border-left: 5px solid #16a34a !important;">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-3 p-3 shadow-sm d-flex align-items-center justify-content-center flex-shrink-0" style="width: 52px; height: 52px; background-color: #16a34a; color: #ffffff;">
                                    <i class="bi bi-file-earmark-spreadsheet fs-3"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1" style="color: #15803d; font-size: 1.05rem;">Standard Sample Excel Format Available</h6>
                                    <p class="mb-0" style="color: #166534; font-size: 0.9rem; line-height: 1.45;">
                                        You can download our pre-formatted template with standard columns, <strong style="color: #14532d;">OR upload your own custom spreadsheet</strong> (like 2-column sheets or telecalling feedback sheets). Our smart mapper will auto-detect your columns!
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                            <a href="{{ route('enquiries.excel.sampleTemplate') }}" class="btn px-4 py-2 rounded-pill shadow-sm text-white" style="background-color: #16a34a; border-color: #16a34a; font-weight: 600;">
                                <i class="bi bi-download me-2"></i> Download Sample Template (.xlsx)
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Wizard Card -->
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-5">
                <div class="card-body p-4 p-md-5">

                    <!-- Progress Stepper -->
                    <div class="stepper mb-5">
                        <div class="step active" id="step-1-indicator">
                            <div class="step-icon">1</div>
                            <div class="step-label">Upload & Source</div>
                        </div>
                        <div class="step-line"></div>
                        <div class="step" id="step-2-indicator">
                            <div class="step-icon">2</div>
                            <div class="step-label">Check & Match Columns</div>
                        </div>
                        <div class="step-line"></div>
                        <div class="step" id="step-3-indicator">
                            <div class="step-icon">3</div>
                            <div class="step-label">Import Leads</div>
                        </div>
                    </div>

                    <!-- STEP 1: Upload & Source Configuration -->
                    <div id="step-1-content" class="step-content">
                        <div class="row justify-content-center">
                            <div class="col-lg-9">

                                <div class="row g-3 mb-4">
                                    <!-- Lead Source Selector -->
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small text-muted">Lead Source <span class="text-danger">*</span></label>
                                        <select class="form-select rounded-3 py-2 border-primary" id="source_id">
                                            <option value="">-- Select Source --</option>
                                            @foreach($sources as $s)
                                                <option value="{{ $s->id }}" {{ strtolower($s->name) == 'telecalling' || strtolower($s->name) == 'excel import' ? 'selected' : '' }}>
                                                    {{ $s->name }}
                                                </option>
                                            @endforeach
                                            <option value="__new__">+ Add New Source...</option>
                                        </select>
                                        <div class="form-text small">Select the lead acquisition channel for these leads.</div>

                                        <!-- New Source Input (Hidden by default) -->
                                        <div id="new_source_wrapper" class="mt-2 d-none">
                                            <input type="text" class="form-control rounded-3" id="new_source_name" placeholder="Enter new source name (e.g. Telecalling, Exhibition)">
                                        </div>
                                    </div>

                                    <!-- Default Assignee (Optional) -->
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small text-muted">Assign Leads To (Optional)</label>
                                        <select class="form-select rounded-3 py-2" id="default_assigned_to">
                                            <option value="">-- Leave Unassigned --</option>
                                            @foreach($users as $u)
                                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                                            @endforeach
                                        </select>
                                        <div class="form-text small">Optionally assign all imported leads to a team member.</div>
                                    </div>
                                </div>

                                <!-- Drag & Drop Zone -->
                                <div class="upload-area rounded-4 p-5 text-center border-2 border-dashed border-primary bg-light mb-4" id="drop-zone">
                                    <i class="bi bi-cloud-arrow-up display-2 text-primary mb-3"></i>
                                    <h4 class="fw-bold">Drag & Drop Spreadsheet File Here</h4>
                                    <p class="text-muted mb-3">Supports .xlsx, .xls, .csv, and .txt files</p>
                                    <input type="file" id="excel_file" class="d-none" accept=".csv,.txt,.xls,.xlsx">
                                    <button type="button" class="btn btn-primary px-5 py-2 rounded-pill shadow-sm" onclick="document.getElementById('excel_file').click()">
                                        <i class="bi bi-folder-fill me-2"></i> Browse Computer
                                    </button>
                                </div>

                                <div id="selected-file-info" class="d-none text-center p-3 rounded-3 mb-4" style="background-color: #eff6ff; border: 1px solid #bfdbfe;">
                                    <i class="bi bi-file-earmark-check fs-5 me-2" style="color: #2563eb;"></i>
                                    <span class="fw-bold" style="color: #1e40af;" id="filename-display"></span>
                                </div>

                                <div class="text-center">
                                    <button type="button" id="btn-next-1" class="btn btn-dark px-5 py-3 rounded-pill shadow d-none">
                                        Continue to Column Verification <i class="bi bi-arrow-right ms-2"></i>
                                    </button>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- STEP 2: Interactive Column Verification & Mapping -->
                    <div id="step-2-content" class="step-content d-none">
                        <!-- Auto-Match Status Banner -->
                        <div class="alert border-0 rounded-4 p-4 mb-4 shadow-sm" style="background-color: #eff6ff; border: 1px solid #bfdbfe !important;">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle p-2 d-flex align-items-center justify-content-center" style="background-color: #dbeafe; width: 48px; height: 48px;">
                                        <i class="bi bi-check2-circle fs-3" style="color: #1d4ed8;"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-1" style="color: #1e40af;" id="auto-match-title">Columns Analyzed</h5>
                                        <p class="mb-0 small" style="color: #1e3a8a;">
                                            We automatically pre-matched detected columns and displayed their live sample values. <strong>Review or adjust any column before importing.</strong>
                                        </p>
                                    </div>
                                </div>
                                <div class="d-flex gap-2" id="match-summary-badges">
                                    <span class="badge px-3 py-2 fs-6 rounded-pill text-white" style="background-color: #16a34a;" id="badge-matched-count">0 Auto-Matched</span>
                                    <span class="badge px-3 py-2 fs-6 rounded-pill text-dark" style="background-color: #e2e8f0;" id="badge-unmatched-count">0 Unmapped</span>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4">
                            <!-- Left Column: Primary Contact Fields -->
                            <div class="col-lg-6">
                                <!-- Required Fields Card -->
                                <div class="card mapping-card rounded-4 p-4 shadow-sm mb-4">
                                    <h6 class="fw-bold text-primary text-uppercase small tracking-wider mb-3">
                                        <i class="bi bi-exclamation-circle me-1"></i> Required Information
                                    </h6>
                                    
                                    <!-- Full Name (Required) -->
                                    <div class="mb-2">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="form-label fw-bold small text-muted mb-0">Full Name / Contact Person <span class="text-danger">*</span></label>
                                            <span class="match-badge-container" data-field="name"></span>
                                        </div>
                                        <select class="form-select mapping-select rounded-3" data-field="name"></select>
                                        <div class="sample-preview mt-1" data-field="name"></div>
                                    </div>
                                </div>

                                <!-- Contact Details Card -->
                                <div class="card mapping-card rounded-4 p-4 shadow-sm mb-4">
                                    <h6 class="fw-bold text-primary text-uppercase small tracking-wider mb-3">
                                        <i class="bi bi-telephone me-1"></i> Contact Information
                                    </h6>

                                    <!-- Mobile / Phone -->
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="form-label fw-bold small text-muted mb-0">Phone / Mobile Number (Used for Deduplication)</label>
                                            <span class="match-badge-container" data-field="mobile"></span>
                                        </div>
                                        <select class="form-select mapping-select rounded-3" data-field="mobile"></select>
                                        <div class="sample-preview mt-1" data-field="mobile"></div>
                                    </div>

                                    <!-- Email -->
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="form-label fw-bold small text-muted mb-0">Email Address</label>
                                            <span class="match-badge-container" data-field="email"></span>
                                        </div>
                                        <select class="form-select mapping-select rounded-3" data-field="email"></select>
                                        <div class="sample-preview mt-1" data-field="email"></div>
                                    </div>

                                    <!-- Location / City -->
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="form-label fw-bold small text-muted mb-0">Location / City</label>
                                            <span class="match-badge-container" data-field="location"></span>
                                        </div>
                                        <select class="form-select mapping-select rounded-3" data-field="location"></select>
                                        <div class="sample-preview mt-1" data-field="location"></div>
                                    </div>

                                    <!-- Full Address -->
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="form-label fw-bold small text-muted mb-0">Full Address</label>
                                            <span class="match-badge-container" data-field="address"></span>
                                        </div>
                                        <select class="form-select mapping-select rounded-3" data-field="address"></select>
                                        <div class="sample-preview mt-1" data-field="address"></div>
                                    </div>

                                    <!-- GSTIN -->
                                    <div class="mb-0">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="form-label fw-bold small text-muted mb-0">GSTIN</label>
                                            <span class="match-badge-container" data-field="gstin"></span>
                                        </div>
                                        <select class="form-select mapping-select rounded-3" data-field="gstin"></select>
                                        <div class="sample-preview mt-1" data-field="gstin"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column: Call Feedback, Appointment, Requirements -->
                            <div class="col-lg-6">
                                <!-- Call Status & Feedback Card (Highlights Screenshot 2 features) -->
                                <div class="card mapping-card rounded-4 p-4 shadow-sm mb-4 border-start border-4 border-info">
                                    <h6 class="fw-bold text-info text-uppercase small tracking-wider mb-3">
                                        <i class="bi bi-chat-left-dots me-1"></i> Call Response & Feedback
                                    </h6>

                                    <!-- Call Status / Response -->
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="form-label fw-bold small text-muted mb-0">Call Connect / Status (e.g. Connected, Not Responding)</label>
                                            <span class="match-badge-container" data-field="call_status"></span>
                                        </div>
                                        <select class="form-select mapping-select rounded-3" data-field="call_status"></select>
                                        <div class="sample-preview mt-1" data-field="call_status"></div>
                                    </div>

                                    <!-- Feedback / Remarks -->
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="form-label fw-bold small text-muted mb-0">Feedback / Remarks / Internal Note</label>
                                            <span class="match-badge-container" data-field="feedback"></span>
                                        </div>
                                        <select class="form-select mapping-select rounded-3" data-field="feedback"></select>
                                        <div class="sample-preview mt-1" data-field="feedback"></div>
                                        <div class="form-text text-muted x-small">Saved into lead description and created as initial timeline comment.</div>
                                    </div>

                                    <!-- Appointment Date -->
                                    <div class="mb-0">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="form-label fw-bold small text-muted mb-0">Appointment / Follow-up Date</label>
                                            <span class="match-badge-container" data-field="appointment"></span>
                                        </div>
                                        <select class="form-select mapping-select rounded-3" data-field="appointment"></select>
                                        <div class="sample-preview mt-1" data-field="appointment"></div>
                                        <div class="form-text text-muted x-small">Automatically schedules a follow-up reminder on the CRM calendar.</div>
                                    </div>
                                </div>

                                <!-- Requirements & Priority -->
                                <div class="card mapping-card rounded-4 p-4 shadow-sm mb-4">
                                    <h6 class="fw-bold text-primary text-uppercase small tracking-wider mb-3">
                                        <i class="bi bi-briefcase me-1"></i> Requirements & Priority
                                    </h6>

                                    <!-- Service Requirement -->
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="form-label fw-bold small text-muted mb-0">Flooring Service / Work Requirement</label>
                                            <span class="match-badge-container" data-field="service_answer"></span>
                                        </div>
                                        <select class="form-select mapping-select rounded-3" data-field="service_answer"></select>
                                        <div class="sample-preview mt-1" data-field="service_answer"></div>
                                    </div>

                                    <!-- Priority -->
                                    <div class="mb-0">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="form-label fw-bold small text-muted mb-0">Priority / Timeline</label>
                                            <span class="match-badge-container" data-field="priority_answer"></span>
                                        </div>
                                        <select class="form-select mapping-select rounded-3" data-field="priority_answer"></select>
                                        <div class="sample-preview mt-1" data-field="priority_answer"></div>
                                    </div>
                                </div>

                                <!-- Live Raw Preview Panel -->
                                <div class="card bg-dark text-white rounded-4 p-4 shadow-sm overflow-hidden">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="fw-bold text-uppercase x-small tracking-wider text-white-50 mb-0">Raw File Preview (Top Rows)</h6>
                                        <span class="badge bg-secondary x-small">From Uploaded File</span>
                                    </div>
                                    <div class="table-responsive" style="max-height: 220px;">
                                        <table class="table table-dark table-sm x-small mb-0 opacity-75" id="preview-table"></table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-center gap-3 mt-5">
                            <button type="button" id="btn-back-2" class="btn btn-outline-secondary px-4 py-2 rounded-pill">
                                <i class="bi bi-arrow-left me-1"></i> Back to Upload
                            </button>
                            <button type="button" id="btn-import" class="btn btn-success px-5 py-3 rounded-pill shadow fw-bold">
                                <i class="bi bi-check2-all me-2"></i> Confirm & Start Importing Leads
                            </button>
                        </div>
                    </div>

                    <!-- STEP 3: Results Summary -->
                    <div id="step-3-content" class="step-content d-none">
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="bi bi-check-circle-fill display-1 text-success"></i>
                            </div>
                            <h2 class="fw-bold mb-2">Import Completed Successfully!</h2>
                            <p class="text-muted fs-5 mb-5" id="import-summary-text">Your spreadsheet leads have been processed into the CRM.</p>

                            <div class="row g-3 justify-content-center mb-5">
                                <div class="col-md-3">
                                    <div class="p-4 border rounded-4 bg-light shadow-sm">
                                        <div class="h2 fw-bold text-success mb-1" id="stat-imported">0</div>
                                        <div class="small text-muted text-uppercase fw-bold">New Leads Created</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-4 border rounded-4 bg-light shadow-sm">
                                        <div class="h2 fw-bold text-info mb-1" id="stat-updated">0</div>
                                        <div class="small text-muted text-uppercase fw-bold">Updated / Notes Appended</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-4 border rounded-4 bg-light shadow-sm">
                                        <div class="h2 fw-bold text-warning mb-1" id="stat-duplicates">0</div>
                                        <div class="small text-muted text-uppercase fw-bold">Duplicates Skipped</div>
                                    </div>
                                </div>
                            </div>

                            <a href="{{ route('enquiries.index') }}" class="btn btn-primary px-5 py-3 rounded-pill shadow me-2">
                                <i class="bi bi-list-task me-2"></i> View Leads in Enquiry Directory
                            </a>
                            <button type="button" class="btn btn-outline-secondary px-4 py-3 rounded-pill" onclick="location.reload()">
                                <i class="bi bi-arrow-repeat me-2"></i> Import Another File
                            </button>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
$(function() {
    let tempPath = '';
    let delimiter = '';
    let headers = [];
    let previewData = [];

    // Smart Keyword Detection Dictionary
    const fieldMapKeywords = {
        'name': ['name', 'full_name', 'full name', 'contact person name', 'contact person', 'customer name', 'client name', 'contact name', 'person name'],
        'mobile': ['phone', 'mobile', 'contact number', 'contact no', 'phone number', 'cell', 'mobile number', 'telephone', 'tel', 'phone no'],
        'email': ['email', 'e-mail', 'email address', 'mail'],
        'location': ['location', 'city', 'area', 'place', 'town'],
        'address': ['address', 'full address', 'street', 'site address'],
        'gstin': ['gst', 'gstin', 'gst no', 'gst number', 'tax id'],
        'call_status': ['call connect yes /no', 'call connect', 'call status', 'connect', 'status', 'call response', 'response', 'connected'],
        'feedback': ['feed back', 'feedback', 'remarks', 'remark', 'notes', 'note', 'comments', 'comment', 'discussion'],
        'appointment': ['feed back appointment', 'appointment', 'appointment date', 'follow up', 'next follow up', 'follow up date', 'scheduled date', 'call date'],
        'service_answer': ['flooring', 'flooring_type', 'service', 'service required', 'requirement', 'work requirement', 'product', 'category'],
        'priority_answer': ['priority', 'timeline', 'urgency', 'planning', 'when', 'start']
    };

    // Show/Hide New Source Input
    $('#source_id').on('change', function() {
        if ($(this).val() === '__new__') {
            $('#new_source_wrapper').removeClass('d-none');
            $('#new_source_name').focus();
        } else {
            $('#new_source_wrapper').addClass('d-none');
        }
    });

    // File Selection & Upload
    $('#excel_file').on('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;

        $('#filename-display').text(file.name);
        $('#selected-file-info').removeClass('d-none');

        const formData = new FormData();
        formData.append('file', file);
        formData.append('_token', '{{ csrf_token() }}');

        Swal.fire({
            title: 'Analyzing Spreadsheet...',
            text: 'Parsing headers and generating sample preview',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.ajax({
            url: "{{ route('enquiries.excel.preview') }}",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                Swal.close();
                tempPath = res.temp_path;
                delimiter = res.delimiter;
                headers = res.headers || [];
                previewData = res.preview || [];

                let matchedCount = 0;
                let totalFields = $('.mapping-select').length;

                // Populate mapping selects & execute smart matching
                $('.mapping-select').each(function() {
                    const sel = $(this);
                    const field = sel.data('field');
                    const badgeContainer = $(`.match-badge-container[data-field="${field}"]`);
                    const sampleContainer = $(`.sample-preview[data-field="${field}"]`);

                    sel.empty().append('<option value="">-- Skip this column --</option>');
                    headers.forEach(h => {
                        sel.append(`<option value="${h}">${h}</option>`);
                    });

                    // Match logic
                    const keywords = fieldMapKeywords[field] || [];
                    let matchedHeader = null;

                    // 1. Exact match (case-insensitive)
                    matchedHeader = headers.find(h => keywords.includes(h.trim().toLowerCase()));

                    // 2. Partial / word match
                    if (!matchedHeader) {
                        matchedHeader = headers.find(h => {
                            const hl = h.trim().toLowerCase();
                            return keywords.some(k => hl === k || hl.includes(k) || k.includes(hl));
                        });
                    }

                    if (matchedHeader) {
                        sel.val(matchedHeader);
                        matchedCount++;
                        badgeContainer.html('<span class="match-badge match-badge-auto"><i class="bi bi-check-circle-fill me-1"></i> Auto-Matched</span>');
                        sel.addClass('select-matched').removeClass('select-user-matched');
                    } else {
                        badgeContainer.html('<span class="match-badge match-badge-unmapped">Not Mapped</span>');
                        sel.removeClass('select-matched select-user-matched');
                    }

                    // Update live sample value under dropdown
                    updateSamplePreview(field, sel.val());
                });

                // Update summary badges
                $('#badge-matched-count').text(`${matchedCount} Auto-Matched`);
                $('#badge-unmatched-count').text(`${totalFields - matchedCount} Unmapped`);
                $('#auto-match-title').text(`${matchedCount} of ${totalFields} Fields Auto-Matched`);

                // Populate raw preview table
                let tableHtml = '<thead><tr>';
                headers.slice(0, 6).forEach(h => tableHtml += `<th>${h}</th>`);
                tableHtml += '</tr></thead><tbody>';
                previewData.slice(0, 3).forEach(row => {
                    tableHtml += '<tr>';
                    headers.slice(0, 6).forEach(h => tableHtml += `<td>${row[h] || '-'}</td>`);
                    tableHtml += '</tr>';
                });
                tableHtml += '</tbody>';
                $('#preview-table').html(tableHtml);

                $('#btn-next-1').removeClass('d-none');
            },
            error: function(xhr) {
                Swal.fire('File Analysis Failed', xhr.responseJSON?.message || 'Failed to read spreadsheet.', 'error');
            }
        });
    });

    // Helper: update live sample preview underneath a dropdown
    function updateSamplePreview(field, selectedHeader) {
        const container = $(`.sample-preview[data-field="${field}"]`);
        if (!selectedHeader || !previewData.length) {
            container.empty();
            return;
        }

        const sampleValues = [];
        for (let i = 0; i < Math.min(3, previewData.length); i++) {
            const val = previewData[i][selectedHeader];
            if (val !== undefined && val !== null && String(val).trim() !== '') {
                sampleValues.push(String(val).trim());
            }
        }

        if (sampleValues.length) {
            const displayStr = sampleValues.join(', ');
            container.html(`<div class="sample-badge"><i class="bi bi-eye text-primary me-1"></i> Sample: <strong>"${displayStr}"</strong></div>`);
        } else {
            container.html('<div class="sample-badge text-muted"><em>(Empty in preview rows)</em></div>');
        }
    }

    // Dynamic update when user changes dropdown manually
    $(document).on('change', '.mapping-select', function() {
        const sel = $(this);
        const field = sel.data('field');
        const val = sel.val();
        const badgeContainer = $(`.match-badge-container[data-field="${field}"]`);

        if (val) {
            badgeContainer.html('<span class="match-badge match-badge-user"><i class="bi bi-check2 me-1"></i> User Matched</span>');
            sel.addClass('select-user-matched').removeClass('select-matched');
        } else {
            badgeContainer.html('<span class="match-badge match-badge-unmapped">Skipped</span>');
            sel.removeClass('select-matched select-user-matched');
        }

        updateSamplePreview(field, val);

        // Recalculate summary badges
        let count = 0;
        $('.mapping-select').each(function() { if ($(this).val()) count++; });
        let total = $('.mapping-select').length;
        $('#badge-matched-count').text(`${count} Matched`);
        $('#badge-unmatched-count').text(`${total - count} Unmapped`);
    });

    // Step Navigation
    $('#btn-next-1').on('click', function() {
        const sourceVal = $('#source_id').val();
        if (!sourceVal) {
            Swal.fire('Select Source', 'Please select a Lead Source before proceeding.', 'warning');
            $('#source_id').focus();
            return;
        }
        if (sourceVal === '__new__' && !$('#new_source_name').val().trim()) {
            Swal.fire('Source Name Required', 'Please enter a name for the new lead source.', 'warning');
            $('#new_source_name').focus();
            return;
        }

        $('#step-1-content').addClass('d-none');
        $('#step-2-content').removeClass('d-none');
        $('#step-1-indicator').addClass('completed');
        $('#step-2-indicator').addClass('active');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    $('#btn-back-2').on('click', function() {
        $('#step-2-content').addClass('d-none');
        $('#step-1-content').removeClass('d-none');
        $('#step-1-indicator').removeClass('completed');
        $('#step-2-indicator').removeClass('active');
    });

    // Import Submission with Verification Confirmation
    $('#btn-import').on('click', function() {
        const mapping = {};
        let nameMapped = false;
        let mappingSummaryHtml = '<ul class="text-start small mb-0 list-group list-group-flush">';

        $('.mapping-select').each(function() {
            const field = $(this).data('field');
            const val = $(this).val();
            mapping[field] = val;

            if (field === 'name' && val) nameMapped = true;
            if (val) {
                const label = $(this).closest('.card').find(`label[for="${field}"], label`).first().text().replace('*', '').trim();
                mappingSummaryHtml += `<li class="list-group-item px-0 py-1 d-flex justify-content-between"><span><strong>${field.toUpperCase()}:</strong></span> <span class="text-primary font-monospace">${val}</span></li>`;
            }
        });
        mappingSummaryHtml += '</ul>';

        if (!nameMapped) {
            Swal.fire('Required Field Missing', 'Please map the <strong>Full Name</strong> column. This is required for creating leads.', 'warning');
            return;
        }

        // Show confirmation popup before executing import
        Swal.fire({
            title: 'Verify & Confirm Import',
            html: `<div class="mb-3 text-muted small">Please verify the confirmed column mappings below:</div>${mappingSummaryHtml}`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-cloud-upload-fill me-1"></i> Confirm & Import Leads',
            cancelButtonText: 'Review Mapping'
        }).then((result) => {
            if (result.isConfirmed) {
                executeImport(mapping);
            }
        });
    });

    function executeImport(mapping) {
        Swal.fire({
            title: 'Importing Leads...',
            text: 'Processing spreadsheet rows, checking duplicates, and saving data',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.ajax({
            url: "{{ route('enquiries.excel.process') }}",
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                temp_path: tempPath,
                delimiter: delimiter,
                source_id: $('#source_id').val() === '__new__' ? null : $('#source_id').val(),
                new_source_name: $('#source_id').val() === '__new__' ? $('#new_source_name').val().trim() : null,
                default_assigned_to: $('#default_assigned_to').val() || null,
                mapping: mapping
            },
            success: function(res) {
                Swal.close();
                $('#stat-imported').text(res.data.imported || 0);
                $('#stat-updated').text(res.data.updated || 0);
                $('#stat-duplicates').text(res.data.duplicates || 0);

                $('#step-2-content').addClass('d-none');
                $('#step-3-content').removeClass('d-none');
                $('#step-2-indicator').addClass('completed');
                $('#step-3-indicator').addClass('active');

                let parts = [];
                if (res.data.imported > 0) parts.push(`${res.data.imported} new leads created`);
                if (res.data.updated > 0) parts.push(`${res.data.updated} existing records updated with new feedback`);
                if (res.data.duplicates > 0) parts.push(`${res.data.duplicates} duplicates handled`);
                $('#import-summary-text').text(`Successfully finished: ${parts.join(', ')}.`);
            },
            error: function(xhr) {
                Swal.fire('Import Failed', xhr.responseJSON?.message || 'Server error occurred during import.', 'error');
            }
        });
    }

    // Drag & drop file upload event handling
    const dropZone = document.getElementById('drop-zone');
    if (dropZone) {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, function(e) { e.preventDefault(); e.stopPropagation(); }, false);
        });
        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => dropZone.classList.add('dragover'), false);
        });
        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => dropZone.classList.remove('dragover'), false);
        });
        dropZone.addEventListener('drop', function(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files && files.length) {
                document.getElementById('excel_file').files = files;
                $('#excel_file').trigger('change');
            }
        }, false);
    }
});
</script>
@endpush

@endsection
