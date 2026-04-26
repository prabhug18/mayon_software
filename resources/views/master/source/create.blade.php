@extends('layouts.backend')

@section('title', 'Add Source')

@section('content')
<div class="card form-card shadow-lg border-0">
    <div class="card-body p-5">
        <form id="source-form">
            <div class="section-style mb-4">
                <div class="section-title">
                    <i class="bi bi-broadcast me-2"></i> Source Details
                </div>
                <div class="row mt-5">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Source Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control custom-input" id="name" name="name" placeholder="Enter Source name" />
                        <div class="invalid-feedback" id="name-error"></div>
                    </div>
                    <div class="col-md-12 mb-3 mt-4">
                        <hr class="text-muted opacity-25 mb-4">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-custom px-5" id="submitBtn">
                                <i class="bi bi-check2-circle me-1"></i> Create Source
                            </button>
                            <a href="{{ route('sources.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/js/source.js') }}"></script>
<script>
    $(function(){
        if (window.Source && typeof Source.initForm === 'function') {
            Source.initForm('#source-form');
        }
    });
</script>
@endpush

@endsection
