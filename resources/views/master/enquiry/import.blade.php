@extends('layouts.backend')

@section('title', 'Import Facebook Leads')

@push('head')
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-xl-10">
            <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                <div class="card-header bg-primary bg-gradient p-4 text-white">
                    <div class="d-flex align-items-center">
                        <div class="icon-box bg-white bg-opacity-25 rounded-3 p-3 me-3">
                            <i class="bi bi-facebook fs-3"></i>
                        </div>
                        <div>
                            <h4 class="mb-0 fw-bold">Facebook Leads Integration</h4>
                            <p class="mb-0 opacity-75">Follow the 3-step process to import your ad campaign leads</p>
                        </div>
                    </div>
                </div>

                <div class="card-body p-5">
                    <!-- Progress Stepper -->
                    <div class="stepper mb-5">
                        <div class="step active" id="step-1-indicator">
                            <div class="step-icon">1</div>
                            <div class="step-label">Upload CSV</div>
                        </div>
                        <div class="step-line"></div>
                        <div class="step" id="step-2-indicator">
                            <div class="step-icon">2</div>
                            <div class="step-label">Map Columns</div>
                        </div>
                        <div class="step-line"></div>
                        <div class="step" id="step-3-indicator">
                            <div class="step-icon">3</div>
                            <div class="step-label">Importing</div>
                        </div>
                    </div>

                    <!-- Step 1: Upload -->
                    <div id="step-1-content" class="step-content">
                        <div class="text-center mb-4">
                            <h5 class="fw-bold">Step 1: Choose your Facebook leads export file</h5>
                            <p class="text-muted">Upload the .csv or .txt file exported from Facebook Ads Manager</p>
                        </div>
                        <div class="upload-area rounded-4 p-5 text-center border-2 border-dashed border-primary bg-light mb-4" id="drop-zone">
                            <i class="bi bi-cloud-arrow-up display-1 text-primary mb-3"></i>
                            <h4>Drag & Drop File Here</h4>
                            <p class="text-muted">or click to browse from your computer</p>
                            <input type="file" id="csv_file" class="d-none" accept=".csv,.txt">
                            <button class="btn btn-primary px-5 py-2 rounded-pill mt-3" onclick="document.getElementById('csv_file').click()">
                                <i class="bi bi-folder-fill me-2"></i> Browse File
                            </button>
                        </div>
                        <div id="selected-file-info" class="d-none text-center p-3 bg-primary bg-opacity-10 rounded-3 mb-4">
                            <span class="fw-bold text-primary" id="filename-display"></span>
                        </div>
                        <div class="text-center">
                            <button id="btn-next-1" class="btn btn-dark px-5 py-3 rounded-pill d-none">
                                Continue to Mapping <i class="bi bi-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Step 2: Mapping -->
                    <div id="step-2-content" class="step-content d-none">
                        <div class="alert alert-info border-0 rounded-4 p-4 mb-5 shadow-sm">
                            <div class="d-flex">
                                <i class="bi bi-info-circle-fill fs-3 me-3"></i>
                                <div>
                                    <h6 class="fw-bold mb-1">Configure your mapping</h6>
                                    <p class="mb-0 small">Select which column from your CSV matches each Enquiry field. We've tried to guess some for you!</p>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-lg-6">
                                <h6 class="fw-bold mb-4 text-primary text-uppercase small tracking-wider">Required Fields</h6>
                                <div class="mapping-card bg-white border rounded-4 p-4 shadow-sm mb-4">
                                    <div class="mb-4">
                                        <label class="form-label fw-bold small text-muted mb-1">Full Name</label>
                                        <select class="form-select mapping-select rounded-3" data-field="name"></select>
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label fw-bold small text-muted mb-1">Phone Number</label>
                                        <select class="form-select mapping-select rounded-3" data-field="mobile"></select>
                                    </div>
                                    <div class="mb-0">
                                        <label class="form-label fw-bold small text-muted mb-1">Facebook Lead ID (Unique)</label>
                                        <select class="form-select mapping-select rounded-3" data-field="fb_lead_id"></select>
                                    </div>
                                </div>

                                <h6 class="fw-bold mb-4 text-primary text-uppercase small tracking-wider">Campaign Details</h6>
                                <div class="mapping-card bg-white border rounded-4 p-4 shadow-sm">
                                    <div class="mb-4">
                                        <label class="form-label fw-bold small text-muted mb-1">Campaign Name</label>
                                        <select class="form-select mapping-select rounded-3" data-field="fb_campaign_name"></select>
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label fw-bold small text-muted mb-1">Form Name</label>
                                        <select class="form-select mapping-select rounded-3" data-field="fb_form_name"></select>
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label fw-bold small text-muted mb-1">Platform (FB/IG)</label>
                                        <select class="form-select mapping-select rounded-3" data-field="fb_platform"></select>
                                    </div>
                                    <div class="mb-0">
                                        <label class="form-label fw-bold small text-muted mb-1">Lead Created Time</label>
                                        <select class="form-select mapping-select rounded-3" data-field="fb_created_at"></select>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <h6 class="fw-bold mb-4 text-primary text-uppercase small tracking-wider">Requirement Analysis</h6>
                                <div class="mapping-card bg-white border rounded-4 p-4 shadow-sm mb-4">
                                    <div class="mb-4">
                                        <label class="form-label fw-bold small text-muted mb-1">Email Address</label>
                                        <select class="form-select mapping-select rounded-3" data-field="email"></select>
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label fw-bold small text-muted mb-1">Requirements (Services)</label>
                                        <select class="form-select mapping-select rounded-3" data-field="service_answer"></select>
                                        <div class="form-text mt-1 text-info small"><i class="bi bi-lightning-charge-fill me-1"></i> Auto-mapped to your existing Services list</div>
                                    </div>
                                    <div class="mb-0">
                                        <label class="form-label fw-bold small text-muted mb-1">Timeline (Priority)</label>
                                        <select class="form-select mapping-select rounded-3" data-field="priority_answer"></select>
                                        <div class="form-text mt-1 text-info small"><i class="bi bi-lightning-charge-fill me-1"></i> Auto-mapped to Priority levels (High/Medium/Low)</div>
                                    </div>
                                </div>

                                <div class="preview-panel bg-dark text-white rounded-4 p-4 shadow-sm overflow-hidden" style="min-height: 200px;">
                                    <h6 class="fw-bold mb-3 text-uppercase x-small tracking-wider opacity-50">Data Preview (Top 3 Rows)</h6>
                                    <div class="table-responsive">
                                        <table class="table table-dark table-sm x-small mb-0 opacity-75" id="preview-table"></table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mt-5">
                            <button id="btn-back-2" class="btn btn-outline-secondary px-4 py-2 rounded-pill me-2">Back</button>
                            <button id="btn-import" class="btn btn-primary px-5 py-3 rounded-pill shadow">
                                <i class="bi bi-cloud-upload-fill me-2"></i> Start Importing Leads
                            </button>
                        </div>
                    </div>

                    <!-- Step 3: Success -->
                    <div id="step-3-content" class="step-content d-none">
                        <div class="text-center py-5">
                            <div class="success-animation mb-4">
                                <i class="bi bi-check-circle-fill display-1 text-success"></i>
                            </div>
                            <h2 class="fw-bold mb-2">Import Successful!</h2>
                            <p class="text-muted fs-5 mb-5" id="import-summary-text"></p>
                            
                            <div class="row g-3 justify-content-center mb-5">
                                <div class="col-md-3">
                                    <div class="p-3 border rounded-4 bg-light">
                                        <div class="h3 fw-bold text-success mb-0" id="stat-imported">0</div>
                                        <div class="small text-muted text-uppercase fw-bold">Leads Imported</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-3 border rounded-4 bg-light">
                                        <div class="h3 fw-bold text-info mb-0" id="stat-updated">0</div>
                                        <div class="small text-muted text-uppercase fw-bold">Updated (Backfilled)</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-3 border rounded-4 bg-light">
                                        <div class="h3 fw-bold text-warning mb-0" id="stat-duplicates">0</div>
                                        <div class="small text-muted text-uppercase fw-bold">Skipped (No Changes)</div>
                                    </div>
                                </div>
                            </div>

                            <a href="{{ route('enquiries.index') }}" class="btn btn-dark px-5 py-3 rounded-pill">
                                <i class="bi bi-list-task me-2"></i> Go to Enquiry List
                            </a>
                            <button class="btn btn-outline-primary px-4 py-3 rounded-pill ms-2" onclick="location.reload()">
                                <i class="bi bi-arrow-repeat me-2"></i> Import Another File
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.stepper { display: flex; align-items: center; justify-content: center; position: relative; }
.step { display: flex; flex-direction: column; align-items: center; z-index: 2; flex: 1; }
.step-icon { width: 40px; height: 40px; border-radius: 50%; background: #e9ecef; color: #6c757d; display: flex; align-items: center; justify-content: center; font-weight: bold; margin-bottom: 8px; transition: all 0.3s ease; }
.step-label { font-size: 13px; font-weight: bold; color: #6c757d; transition: all 0.3s ease; }
.step-line { flex: 1; height: 2px; background: #e9ecef; margin-bottom: 24px; transition: all 0.3s ease; }
.step.active .step-icon { background: #0d6efd; color: white; transform: scale(1.1); box-shadow: 0 4px 10px rgba(13, 110, 253, 0.3); }
.step.active .step-label { color: #0d6efd; }
.step.completed .step-icon { background: #198754; color: white; }
.step.completed + .step-line { background: #198754; }

.upload-area { cursor: pointer; transition: all 0.3s ease; }
.upload-area:hover { background: #f0f7ff !important; border-color: #0d6efd !important; transform: translateY(-2px); }
.border-dashed { border-style: dashed !important; }

.mapping-select { border-color: #dee2e6; transition: all 0.3s ease; }
.mapping-select:focus { box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1); border-color: #0d6efd; }

.x-small { font-size: 0.75rem; }
.tracking-wider { letter-spacing: 0.1em; }
</style>

@push('scripts')
<script>
$(function() {
    let tempPath = '';
    let delimiter = '';
    let headers = [];

    const fieldMapKeywords = {
        'fb_lead_id': ['id', 'lead_id', 'lead id'],
        'name': ['full_name', 'full name', 'name', 'customer name'],
        'mobile': ['phone_number', 'phone number', 'phone', 'mobile'],
        'email': ['email', 'email address'],
        'fb_campaign_name': ['campaign_name', 'campaign name', 'campaign'],
        'fb_form_name': ['form_name', 'form name', 'form'],
        'fb_platform': ['platform'],
        'fb_created_at': ['created_time', 'created time', 'date'],
        'service_answer': ['flooring_type', 'required', 'requirement', 'service'],
        'priority_answer': ['when', 'planning', 'start', 'timeline']
    };

    // Step 1 File Upload
    $('#csv_file').on('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;

        $('#filename-display').text(file.name);
        $('#selected-file-info').removeClass('d-none');
        
        const formData = new FormData();
        formData.append('file', file);
        formData.append('_token', '{{ csrf_token() }}');

        Swal.fire({
            title: 'Analyzing file...',
            text: 'Parsing CSV headers and preview',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.ajax({
            url: "{{ route('enquiries.import.preview') }}",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                Swal.close();
                tempPath = res.temp_path;
                delimiter = res.delimiter;
                headers = res.headers;
                
                // Populate selects
                $('.mapping-select').each(function() {
                    const sel = $(this);
                    const field = sel.data('field');
                    sel.empty().append('<option value="">-- Skip this field --</option>');
                    headers.forEach(h => {
                        sel.append(`<option value="${h}">${h}</option>`);
                    });

                    // Try to auto-guess
                    const keywords = fieldMapKeywords[field];
                    if (keywords) {
                        // First try exact match
                        let matched = headers.find(h => keywords.includes(h.toLowerCase()));
                        
                        // If no exact match, try a stricter includes (word boundaries)
                        if (!matched) {
                            matched = headers.find(h => {
                                const hl = h.toLowerCase();
                                return keywords.some(k => hl === k || hl.startsWith(k + '_') || hl.endsWith('_' + k) || hl.includes(' ' + k + ' '));
                            });
                        }
                        
                        // Fallback to basic includes if still nothing, but be careful with 'id'
                        if (!matched && field !== 'fb_lead_id') {
                             matched = headers.find(h => {
                                const hl = h.toLowerCase();
                                return keywords.some(k => hl.includes(k));
                            });
                        }

                        if (matched) {
                            sel.val(matched);
                        }
                    }
                });

                // Preview table
                let tableHtml = '<thead><tr>';
                headers.slice(0, 5).forEach(h => tableHtml += `<th>${h}</th>`);
                tableHtml += '</tr></thead><tbody>';
                res.preview.slice(0, 3).forEach(row => {
                    tableHtml += '<tr>';
                    headers.slice(0, 5).forEach(h => tableHtml += `<td>${row[h]}</td>`);
                    tableHtml += '</tr>';
                });
                tableHtml += '</tbody>';
                $('#preview-table').html(tableHtml);

                $('#btn-next-1').removeClass('d-none');
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON.message || 'Failed to parse file', 'error');
            }
        });
    });

    $('#btn-next-1').on('click', function() {
        $('#step-1-content').addClass('d-none');
        $('#step-2-content').removeClass('d-none');
        $('#step-1-indicator').addClass('completed');
        $('#step-2-indicator').addClass('active');
    });

    $('#btn-back-2').on('click', function() {
        $('#step-2-content').addClass('d-none');
        $('#step-1-content').removeClass('d-none');
        $('#step-1-indicator').removeClass('completed');
        $('#step-2-indicator').removeClass('active');
    });

    // Step 2 Import Execution
    $('#btn-import').on('click', function() {
        const mapping = {};
        let missingRequired = false;

        $('.mapping-select').each(function() {
            const field = $(this).data('field');
            const val = $(this).val();
            if (['name', 'mobile', 'fb_lead_id'].includes(field) && !val) {
                missingRequired = true;
            }
            mapping[field] = val;
        });

        if (missingRequired) {
            Swal.fire('Incomplete Mapping', 'Please map Full Name, Phone Number, and Lead ID columns.', 'warning');
            return;
        }

        Swal.fire({
            title: 'Processing Import',
            text: 'This may take a moment...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.ajax({
            url: "{{ route('enquiries.import.process') }}",
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                temp_path: tempPath,
                delimiter: delimiter,
                mapping: mapping
            },
            success: function(res) {
                Swal.close();
                $('#stat-imported').text(res.data.imported);
                $('#stat-updated').text(res.data.updated || 0);
                $('#stat-duplicates').text(res.data.duplicates);
                
                $('#step-2-content').addClass('d-none');
                $('#step-3-content').removeClass('d-none');
                $('#step-2-indicator').addClass('completed');
                $('#step-3-indicator').addClass('active');
                
                let summaryParts = [];
                if (res.data.imported > 0) summaryParts.push(`${res.data.imported} new leads imported`);
                if (res.data.updated > 0) summaryParts.push(`${res.data.updated} existing leads updated with missing data`);
                if (res.data.duplicates > 0) summaryParts.push(`${res.data.duplicates} already up-to-date`);
                $('#import-summary-text').text(`Processed all rows. ${summaryParts.join(', ')}.`);
            },
            error: function(xhr) {
                Swal.fire('Import Failed', xhr.responseJSON.message || 'Server error', 'error');
            }
        });
    });

    // Drag and drop logic
    const dropZone = document.getElementById('drop-zone');
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => dropZone.classList.add('bg-primary', 'bg-opacity-10'), false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => dropZone.classList.remove('bg-primary', 'bg-opacity-10'), false);
    });

    dropZone.addEventListener('drop', handleDrop, false);

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        document.getElementById('csv_file').files = files;
        $('#csv_file').trigger('change');
    }
});
</script>
@endpush

@endsection
