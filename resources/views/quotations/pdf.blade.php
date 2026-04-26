@php 
$fmt = fn($v) => number_format((float)$v, 2);

// Embed company logo
$companyLogoSrc = null;
if (optional($quotation->company)->logo) {
    $logoPath = public_path(optional($quotation->company)->logo);
    if (file_exists($logoPath) && is_readable($logoPath)) {
        $type = pathinfo($logoPath, PATHINFO_EXTENSION);
        $companyLogoSrc = 'data:image/' . $type . ';base64,' . base64_encode(file_get_contents($logoPath));
    }
}

// Embed authorized signature
$sigSrc = null;
if (optional($quotation->company)->authorized_image) {
    $sigPath = public_path(optional($quotation->company)->authorized_image);
    if (file_exists($sigPath) && is_readable($sigPath)) {
        $type = pathinfo($sigPath, PATHINFO_EXTENSION);
        $sigSrc = 'data:image/' . $type . ';base64,' . base64_encode(file_get_contents($sigPath));
    }
}

// Group items by service
$groupedItems = $quotation->items->groupBy('service_id');
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>{{ $quotation->quotation_no ?? 'Quotation' }}</title>
    <style>
        body{ font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif; color:#222; font-size:13px; margin:0; padding:0; }
        .card { max-width:820px; margin:18px auto; padding:12px 18px; background:#fff }
        .header { background:#F0F8FF; padding:10px; border-radius:6px; margin-bottom:15px; }
        .quotation-badge { background:#2563EB; color:#fff; padding:6px 10px; border-radius:6px; font-weight:700; display:inline-block; }
        .client-card { border-radius:6px; padding:8px; border:1px solid rgba(0,0,0,0.06); background:#fff; margin-bottom:10px; }
        table.items { width:100%; border-collapse:collapse; margin-top:10px; font-size:12px; }
        table.items thead th { background:#DBEAFE; color:#2563EB; font-weight:700; padding:8px 6px; border-bottom:2px solid #2563EB; text-align:left; }
        table.items td, table.items th { padding:6px; border-bottom:1px solid #eee; }
        .amount-cell { color:#2563EB; font-weight:700; text-align:right; }
        .service-header { background:#F3F4F6; font-weight:700; padding:8px; margin-top:10px; border-left:4px solid #2563EB; }
        .totals-panel { background:#2563EB; color:#fff; padding:10px; border-radius:6px; margin-top:15px; }
        .terms-section { margin-top:15px; padding:10px; background:#F9FAFB; border-radius:6px; font-size:11px; }
        @page { size:A4 portrait; margin:18mm; }
        .watermark { position:fixed; top:50%; left:50%; transform:translate(-50%,-50%) rotate(-45deg); font-size:120px; color:rgba(255,0,0,0.1); font-weight:700; z-index:-1; }
        .signature-section { margin-top:30px; text-align:right; }
    </style>
</head>
<body>

@if($quotation->revision_no > 0)
<div class="watermark">REVISED</div>
@endif

<div class="card">
    <div class="header">
        <table style="width:100%;border-collapse:collapse;">
        <tr>
            <td style="vertical-align:top;width:65%;padding:0">
                <table style="border-collapse:collapse;width:100%"><tr>
                    <td style="width:72px;vertical-align:top;padding-right:12px">
                        @if($companyLogoSrc)
                            <img src="{{ $companyLogoSrc }}" alt="{{ optional($quotation->company)->name }}" style="height:64px; width:auto; border-radius:8px;">
                        @else
                            <div style="height:64px;width:64px;border-radius:8px;background:#DBEAFE;display:inline-flex;align-items:center;justify-content:center;color:#2563EB;font-weight:700">{{ strtoupper(substr(optional($quotation->company)->name ?? 'CO',0,2)) }}</div>
                        @endif
                    </td>
                    <td style="vertical-align:top">
                        <div style="font-size:16px;font-weight:700;color:#2563EB;margin-bottom:4px">{{ optional($quotation->company)->name ?? 'Company Name' }}</div>
                        <div style="font-size:11px;line-height:1.4;color:#555">
                            {!! nl2br(e(optional($quotation->company)->address ?? '')) !!}
                            @if(optional($quotation->company)->gst_no)
                            <br><strong>GSTIN:</strong> {{ $quotation->company->gst_no }}
                            @endif
                        </div>
                    </td>
                </tr></table>
            </td>
            <td style="vertical-align:top;width:35%;text-align:right;padding:0">
                <div class="quotation-badge">QUOTATION</div>
                <div style="margin-top:8px;font-size:12px">
                    <strong>{{ $quotation->quotation_no }}</strong><br>
                    <span style="font-size:11px;color:#666">Date: {{ $quotation->quotation_date->format('d M Y') }}</span>
                    @if($quotation->valid_till)
                    <br><span style="font-size:11px;color:#666">Valid Till: {{ $quotation->valid_till->format('d M Y') }}</span>
                    @endif
                </div>
            </td>
        </tr>
        </table>
    </div>

    @if($quotation->enquiry)
    <div class="client-card">
        <div style="font-weight:700;margin-bottom:4px;color:#2563EB">Client Details</div>
        <div style="font-size:12px;line-height:1.5">
            <strong>{{ $quotation->enquiry->name }}</strong><br>
            @if($quotation->enquiry->mobile)Mobile: {{ $quotation->enquiry->mobile }}<br>@endif
            @if($quotation->enquiry->location)Location: {{ $quotation->enquiry->location }}@endif
        </div>
    </div>
    @endif

    <div style="margin-top:15px">
        <div style="font-weight:700;font-size:14px;margin-bottom:10px;color:#2563EB">Quotation Details</div>
        
        @foreach($groupedItems as $serviceId => $items)
            @php $service = $items->first()->service; @endphp
            <div class="service-header">{{ $service->name }}</div>
            
            <table class="items">
                <thead>
                    <tr>
                        <th style="width:5%">#</th>
                        <th style="width:30%">Item</th>
                        <th style="width:30%">Description</th>
                        <th style="width:8%">Unit</th>
                        <th style="width:8%">Qty</th>
                        <th style="width:12%">Rate</th>
                        <th style="width:7%">GST%</th>
                        <th style="width:12%;text-align:right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $index => $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->serviceItem->item_name }}</td>
                        <td style="font-size:11px;color:#666">{{ $item->description ?: '-' }}</td>
                        <td>{{ $item->unit }}</td>
                        <td>{{ $fmt($item->quantity) }}</td>
                        <td>₹ {{ $fmt($item->selling_rate) }}</td>
                        <td>{{ $fmt($item->gst_percentage) }}%</td>
                        <td class="amount-cell">₹ {{ $fmt($item->line_total) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endforeach
    </div>

    <div style="margin-top:15px">
        <table style="width:100%;border-collapse:collapse">
        <tr>
            <td style="width:60%"></td>
            <td style="width:40%">
                <div class="totals-panel">
                    <table style="width:100%;border-collapse:collapse;color:#fff">
                        <tr>
                            <td style="padding:4px 0;font-size:12px">Subtotal:</td>
                            <td style="padding:4px 0;text-align:right;font-size:12px">₹ {{ $fmt($quotation->subtotal) }}</td>
                        </tr>
                        <tr>
                            <td style="padding:4px 0;font-size:12px">GST Total:</td>
                            <td style="padding:4px 0;text-align:right;font-size:12px">₹ {{ $fmt($quotation->gst_total) }}</td>
                        </tr>
                        <tr style="border-top:1px solid rgba(255,255,255,0.3)">
                            <td style="padding:8px 0 4px 0;font-size:14px;font-weight:700">Grand Total:</td>
                            <td style="padding:8px 0 4px 0;text-align:right;font-size:14px;font-weight:700">₹ {{ $fmt($quotation->grand_total) }}</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
        </table>
    </div>

    @if($quotation->terms_content || $quotation->termsCondition)
    <div class="terms-section">
        @if($quotation->terms_content)
            <div style="font-weight:700;margin-bottom:6px;color:#2563EB">Terms & Conditions</div>
            <div style="line-height:1.5">{!! $quotation->terms_content !!}</div>
        @else
            <div style="font-weight:700;margin-bottom:6px;color:#2563EB">{{ $quotation->termsCondition->title }}</div>
            <div style="line-height:1.5">{!! $quotation->termsCondition->content !!}</div>
        @endif
    </div>
    @endif

    <div class="signature-section">
        @if($sigSrc)
            <img src="{{ $sigSrc }}" alt="Authorized Signature" style="height:50px;width:auto;margin-bottom:5px">
        @endif
        <div style="font-weight:700;font-size:12px">Authorized Signatory</div>
        <div style="font-size:11px;color:#666">{{ optional($quotation->company)->name }}</div>
    </div>
</div>

</body>
</html>
