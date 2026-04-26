@extends('layouts.backend')

@section('title','Purchase Order Details')

@section('content')
<!-- top spacer to add clear space above the PO content -->

    <div class="card theme-card border-0 shadow-lg" style="margin: 40px auto; max-width: 1000px;">
        <div class="card-body p-0">
            <!-- Header Section -->
            <div class="p-5 border-bottom bg-light bg-opacity-50">
                <div class="d-flex justify-content-between align-items-start no-print mb-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="section-icon-box">
                            <i class="bi bi-cart-check"></i>
                        </div>
                        <div>
                            <h4 class="mb-1 fw-bold">Purchase Order</h4>
                            <div class="text-muted small">View and manage PO details</div>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('purchaseOrders.index') }}" class="btn btn-white shadow-sm border px-3">
                            <i class="bi bi-arrow-left me-1"></i> Back
                        </a>
                        <a href="{{ route('purchaseOrders.edit', $po->id) }}" class="btn btn-custom px-3">
                            <i class="bi bi-pencil-square me-1"></i> Edit
                        </a>
                        <a href="{{ route('purchaseOrders.print', $po->id) }}?autoprint=1" target="_blank" class="btn btn-dark px-3">
                            <i class="bi bi-printer me-1"></i> Print
                        </a>
                        <button id="send-invoice-btn" type="button" class="btn btn-success px-3">
                            <i class="bi bi-send me-1"></i> Send Invoice
                        </button>
                    </div>
                </div>

                <div class="row g-4 mt-2">
                    <div class="col-md-7">
                        <div class="d-flex align-items-center gap-3">
                            @if(optional($po->company)->logo)
                                <img src="{{ asset(optional($po->company)->logo) }}" alt="{{ $po->company->name }}" style="height:64px; width:auto; border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.08)">
                            @else
                                <div class="bg-primary bg-opacity-10 text-primary rounded-4 d-flex align-items-center justify-content-center fw-bold fs-4" style="height:64px;width:64px;">
                                    {{ strtoupper(substr(optional($po->company)->name ?? 'CO',0,2)) }}
                                </div>
                            @endif
                            <div>
                                <h5 class="fw-bold mb-1">{{ optional($po->company)->name }}</h5>
                                @php
                                    $company = $po->company ?? null;
                                    $addr = '';
                                    if ($company) {
                                        if (!empty($company->address_line1) || !empty($company->address_line2) || !empty($company->city) || !empty($company->pincode)) {
                                            $parts = array_filter([ $company->address_line1 ?? null, $company->address_line2 ?? null, ($company->city ?? null) ? ($company->city . ($company->pincode ? ' - '.$company->pincode : '')) : null ]);
                                            $addr = implode("\n", $parts);
                                        } else {
                                            $addr = $company->address ?? '';
                                        }
                                    }
                                @endphp
                                <div class="small text-muted mb-0">{!! nl2br(e($addr)) !!}</div>
                                <div class="small text-muted mt-1">
                                    <i class="bi bi-telephone-fill me-1"></i> {{ optional($po->company)->mobile ?? '-' }}
                                    @if(optional($po->company)->gst_no)
                                        <span class="mx-2 text-dark opacity-25">|</span>
                                        <i class="bi bi-receipt me-1"></i> GST: {{ optional($po->company)->gst_no }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5 text-md-end">
                        <div class="bg-white p-4 rounded-4 shadow-sm border border-light h-100">
                            <div class="text-uppercase tracking-wider small text-muted mb-1 fw-bold">PO Number</div>
                            <div class="h4 fw-bold text-primary mb-3">{{ $po->po_number }}</div>
                            <div class="d-flex justify-content-md-end gap-3 small">
                                <span class="text-muted"><i class="bi bi-calendar3 me-1"></i> Date:</span>
                                <span class="fw-bold">{{ optional($po->po_date)->format('d M Y') ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-5">
                <!-- Supplier & Deliver To Section -->
                <div class="row g-4 mb-5">
                    <div class="col-md-6 border-end pe-md-5">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <i class="bi bi-truck text-custom fs-5"></i>
                            <h6 class="fw-bold mb-0 text-uppercase tracking-wider">Bill To / Supplier</h6>
                        </div>
                        <h5 class="fw-bold mb-2">{{ optional($po->supplier)->name ?? '-' }}</h5>
                        <div class="text-muted mb-3">{!! nl2br(e(optional($po->supplier)->address ?? '')) !!}</div>
                        <div class="small">
                            <div class="mb-1"><span class="text-muted">Contact:</span> {{ optional($po->supplier)->contact_person ?? '-' }}</div>
                            <div class="mb-1"><span class="text-muted">Phone:</span> {{ optional($po->supplier)->mobile ?? '-' }}</div>
                            <div><span class="text-muted">Email:</span> {{ optional($po->supplier)->email ?? '-' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6 ps-md-5">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <i class="bi bi-geo-alt text-custom fs-5"></i>
                            <h6 class="fw-bold mb-0 text-uppercase tracking-wider">Deliver To / Project</h6>
                        </div>
                        <h5 class="fw-bold mb-2">{{ optional($po->project)->name ?? '-' }}</h5>
                        <div class="text-muted mb-3">{!! nl2br(e($projAddr)) !!}</div>
                        <div class="small">
                            <div class="mb-1"><span class="text-muted">Site Engineer:</span> {{ optional($po->siteEngineer)->name ?? '-' }}</div>
                            <div>{{ optional($po->siteEngineer)->mobile ? 'Phone: ' . optional($po->siteEngineer)->mobile : '' }}</div>
                        </div>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="table-responsive mb-4">
                    <table class="table custom-table align-middle">
                        <thead>
                            <tr class="text-uppercase xsmall tracking-wider">
                                <th style="width: 50px;">#</th>
                                <th>Item Description</th>
                                <th class="text-center" style="width: 100px;">UOM</th>
                                <th class="text-center" style="width: 100px;">Qty</th>
                                <th class="text-end" style="width: 150px;">Unit Price</th>
                                <th class="text-end" style="width: 150px;">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($po->items as $i => $item)
                            <tr>
                                <td class="text-muted">{{ $i + 1 }}</td>
                                <td>
                                    <div class="fw-semibold text-dark">{!! nl2br(e($item->description ?? '-')) !!}</div>
                                </td>
                                <td class="text-center"><span class="badge bg-light text-dark fw-normal rounded-pill px-3">{{ optional($item->uom)->name ?? '-' }}</span></td>
                                <td class="text-center fw-bold">{{ $fmt($item->quantity) }}</td>
                                <td class="text-end text-muted">{{ $fmt($item->unit_price) }}</td>
                                <td class="text-end fw-bold text-dark">{{ $fmt($item->total) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="p-5 text-center text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-3 opacity-25"></i>
                                    No items found for this PO.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" class="text-end border-0 pt-4">
                                    <h6 class="fw-bold mb-0">Grand Total</h6>
                                </td>
                                <td class="text-end border-0 pt-4">
                                    <h5 class="fw-bold text-primary mb-0">{{ config('app.currency_symbol', '₹') }} {{ $fmt($po->amount) }}</h5>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                @if($po->notes)
                <div class="mt-5 p-4 rounded-4 bg-light bg-opacity-50 border">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-journals text-muted"></i>
                        <h6 class="fw-bold mb-0">Notes / Terms & Conditions</h6>
                    </div>
                    <div class="text-muted small lh-base">{!! nl2br(e($po->notes)) !!}</div>
                </div>
                @endif

                <div class="row mt-5 pt-4">
                    <div class="col-md-6 border-end">
                        <div class="small text-muted text-uppercase tracking-wider mb-4 fw-bold">Prepared By</div>
                        <div class="fw-bold h5 text-dark mb-1">{{ optional($po->createdBy)->name ?? auth()->user()->name ?? '-' }}</div>
                        <div class="small text-muted">Mayon Flooring Pvt Ltd</div>
                    </div>
                    <div class="col-md-6 text-md-end ps-md-5">
                        <div class="small text-muted text-uppercase tracking-wider mb-4 fw-bold">Authorized Signature</div>
                        <div class="mt-2">
                            @if(optional($po->company)->authorized_image)
                                <img src="{{ asset(optional($po->company)->authorized_image) }}" alt="Authorized" style="max-height:80px; filter: grayscale(100%) brightness(0.9);" />
                            @else
                                <div class="border-bottom d-inline-block" style="width: 200px; height: 60px;"></div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<!-- Theme toggler removed -->
<script>
    (function(){
        const btn = document.getElementById('send-invoice-btn');
        if (!btn) return;
        btn.addEventListener('click', function(){
            if (!confirm('Send invoice to supplier by email?')) return;
            const orig = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = 'Sending...';

            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            fetch("{{ route('purchaseOrders.sendInvoice', $po->id) }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json'
                }
            }).then(r => r.json()).then(data => {
                if (data && data.status === 'success') {
                    // show a temporary alert
                    const a = document.createElement('div');
                    a.className = 'alert alert-success';
                    a.style.position = 'fixed'; a.style.right = '20px'; a.style.top = '20px'; a.style.zIndex = 2000;
                    a.textContent = data.message || 'Invoice sent successfully';
                    document.body.appendChild(a);
                    setTimeout(()=> { try{ a.remove(); } catch(e){} }, 4000);
                    btn.innerHTML = 'Sent';
                } else {
                    const msg = (data && data.message) ? data.message : (data && data.errors ? JSON.stringify(data.errors) : 'Failed to send invoice');
                    const a = document.createElement('div');
                    a.className = 'alert alert-danger';
                    a.style.position = 'fixed'; a.style.right = '20px'; a.style.top = '20px'; a.style.zIndex = 2000;
                    a.textContent = msg;
                    document.body.appendChild(a);
                    setTimeout(()=> { try{ a.remove(); } catch(e){} }, 6000);
                    btn.disabled = false;
                    btn.innerHTML = orig;
                }
            }).catch(err => {
                const a = document.createElement('div');
                a.className = 'alert alert-danger';
                a.style.position = 'fixed'; a.style.right = '20px'; a.style.top = '20px'; a.style.zIndex = 2000;
                a.textContent = 'Network or server error while sending invoice';
                document.body.appendChild(a);
                setTimeout(()=> { try{ a.remove(); } catch(e){} }, 6000);
                btn.disabled = false;
                btn.innerHTML = orig;
            });
        });
    })();
</script>
@endsection
