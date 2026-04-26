@extends('layouts.backend')

@section('title', 'Units')

@section('content')
<div class="main-content">
    <div class="row g-4">
        <div class="col-12">
            <div class="theme-card p-4">
                <div class="theme-card-header d-flex justify-content-between align-items-center mb-4">
                    <div class="d-flex align-items-center gap-2">
                        <div class="section-icon-box">
                            <i class="bi bi-rulers"></i>
                        </div>
                        <h4 class="mb-0 fw-bold">Units</h4>
                    </div>
                    <a href="{{ route('units.create') }}" class="btn btn-custom btn-sm px-3">
                        <i class="bi bi-plus-lg me-1"></i> Add Unit
                    </a>
                </div>
                
                <div class="table-responsive">
                    <table class="custom-table table table-hover" id="UnitTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Unit Name</th>
                                <th>Created At</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(function() {
    $('#UnitTable').DataTable({
        ajax: {
            url: "{{ route('units.index') }}",
            dataSrc: 'data'
        },
        columns: [
            { data: 'id' },
            { data: 'name', className: 'fw-bold' },
            { 
                data: 'created_at',
                render: function(data) {
                    return data ? new Date(data).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : '-';
                }
            },
            {
                data: null,
                className: 'text-end',
                render: function(data) {
                    return `
                        <div class="d-flex gap-1 justify-content-end">
                            <a href="/master/units/${data.id}/edit" class="btn btn-sm btn-custom" title="Edit"><i class="bi bi-pencil-square"></i></a>
                            <button onclick="deleteUnit(${data.id})" class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                        </div>
                    `;
                }
            }
        ],
        order: [[0, 'desc']]
    });
});

function deleteUnit(id) {
    if (confirm('Are you sure you want to delete this unit?')) {
        fetch(`/master/units/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(d => {
            showAlert(d.message || 'Unit deleted');
            $('#UnitTable').DataTable().ajax.reload();
        });
    }
}
</script>
@endpush
@endsection
