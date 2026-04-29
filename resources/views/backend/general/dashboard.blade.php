@extends('layouts.backend')

@section('content')
    <div class="container-fluid my-4">
      
      <!-- TOP CARDS -->
      <div class="row g-4 mb-4">
        <!-- Enquiries Card -->
        <div class="col-xl-3 col-lg-6">
          <div id="enquiries-card" class="glass-card position-relative clickable" data-accent="blue" role="button" title="Click to view Enquiries">
            <div class="icon-wrapper bg-primary shadow-sm">
              <i class="bi bi-person-lines-fill"></i>
            </div>
            <h6 class="mt-4">Total Enquiries</h6>
            <p class="display-6 fw-bold mb-0">{{ $enquiryCount ?? 0 }}</p>
          </div>
        </div>
        
        <!-- Quotations Card -->
        <div class="col-xl-3 col-lg-6">
          <div id="quotations-card" class="glass-card position-relative clickable" data-accent="indigo" role="button" title="Click to view Quotations">
            <div class="icon-wrapper bg-indigo shadow-sm">
              <i class="bi bi-file-earmark-text"></i>
            </div>
            <h6 class="mt-4">Total Quotations</h6>
            <p class="display-6 fw-bold mb-0">{{ $quotationCount ?? 0 }}</p>
          </div>
        </div>

        <!-- Purchase Orders Card -->
        <div class="col-xl-3 col-lg-6">
          <div id="pos-card" class="glass-card position-relative clickable" data-accent="amber" role="button" title="Click to view Purchase Orders">
            <div class="icon-wrapper bg-amber shadow-sm">
              <i class="bi bi-cart-check"></i>
            </div>
            <h6 class="mt-4">Purchase Orders</h6>
            <p class="display-6 fw-bold mb-0">{{ $poCount ?? 0 }}</p>
          </div>
        </div>
        
        <!-- Today Follow-ups Card -->
        <div class="col-xl-3 col-lg-6">
          <div id="today-followups-card" class="glass-card position-relative clickable" data-accent="green" role="button" title="Click to view Follow-ups">
            <div class="icon-wrapper bg-success shadow-sm">
              <i class="bi bi-bell-fill"></i>
            </div>
            <h6 class="mt-4">Today Follow-ups</h6>
            <p class="display-6 fw-bold mb-0 text-success">{{ $todayFollowUpCount ?? 0 }}</p>
          </div>
        </div>
      </div>

      <!-- CHARTS SECTION -->
      <div class="row g-4 mb-4">
        <div class="col-xl-8">
            <div class="card shadow-sm border-0 h-100 rounded-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4"><i class="bi bi-graph-up-arrow text-primary me-2"></i> 30-Day Trend</h5>
                    <div style="height: 300px;">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card shadow-sm border-0 h-100 rounded-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4"><i class="bi bi-pie-chart text-info me-2"></i> Lead Sources</h5>
                    <div style="height: 300px;" class="d-flex justify-content-center">
                        <canvas id="sourceChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
      </div>

      <!-- RECENT ACTIVITY SECTION -->
      <div class="row g-4">
        <!-- Recent Enquiries -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0"><i class="bi bi-clock-history text-primary me-2"></i> Recent Enquiries</h5>
                    <a href="{{ route('enquiries.index') }}" class="btn btn-sm btn-outline-primary rounded-pill">View All</a>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentEnquiries as $enq)
                                <tr>
                                    <td>
                                        <a href="{{ route('enquiries.show', $enq->id) }}" class="fw-bold text-decoration-none">{{ $enq->name }}</a>
                                        <div class="small text-muted">{{ $enq->mobile }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $enq->status == 'Won' ? 'success' : ($enq->status == 'Lost' ? 'danger' : 'secondary') }}">{{ $enq->status ?? 'Open' }}</span>
                                    </td>
                                    <td class="text-muted small">{{ $enq->created_at->format('M d, Y') }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="text-center text-muted">No recent enquiries.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Quotations -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0"><i class="bi bi-receipt text-indigo me-2"></i> Recent Quotations</h5>
                    <a href="{{ route('quotations.index') }}" class="btn btn-sm btn-outline-indigo rounded-pill">View All</a>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Quote No</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentQuotationsList as $q)
                                <tr>
                                    <td>
                                        <a href="{{ route('quotations.show', $q->id) }}" class="fw-bold text-decoration-none">{{ $q->quotation_no }}</a>
                                    </td>
                                    <td>{{ $q->customer_name }}</td>
                                    <td class="fw-bold">₹{{ number_format($q->grand_total, 2) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="text-center text-muted">No recent quotations.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
      </div>

    </div>

    <!-- MODALS -->

    <!-- Today Follow-ups Modal -->
    <div class="modal fade" id="todayFollowupsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
            <div class="modal-header border-0 bg-light pb-2">
                <h5 class="modal-title fw-bold"><i class="bi bi-bell-fill text-success me-2"></i> Today's Follow-ups</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div id="today-followups-list" class="p-4">Loading…</div>
            </div>
            </div>
        </div>
    </div>

    <!-- Enquiries Modal -->
    <div class="modal fade" id="enquiriesModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
            <div class="modal-header border-0 bg-light pb-2">
                <h5 class="modal-title fw-bold"><i class="bi bi-person-lines-fill text-primary me-2"></i> Latest Enquiries</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div id="enquiries-list" class="p-4">Loading…</div>
            </div>
            <div class="modal-footer border-0 bg-light">
                <a href="{{ route('enquiries.index') }}" class="btn btn-primary">View Full Directory</a>
            </div>
            </div>
        </div>
    </div>

    <!-- Quotations Modal -->
    <div class="modal fade" id="quotationsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
            <div class="modal-header border-0 bg-light pb-2">
                <h5 class="modal-title fw-bold"><i class="bi bi-file-earmark-text text-indigo me-2"></i> Latest Quotations</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div id="quotations-list" class="p-4">Loading…</div>
            </div>
            <div class="modal-footer border-0 bg-light">
                <a href="{{ route('quotations.index') }}" class="btn btn-indigo">Manage All Quotations</a>
            </div>
            </div>
        </div>
    </div>

    <!-- Purchase Orders Modal -->
    <div class="modal fade" id="posModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
            <div class="modal-header border-0 bg-light pb-2">
                <h5 class="modal-title fw-bold"><i class="bi bi-cart-check text-warning me-2"></i> Latest Purchase Orders</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div id="pos-list" class="p-4">Loading…</div>
            </div>
            <div class="modal-footer border-0 bg-light">
                <a href="{{ route('purchaseOrders.index') }}" class="btn btn-warning">View All Purchase Orders</a>
            </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function(){
    
    // Setup generic modal fetcher
    function setupModalTrigger(cardId, modalId, listId, fetchUrl, tableBuilder) {
        var card = document.getElementById(cardId);
        if (!card) return;
        card.addEventListener('click', function(){
            var modalEl = document.getElementById(modalId);
            var listEl = document.getElementById(listId);
            listEl.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted">Loading data...</p></div>';
            
            var modal = new bootstrap.Modal(modalEl);
            modal.show();

            fetch(fetchUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(res => res.json())
                .then(json => {
                    var data = json.data || [];
                    if (!data.length) {
                        listEl.innerHTML = '<div class="alert alert-light text-center border">No records found.</div>';
                    } else {
                        listEl.innerHTML = tableBuilder(data);
                    }
                })
                .catch(err => {
                    listEl.innerHTML = '<div class="alert alert-danger">Failed to load data. Please try again.</div>';
                });
        });
    }

    // 1. Follow-ups Modal
    setupModalTrigger('today-followups-card', 'todayFollowupsModal', 'today-followups-list', '{{ route('dashboard.followups.today') }}', function(data) {
        var html = '<div class="table-responsive"><table class="table table-hover align-middle">';
        html += '<thead class="table-light"><tr><th>Name</th><th>Mobile</th><th>Status</th><th>Source</th><th>Time</th></tr></thead><tbody>';
        data.forEach(function(item){
            html += `<tr>
                <td><a href="/enquiries/${item.id}" class="fw-bold text-decoration-none">${item.name || '-'}</a></td>
                <td>${item.mobile || '-'}</td>
                <td><span class="badge bg-secondary">${item.status || '-'}</span></td>
                <td>${item.source || '-'}</td>
                <td class="text-success fw-bold">${item.next_follow_up_human || '-'}</td>
            </tr>`;
        });
        html += '</tbody></table></div>';
        return html;
    });

    // 2. Enquiries Modal
    setupModalTrigger('enquiries-card', 'enquiriesModal', 'enquiries-list', '{{ route('dashboard.enquiries.modal') }}', function(data) {
        var html = '<div class="table-responsive"><table class="table table-hover align-middle">';
        html += '<thead class="table-light"><tr><th>Name / Mobile</th><th>Type</th><th>Status</th><th>Source</th><th>Created On</th></tr></thead><tbody>';
        data.forEach(function(item){
            html += `<tr>
                <td><a href="/enquiries/${item.id}" class="fw-bold text-decoration-none">${item.name || '-'}</a><br><small class="text-muted">${item.mobile || '-'}</small></td>
                <td>${item.enquiry_type || '-'}</td>
                <td><span class="badge bg-secondary">${item.status || 'Open'}</span></td>
                <td>${item.source || '-'}</td>
                <td class="text-muted">${item.created_at || '-'}</td>
            </tr>`;
        });
        html += '</tbody></table></div>';
        return html;
    });

    // 3. Quotations Modal
    setupModalTrigger('quotations-card', 'quotationsModal', 'quotations-list', '{{ route('dashboard.quotations.modal') }}', function(data) {
        var html = '<div class="table-responsive"><table class="table table-hover align-middle">';
        html += '<thead class="table-light"><tr><th>Quote No</th><th>Customer</th><th>Status</th><th>Amount</th><th>Date</th></tr></thead><tbody>';
        data.forEach(function(item){
            html += `<tr>
                <td><a href="/quotations/${item.id}" class="fw-bold text-decoration-none">${item.quotation_no || '-'}</a></td>
                <td>${item.customer_name || '-'}</td>
                <td><span class="badge bg-secondary">${item.status || 'Draft'}</span></td>
                <td class="fw-bold">₹${parseFloat(item.grand_total || 0).toFixed(2)}</td>
                <td class="text-muted">${item.created_at || '-'}</td>
            </tr>`;
        });
        html += '</tbody></table></div>';
        return html;
    });

    // 4. Purchase Orders Modal
    setupModalTrigger('pos-card', 'posModal', 'pos-list', '{{ route('dashboard.purchaseOrders.modal') }}', function(data) {
        var html = '<div class="table-responsive"><table class="table table-hover align-middle">';
        html += '<thead class="table-light"><tr><th>PO Number</th><th>Supplier</th><th>Status</th><th>Amount</th><th>PO Date</th></tr></thead><tbody>';
        data.forEach(function(item){
            html += `<tr>
                <td><a href="/purchaseOrders/${item.id}" class="fw-bold text-decoration-none">${item.po_number || '-'}</a></td>
                <td>${item.supplier_name || '-'}</td>
                <td><span class="badge bg-secondary">${item.status || 'Pending'}</span></td>
                <td class="fw-bold">₹${parseFloat(item.amount || 0).toFixed(2)}</td>
                <td class="text-muted">${item.po_date || '-'}</td>
            </tr>`;
        });
        html += '</tbody></table></div>';
        return html;
    });

    // -- CHART RENDERING --
    const trendData = @json($trendData);
    const sourceData = @json($sourceData);

    // 1. Trend Chart (Line)
    if(document.getElementById('trendChart')) {
        const ctxTrend = document.getElementById('trendChart').getContext('2d');
        new Chart(ctxTrend, {
            type: 'line',
            data: {
                labels: trendData.labels,
                datasets: [
                    {
                        label: 'Enquiries',
                        data: trendData.enquiries,
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Quotations',
                        data: trendData.quotations,
                        borderColor: '#6610f2',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        borderDash: [5, 5],
                        fill: false,
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: { mode: 'index', intersect: false }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 } }
                }
            }
        });
    }

    // 2. Sources Chart (Doughnut)
    if(document.getElementById('sourceChart')) {
        const ctxSource = document.getElementById('sourceChart').getContext('2d');
        
        let sLabels = sourceData.map(s => s.label);
        let sData = sourceData.map(s => s.data);
        
        if (sData.length === 0) {
            sLabels = ['No Data'];
            sData = [1];
        }

        new Chart(ctxSource, {
            type: 'doughnut',
            data: {
                labels: sLabels,
                datasets: [{
                    data: sData,
                    backgroundColor: [
                        '#0d6efd', '#198754', '#ffc107', '#dc3545', '#0dcaf0', '#6610f2'
                    ],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12 } }
                }
            }
        });
    }

});
</script>
@endpush

@push('styles')
<style>
.clickable {
    cursor: pointer;
    transition: all 0.3s ease;
}
.clickable:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
}
.glass-card {
    padding: 1.5rem;
    border-radius: 1rem;
    background: #fff;
    border: 1px solid rgba(0,0,0,0.05);
}
.bg-indigo { background-color: #6610f2 !important; color: white; }
.text-indigo { color: #6610f2 !important; }
.btn-outline-indigo { color: #6610f2; border-color: #6610f2; }
.btn-outline-indigo:hover { background-color: #6610f2; color: white; }
.btn-indigo { background-color: #6610f2; color: white; }
.btn-indigo:hover { background-color: #520dc2; color: white; }
</style>
@endpush
    