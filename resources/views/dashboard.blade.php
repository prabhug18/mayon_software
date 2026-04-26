@extends('layouts.backend')
@section('title','Dashboard')
@section('content')
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card theme-card shadow-sm border-0 h-100">
            <div class="card-body p-4 text-center">
                <div class="mb-3">
                    <span class="p-3 rounded-circle bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-person-lines-fill h4 mb-0"></i>
                    </span>
                </div>
                <h3 class="fw-bold mb-1">{{ \App\Models\Enquiry::count() }}</h3>
                <p class="text-muted mb-0">Total Enquiries</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card theme-card shadow-sm border-0 h-100">
            <div class="card-body p-4 text-center">
                <div class="mb-3">
                    <span class="p-3 rounded-circle bg-success bg-opacity-10 text-success">
                        <i class="bi bi-file-earmark-text h4 mb-0"></i>
                    </span>
                </div>
                <h3 class="fw-bold mb-1">{{ \App\Models\Quotation::count() }}</h3>
                <p class="text-muted mb-0">Total Quotations</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card theme-card shadow-sm border-0 h-100">
            <div class="card-body p-4 text-center">
                <div class="mb-3">
                    <span class="p-3 rounded-circle bg-info bg-opacity-10 text-info">
                        <i class="bi bi-box-seam h4 mb-0"></i>
                    </span>
                </div>
                <h3 class="fw-bold mb-1">{{ \App\Models\Product::count() }}</h3>
                <p class="text-muted mb-0">Products</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card theme-card shadow-sm border-0 h-100">
            <div class="card-body p-4 text-center">
                <div class="mb-3">
                    <span class="p-3 rounded-circle bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-truck h4 mb-0"></i>
                    </span>
                </div>
                <h3 class="fw-bold mb-1">{{ \App\Models\Supplier::count() }}</h3>
                <p class="text-muted mb-0">Suppliers</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card theme-card shadow-sm border-0">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="section-title mb-0">
                        <i class="bi bi-clock-history me-2"></i> Recent Enquiries
                    </div>
                    <a href="{{ route('enquiries.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table custom-table table-hover">
                        <thead>
                            <tr>
                                <th>Client Name</th>
                                <th>Mobile</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(\App\Models\Enquiry::latest()->take(5)->get() as $enquiry)
                            <tr>
                                <td class="fw-bold">{{ $enquiry->name }}</td>
                                <td>{{ $enquiry->mobile }}</td>
                                <td>{{ $enquiry->created_at->format('d M Y') }}</td>
                                <td>
                                    <span class="badge bg-secondary">{{ $enquiry->status ?? 'NEW' }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card theme-card shadow-sm border-0 h-100">
            <div class="card-body p-4">
                <div class="section-title mb-4">
                    <i class="bi bi-lightning me-2"></i> Quick Actions
                </div>
                <div class="d-grid gap-3">
                    <a href="{{ route('enquiries.create') }}" class="btn btn-custom py-3 text-start px-4">
                        <i class="bi bi-person-plus me-3 h5 mb-0"></i> New Enquiry
                    </a>
                    <a href="{{ route('quotations.create') }}" class="btn btn-outline-primary py-3 text-start px-4 border-2">
                        <i class="bi bi-file-earmark-plus me-3 h5 mb-0"></i> Create Quotation
                    </a>
                    <a href="{{ route('products.create') }}" class="btn btn-outline-secondary py-3 text-start px-4 border-2">
                        <i class="bi bi-plus-circle me-3 h5 mb-0"></i> Add Product
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
