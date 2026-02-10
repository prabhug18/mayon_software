@extends('layouts.backend')

@section('title', 'Edit Source')

@section('content')
<div class="card form-card shadow-lg border-0">
    <div class="card-body p-5">
        <form id="source-edit-form">
            <div class="section-style mb-4">
                <div class="section-title">Source Details</div>
                <div class="row mt-5">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Source Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control custom-input" id="name" name="name" value="{{ $source->name }}" />
                        <div class="invalid-feedback" id="name-error"></div>
                    </div>
                    <div class="col-md-6 mb-3 d-flex align-items-center gap-2 mt-3">
                        <a href="{{ route('sources.index') }}" class="btn btn-outline-secondary">Back</a>
                        <button type="submit" class="btn btn-custom px-5 py-2" id="submitBtn">Update</button>
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
        if (window.Source && typeof Source.initEditForm === 'function') {
            Source.initEditForm('#source-edit-form', {{ $source->id }});
        }
    });
</script>
@endpush

@endsection
