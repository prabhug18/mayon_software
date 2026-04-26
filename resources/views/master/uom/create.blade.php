@extends('layouts.backend')

@section('title', 'Add UOM')

@section('content')
<div class="card form-card shadow-lg border-0">
    <div class="card-body p-5">
        <form id="uom-form">
            <div class="section-style mb-4">
                <div class="section-title">
                    <i class="bi bi-rulers me-2"></i> UOM Details
                </div>
                <div class="row mt-5">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">UOM Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control custom-input" id="name" name="name" placeholder="Enter UOM name" />
                        <div class="invalid-feedback" id="name-error"></div>
                    </div>
                    <div class="col-md-12 mb-3 mt-4">
                        <hr class="text-muted opacity-25 mb-4">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-custom px-5" id="submitBtn">
                                <i class="bi bi-check2-circle me-1"></i> Create UOM
                            </button>
                            <a href="{{ route('uoms.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/js/uom.js') }}"></script>
<script>
    $(function(){
        if (window.Uom && typeof Uom.initForm === 'function') {
            Uom.initForm('#uom-form');
        }
    });
</script>
@endpush

@endsection
