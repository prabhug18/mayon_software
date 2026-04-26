@extends('layouts.backend')
@section('title','Service Items')
@section('content')
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between mb-3">
            <h5>Service Items</h5>
            <a href="{{ route('service-items.create') }}" class="add-btn-custom">+ New Service Item</a>
        </div>
        <table id="ServiceItemTable" class="table table-bordered table-hover table-striped custom-table mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Service</th>
                    <th>Item Name</th>
                    <th>Unit</th>
                    <th>Default Rate</th>
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
    $('#ServiceItemTable').DataTable({
        ajax: { url: '/service-items', dataSrc: 'data' },
        columns: [
            { data: 'id' },
            { data: null, render: function(row){ return (row.service && row.service.name) || '-'; }},
            { data: 'item_name' },
            { data: null, render: function(row){ return (row.unit_master && row.unit_master.name) || row.unit || '-'; }},
            { data: 'default_rate', render: function(v){ return v ? '₹ ' + parseFloat(v).toFixed(2) : '-'; }},
            { data: 'is_active', render: function(v){ return v ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'; }},
            { data: null, orderable: false, render: function(data){
                return `<a href="/service-items/${data.id}/edit" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>
                <button class="btn btn-sm btn-outline-danger" onclick="deleteItem(${data.id})"><i class="bi bi-trash"></i></button>`;
            }}
        ]
    });
});

function deleteItem(id){
    if(!confirm('Delete this service item?')) return;
    fetch(`/service-items/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept':'application/json'
        }
    })
    .then(r => r.json())
    .then(d => {
        showAlert(d.message||'Deleted');
        $('#ServiceItemTable').DataTable().ajax.reload();
    })
    .catch(() => showAlert('Error deleting service item'));
}
</script>
@endpush

@endsection
