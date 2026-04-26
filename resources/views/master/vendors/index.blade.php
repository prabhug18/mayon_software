@extends('layouts.backend')
@section('title','Vendors')
@section('content')
<div class="card theme-card shadow-sm border-0">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="section-title mb-0">
                <i class="bi bi-people me-2"></i> Vendors
            </div>
            <a href="{{ route('vendors.create') }}" class="btn btn-custom px-4">
                <i class="bi bi-plus-lg me-1"></i> Add Vendor
            </a>
        </div>
        <div class="table-responsive">
            <table id="VendorTable" class="table custom-table table-hover w-100">
                <thead>
                    <tr>
                        <th width="50">ID</th>
                        <th>Name</th>
                        <th>GSTIN</th>
                        <th>Contact Person</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(function(){
    $('#VendorTable').DataTable({
        ajax: { url: '/vendors', dataSrc: 'data' },
        columns: [
            { data: 'id' },
            { data: 'name' },
            { data: 'gstin', render: function(v){ return v || '-'; }},
            { data: 'contact_person', render: function(v){ return v || '-'; }},
            { data: 'phone', render: function(v){ return v || '-'; }},
            { data: 'is_active', render: function(v){ return v ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'; }},
            { data: null, orderable: false, render: function(data){
                return `<a href="/vendors/${data.id}/edit" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>
                <button class="btn btn-sm btn-outline-danger" onclick="deleteItem(${data.id})"><i class="bi bi-trash"></i></button>`;
            }}
        ]
    });
});

function deleteItem(id){
    if(!confirm('Delete this vendor?')) return;
    fetch(`/vendors/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept':'application/json'
        }
    })
    .then(r => r.json())
    .then(d => {
        showAlert(d.message||'Deleted');
        $('#VendorTable').DataTable().ajax.reload();
    })
    .catch(() => showAlert('Error deleting vendor'));
}
</script>
@endpush

@endsection
