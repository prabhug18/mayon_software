@extends('layouts.backend')
@section('title','Methodology')
@section('content')
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between mb-3">
            <h5>Methodology</h5>
            <a href="{{ route('methodologies.create') }}" class="add-btn-custom">+ New Methodology</a>
        </div>
        <table id="TermsTable" class="table table-bordered table-hover table-striped custom-table mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Applicable For</th>
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
    $('#TermsTable').DataTable({
        ajax: { url: '/methodologies', dataSrc: 'data' },
        columns: [
            { data: 'id' },
            { data: 'title' },
            { data: 'applicable_for', render: function(v){ return v ? v.charAt(0).toUpperCase() + v.slice(1) : '-'; }},
            { data: 'is_active', render: function(v){ return v ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'; }},
            { data: null, orderable: false, render: function(data){
                return `<a href="/methodologies/${data.id}/edit" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>
                <button class="btn btn-sm btn-outline-danger" onclick="deleteItem(${data.id})"><i class="bi bi-trash"></i></button>`;
            }}
        ]
    });
});

function deleteItem(id){
    if(!confirm('Delete this Methodology?')) return;
    fetch(`/methodologies/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept':'application/json'
        }
    })
    .then(r => r.json())
    .then(d => {
        showAlert(d.message||'Deleted');
        $('#TermsTable').DataTable().ajax.reload();
    })
    .catch(() => showAlert('Error deleting Methodology'));
}
</script>
@endpush

@endsection
