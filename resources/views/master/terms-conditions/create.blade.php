@extends('layouts.backend')
@section('title','Create Terms & Conditions')
@section('content')
<div class="card form-card shadow-lg border-0">
    <div class="card-body p-5">
        <form id="terms-form" method="POST" action="{{ route('terms-conditions.store') }}">
            @csrf
            
            <div class="section-style mb-4">
                <div class="section-title">
                    <i class="bi bi-info-circle me-2"></i> Basic Information
                </div>
                <div class="row g-4 mt-2">
                    <div class="col-md-8">
                        <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control custom-input" id="title" name="title" placeholder="e.g., Standard Quotation Terms" required>
                        <div class="invalid-feedback" id="title-error"></div>
                    </div>
                    <div class="col-md-4">
                        <label for="applicable_for" class="form-label">Applicable For</label>
                        <select class="form-select custom-input" id="applicable_for" name="applicable_for">
                            <option value="quotation" selected>Quotation</option>
                        </select>
                        <div class="invalid-feedback" id="applicable_for-error"></div>
                    </div>
                </div>
            </div>

            <div class="section-style mb-4">
                <div class="section-title">
                    <i class="bi bi-file-richtext me-2"></i> Content
                </div>
                <div class="mt-4">
                    <textarea class="form-control" id="terms_content" name="content" rows="12" required></textarea>
                    <div class="form-text mt-2">
                        <i class="bi bi-lightbulb text-warning me-1"></i>
                        <strong>Tip:</strong> Use the editor toolbar to format your content with headings, lists, bold text, etc.
                    </div>
                    <div class="invalid-feedback" id="content-error"></div>
                </div>
            </div>

            <div class="section-style mb-4">
                <div class="section-title">
                    <i class="bi bi-toggle-on me-2"></i> Status
                </div>
                <div class="mt-4">
                    <div class="form-check form-switch custom-switch">
                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" checked>
                        <label class="form-check-label ms-2" for="is_active">
                            <span class="fw-bold">Active</span>
                            <small class="text-muted d-block">This template will be available for selection</small>
                        </label>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 pt-2">
                <a href="{{ route('terms-conditions.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                <button type="submit" class="btn btn-custom px-5 py-2" id="submitBtn">
                    <i class="bi bi-check-circle me-2"></i> Create Terms & Conditions
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('head')
<!-- Summernote Lite CSS -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<style>
    .note-editor.note-frame {
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        background: #fff;
    }
    .note-editor.note-frame.is-invalid {
        border-color: #dc3545;
    }
</style>
@endpush

@push('scripts')
<!-- Summernote Lite JS -->
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

<script>
$(document).ready(function(){
    console.log('Terms Create Page - Initializing Editor');
    
    function initEditor() {
        const $editor = $('#terms_content');
        
        // Check plugin existence
        if (typeof $.fn.summernote !== 'function') {
            console.warn('Summernote Lite plugin not found yet, retrying in 500ms...');
            setTimeout(initEditor, 500);
            return;
        }

        if ($editor.length === 0) {
            console.error('Editor textarea #terms_content not found in DOM');
            return;
        }

        console.log('Initializing Summernote Lite...');
        $editor.summernote({
            height: 300,
            placeholder: 'Enter your terms and conditions here...',
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'clear']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['height', ['height']],
                ['insert', ['link']],
                ['view', ['codeview', 'help']]
            ],
            styleTags: ['p', 'h4', 'h5', 'h6'],
            callbacks: {
                onChange: function(contents) {
                    $editor.removeClass('is-invalid');
                    $('#content-error').text('');
                    $('.note-editor').removeClass('is-invalid');
                }
            }
        });
    }

    initEditor();
    $('input, select').on('input change', function(){
        $(this).removeClass('is-invalid');
        $('#' + $(this).attr('id') + '-error').text('');
    });

    $('#terms-form').on('submit', function(e){
        e.preventDefault();
        
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        
        // Get content from Summernote
        const contentArea = $('#terms_content');
        const content = contentArea.summernote('code');
        
        if(!content || content.trim() === '' || content === '<p><br></p>'){
            contentArea.addClass('is-invalid');
            $('.note-editor').addClass('is-invalid');
            $('#content-error').text('The content field is required.');
            showAlert('Please enter terms and conditions content', 'error');
            return;
        }
        
        const fd = new FormData(this);
        // Update FormData with Summernote content
        fd.set('content', content);
        
        const submitBtn = $('#submitBtn');
        const originalText = submitBtn.html();
        
        // Disable button and show loading
        submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split me-2"></i>Creating...');
        
        fetch('{{ route("terms-conditions.store") }}', {
            method: 'POST',
            body: fd,
            headers: { 
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'), 
                'Accept':'application/json' 
            }
        })
        .then(r => { 
            if(r.status==422) return r.json().then(x => { throw {validation:x}; }); 
            return r.json(); 
        })
        .then(d => { 
            showAlert(d.message||'Terms & Conditions created successfully', 'success'); 
            setTimeout(() => window.location.href='{{ route("terms-conditions.index") }}', 900); 
        })
        .catch(err => {
            submitBtn.prop('disabled', false).html(originalText);
            
            if(err.validation && err.validation.errors){
                const errors = err.validation.errors;
                for(const k in errors){
                    $('#'+k+'-error').text(errors[k][0]);
                    $('#'+k).addClass('is-invalid');
                    if(k === 'content'){
                        $('.note-editor').addClass('is-invalid');
                    }
                }
                showAlert('Please fix the validation errors', 'error');
                
                // Scroll to first error
                const firstError = $('.is-invalid').first();
                if(firstError.length){
                    $('html, body').animate({
                        scrollTop: firstError.offset().top - 100
                    }, 500);
                }
            } else {
                showAlert('Error creating terms & conditions', 'error');
            }
        });
    });
});
</script>
@endpush
