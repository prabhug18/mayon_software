@extends('layouts.backend')
@section('title','Services')
@section('content')
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between mb-3">
            <h5>Services</h5>
            <a href="{{ route('services.create') }}" class="add-btn-custom">+ New Service</a>
        </div>
        <table id="ServiceTable" class="table table-bordered table-hover table-striped custom-table mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Default GST %</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

@push('scripts')
<script>
$(function(){
    $('#ServiceTable').DataTable({
        ajax: { url: '/services', dataSrc: 'data' },
        columns: [
            { data: 'id' },
            { data: 'name' },
            { data: 'category', render: function(v){ return v || '-'; }},
            { data: 'default_gst_percentage', render: function(v){ return v ? v + '%' : '-'; }},
            { data: 'is_active', render: function(v){ return v ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'; }},
            { data: null, orderable: false, render: function(data){
                return `<a href="/services/${data.id}/edit" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>
                <button class="btn btn-sm btn-outline-danger" onclick="deleteItem(${data.id})"><i class="bi bi-trash"></i></button>`;
            }}
        ]
    });
});

function deleteItem(id){
    if(!confirm('Delete this service?')) return;
    fetch(`/services/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept':'application/json'
        }
    })
    .then(r => r.json())
    .then(d => {
        showAlert(d.message||'Deleted');
        $('#ServiceTable').DataTable().ajax.reload();
    })
    .catch(() => showAlert('Error deleting service'));
}
</script>
@endpush

@endsection
