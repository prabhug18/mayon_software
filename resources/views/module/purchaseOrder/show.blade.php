@extends('layouts.backend')

@section('title','Purchase Order Details')

@section('content')
<!-- top spacer to add clear space above the PO content -->

<div class="card shadow-lg border-0" style="max-width:1000px;margin:80px 100px 120px 100px;padding-left:40px;padding-right:40px;padding-bottom:40px;">
    <div class="card-body p-4">

    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
            <div>
                <h4 class="mb-0">Purchase Order</h4>
                <div class="text-muted small">{{ $po->po_number }}</div>
            </div>
            <div>
                <a href="{{ route('purchaseOrders.index') }}" class="btn btn-outline-secondary">Back</a>
                <a href="{{ route('purchaseOrders.edit', $po->id) }}" class="btn btn-primary">Edit</a>
                <!-- <a href="{{ route('purchaseOrders.pdf', $po->id) }}" class="btn btn-dark">Download PDF</a> -->
                <a href="{{ route('purchaseOrders.print', $po->id) }}?autoprint=1" target="_blank" class="btn btn-light">Print</a>
                <button id="send-invoice-btn" type="button" class="btn btn-success">Send Invoice</button>
            </div>
        </div>

        <!-- Theme preview controls removed -->

        @php
            $fmt = fn($v) => number_format((float)$v, 2);
            // prepare project address for consistent rendering (avoid inline raw-block issues)
            $proj = $po->project ?? null;
            $projAddr = $proj ? ($proj->address ?? $proj->location ?? '-') : '-';
        @endphp

        <style>
            /* Use explicit colors to ensure print/PDF fidelity */
            :root{
                --po-accent: #D6336C; /* rose pink */
                --po-accent-2: #FFE8F0; /* very light pink */
                --po-soft: #FFF8FB; /* soft pink-tinted white */
                --po-text: #2B1F2E; /* dark muted plum for text */
            }

            .po-top { background: #FFF9FB; padding:12px; border-radius:8px; }
            .po-badge { background: #D6336C; color: #fff; padding:6px 10px; border-radius:8px; font-weight:700; font-size:0.95rem }
            .supplier-card { background: #ffffff; border-radius:8px; padding:10px; border:1px solid rgba(17,24,39,0.06); }
            /* Table header: clear monochrome contrast */
            .table-items thead th { background: var(--po-accent-2); color: var(--po-accent); font-weight:700; border-bottom: 1px solid rgba(17,24,39,0.06); }
            .table-items thead th:first-child { border-top-left-radius:6px; }
            .table-items thead th:last-child { border-top-right-radius:6px; }
            .table-items tbody tr:nth-child(odd){ background: #fff; }
            .table-items tbody tr:nth-child(even){ background: #FAFAFA; }
            .amount-cell{ color: var(--po-accent); font-weight:700; }
            .totals-panel{ background: linear-gradient(180deg,var(--po-accent), #0B1220); color:white; padding:12px; border-radius:8px; }

                /* Alternate palettes removed; default palette applied */
            @page { size: A4 portrait; margin: 18mm; }
            @media print{
                /* Ensure colors are preserved and adjusted for print/PDF */
                html, body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                /* Force solid backgrounds for critical elements */
                .po-top { background: #FFF9FB !important; }
                .po-badge { background: #D6336C !important; color:#fff !important }
                .supplier-card { background: #FFFFFF !important }
                .totals-panel { background: #D6336C !important; color:#fff !important }

                /* Show top spacer in printed output to maintain outer space */
                .po-top-spacer { display:block !important; height:24px !important; }

                /* Solid fallbacks for PDF engines that strip gradients; use explicit hex values */
                .po-top { background: #FFE8F0 !important; color: #000 !important; }
                .supplier-card { background: #FFFFFF !important; color: #000 !important; border: 1px solid #e6e6e6 !important; }
                .totals-panel { background: #D6336C !important; color: #fff !important; }

                /* Remove heavy shadows and rounded clipping which some renderers mis-handle */
                .card { box-shadow: none !important; border-radius: 0 !important; }

                /* Table page break rules: avoid breaking inside rows */
                table { width:100%; border-collapse: collapse; }
                tr, td, th { page-break-inside: avoid; }

                /* Hide interactive UI in print */
                .no-print{display:none!important;}
            }
        </style>

    <div class="po-top d-flex justify-content-between align-items-center mb-3" style="background-color:#FFF9FB;-webkit-print-color-adjust:exact;">
            <div class="d-flex align-items-center gap-3">
                @if(optional($po->company)->logo)
                    <img src="{{ asset(optional($po->company)->logo) }}" alt="{{ $po->company->name }}" style="height:48px; width:auto; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,0.06)">
                @else
                    <div style="height:64px;width:64px;border-radius:8px;background:var(--po-accent-2);display:flex;align-items:center;justify-content:center;color:var(--po-accent);font-weight:700">{{ strtoupper(substr(optional($po->company)->name ?? 'CO',0,2)) }}</div>
                @endif
                <div>
                    <div class="fw-bold" style="color:var(--po-text)">{{ optional($po->company)->name }}</div>
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
                        <div class="small text-muted">{!! nl2br(e($addr)) !!}</div>
                        <div class="small text-muted mt-1">Mobile: {{ optional($po->company)->mobile ?? '-' }} @if(optional($po->company)->gst_no) | GST: {{ optional($po->company)->gst_no }} @endif</div>
                        <div class="small text-muted">Email: {{ optional($po->company)->email ?? '-' }}</div>
                </div>
            </div>

            <div class="text-end">
                <div class="po-badge" style="background-color:#D6336C;color:#fff;-webkit-print-color-adjust:exact">{{ $po->po_number }}</div>
                <div class="small text-muted mt-2">PO Date: <strong>{{ optional($po->po_date)->format('d M Y') ?? '-' }}</strong></div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <div class="supplier-card">
                    <div class="small text-muted">Bill To</div>
                    <div class="fw-semibold">{{ optional($po->supplier)->name ?? '-' }}</div>
                    <div class="small text-muted mt-1">{!! nl2br(e(optional($po->supplier)->address ?? '')) !!}</div>
                    <div class="small text-muted mt-1">Mobile: {{ optional($po->supplier)->mobile ?? '-' }}</div>
                    <div class="small text-muted">Email: {{ optional($po->supplier)->email ?? '-' }}</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="supplier-card text-md-end">
                    <div class="small text-muted">Deliver To</div>
                    <div class="fw-semibold">{{ optional($po->project)->name ?? '-' }}</div>
                    <div class="small text-muted mt-1">{!! nl2br(e($projAddr)) !!}</div>
                </div>
            </div>
        </div>

        <div class="table-responsive mb-3">
            <table class="table table-sm table-items align-middle">
                <thead>
                    <tr class="text-muted small">
                        <th style="width:40px;background-color:#FFE8F0;color:#D6336C">#</th>
                        <th style="background-color:#FFE8F0;color:#D6336C">Description</th>
                        <th style="width:80px;background-color:#FFE8F0;color:#D6336C">UOM</th>
                        <th class="text-end" style="width:90px;background-color:#FFE8F0;color:#D6336C">Qty</th>
                        <th class="text-end" style="width:130px;background-color:#FFE8F0;color:#D6336C">Unit Price</th>
                        <th class="text-end" style="width:140px;background-color:#FFE8F0;color:#D6336C">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($po->items as $i => $item)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{!! nl2br(e($item->description ?? '-')) !!}</td>
                        <td>{{ optional($item->uom)->name ?? '-' }}</td>
                        <td class="text-end">{{ $fmt($item->quantity) }}</td>
                        <td class="text-end">{{ $fmt($item->unit_price) }}</td>
                        <td class="text-end amount-cell">{{ $fmt($item->total) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">No items found for this PO.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="row g-3 align-items-start">
            <div class="col-md-7">
                {{-- Site Engineer & Notes (left column under products) --}}
                <div class="mb-3">
                    <div class="small text-muted">Site Engineer</div>
                    <div class="fw-semibold">{{ optional($po->siteEngineer)->name ?? '-' }} @if(optional($po->siteEngineer)->mobile) | {{ optional($po->siteEngineer)->mobile }} @endif</div>
                </div>
                @if($po->notes)
                <div class="small text-muted">Notes / Terms</div>
                <div class="mt-2">{!! nl2br(e($po->notes)) !!}</div>
                @endif
            </div>

            <div class="col-md-5">
                <div class="totals-panel" style="background-color:#D6336C;color:#fff;-webkit-print-color-adjust:exact;border-radius:8px;padding:12px;">
                    <div class="d-flex justify-content-between fw-bold">
                        <div>Grand Total</div>
                        <div>{{ config('app.currency_symbol', '₹') }} {{ $fmt($po->amount) }}</div>
                    </div>
                </div>
            </div>
        </div>


        <div class="row mt-5 align-items-center">
            <div class="col-md-6">
                <div class="small text-muted">Prepared By</div>
                <div class="mt-3">{{ optional($po->createdBy)->name ?? auth()->user()->name ?? '________________' }}</div>
            </div>
            <div class="col-md-6 text-md-end">
                <div class="small text-muted">Authorized Signature</div>
                <div class="mt-3">
                    @if(optional($po->company)->authorized_image)
                        <img src="{{ asset(optional($po->company)->authorized_image) }}" alt="Authorized" style="max-height:80px;" />
                    @else
                        ______________________________
                    @endif
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
