@extends('layouts.backend')
@section('title','Edit Service Item')
@section('content')
<div class="card">
    <div class="card-body">
        <h5 class="mb-3">Edit Service Item</h5>
        <form id="service-item-form" method="POST" action="{{ route('service-items.update', $serviceItem->id) }}">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="service_id" class="form-label">Service <span class="text-danger">*</span></label>
                <select class="form-select" id="service_id" name="service_id" required>
                    <option value="">Select Service</option>
                    @foreach($services as $service)
                        <option value="{{ $service->id }}" {{ $serviceItem->service_id == $service->id ? 'selected' : '' }}>{{ $service->name }}</option>
                    @endforeach
                </select>
                <div class="invalid-feedback" id="service_id-error"></div>
            </div>
            <div class="mb-3">
                <label for="item_name" class="form-label">Item Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="item_name" name="item_name" value="{{ $serviceItem->item_name }}" required>
                <div class="invalid-feedback" id="item_name-error"></div>
            </div>
            <div class="mb-3">
                <label for="unit" class="form-label">Unit <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="unit" name="unit" value="{{ $serviceItem->unit }}" required>
                <div class="invalid-feedback" id="unit-error"></div>
            </div>
            <div class="mb-3">
                <label for="default_rate" class="form-label">Default Rate</label>
                <input type="number" step="0.01" class="form-control" id="default_rate" name="default_rate" value="{{ $serviceItem->default_rate }}">
                <div class="invalid-feedback" id="default_rate-error"></div>
            </div>
            <div class="mb-3">
                <label for="default_gst_percentage" class="form-label">Default GST %</label>
                <input type="number" step="0.01" class="form-control" id="default_gst_percentage" name="default_gst_percentage" value="{{ $serviceItem->default_gst_percentage }}">
                <div class="invalid-feedback" id="default_gst_percentage-error"></div>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3">{{ $serviceItem->description }}</textarea>
                <div class="invalid-feedback" id="description-error"></div>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ $serviceItem->is_active ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">Active</label>
            </div>
            <button type="submit" class="btn btn-primary">Update Service Item</button>
            <a href="{{ route('service-items.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>

@push('scripts')
<script>
$(function(){
    $('#service-item-form').on('submit', function(e){
        e.preventDefault();
        const fd = new FormData(this);
        fetch('{{ route("service-items.update", $serviceItem->id) }}', {
            method: 'POST',
            body: fd,
            headers: { 'X-HTTP-Method-Override': 'PUT', 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'), 'Accept':'application/json' }
        })
        .then(r => { if(r.status==422) return r.json().then(x => { throw {validation:x}; }); return r.json(); })
        .then(d => { showAlert(d.message||'Updated'); setTimeout(() => window.location.href='{{ route("service-items.index") }}', 900); })
        .catch(err => {
            if(err.validation && err.validation.errors){
                for(const k in err.validation.errors){
                    $('#'+k+'-error').text(err.validation.errors[k][0]);
                    $('#'+k).addClass('is-invalid');
                }
            } else showAlert('Error updating service item');
        });
    });
});
</script>
@endpush

@endsection
