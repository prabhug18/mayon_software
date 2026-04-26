@php
$fmt = fn($v) => number_format((float)$v, 2);

// Embed company logo as base64
$companyLogoSrc = null;
if (optional($quotation->company)->logo) {
    $logoPath = public_path(optional($quotation->company)->logo);
    if (file_exists($logoPath) && is_readable($logoPath)) {
        $type = pathinfo($logoPath, PATHINFO_EXTENSION);
        $companyLogoSrc = 'data:image/' . $type . ';base64,' . base64_encode(file_get_contents($logoPath));
    }
}

// Embed authorized signature image as base64
$authSigSrc = null;
if (optional($quotation->company)->authorized_image) {
    $sigPath = public_path(optional($quotation->company)->authorized_image);
    if (file_exists($sigPath) && is_readable($sigPath)) {
        $sType = pathinfo($sigPath, PATHINFO_EXTENSION);
        $authSigSrc = 'data:image/' . $sType . ';base64,' . base64_encode(file_get_contents($sigPath));
    }
}

$blue = "#1a3a8a";
$lightBlue = "#e8edf8";
$darkText = "#1a1a2e";
$grayText = "#555555";
$companyName = optional($quotation->company)->name ?? 'MAYON FLOORING';
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $quotation->quotation_no ?? 'Quotation' }}</title>
    <style>
        /* ───────── PAGE ───────── */
        @page {
            size: A4 portrait;
            margin: 20mm 18mm 15mm 18mm;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9px;
            color: {{ $darkText }};
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }

        /* ───────── HEADER BAND ───────── */
        .header-band {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 3px solid {{ $blue }};
            margin-bottom: 18px;
        }
        .header-band td {
            vertical-align: middle;
            padding: 0 0 12px 0;
        }
        .hdr-logo-cell {
            width: 40%;
        }
        .hdr-info-cell {
            width: 60%;
            text-align: right;
        }
        .logo-img {
            max-height: 60px;
            max-width: 200px;
        }
        .company-name-text {
            font-size: 20px;
            font-weight: bold;
            color: {{ $blue }};
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .company-addr {
            font-size: 8px;
            color: {{ $grayText }};
            line-height: 1.5;
            margin-top: 3px;
        }

        /* ───────── DETAILS GRID ───────── */
        .details-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .details-grid td {
            vertical-align: top;
            padding: 0;
        }
        .to-cell { width: 58%; }
        .ref-cell { width: 42%; text-align: right; }

        .to-label {
            font-size: 8px;
            font-weight: bold;
            color: {{ $blue }};
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .to-name {
            font-size: 11px;
            font-weight: bold;
            color: {{ $darkText }};
        }
        .to-addr {
            font-size: 9px;
            color: {{ $grayText }};
            line-height: 1.5;
            margin-top: 3px;
        }

        .ref-table {
            border-collapse: collapse;
            margin-left: auto;
        }
        .ref-table td {
            padding: 3px 0;
            font-size: 10px;
        }
        .ref-label {
            font-weight: bold;
            color: {{ $blue }};
            padding-right: 10px;
            text-align: right;
        }
        .ref-val {
            color: {{ $darkText }};
            text-align: left;
        }

        /* ───────── SUBJECT ───────── */
        .subject-line {
            font-size: 10px;
            font-weight: bold;
            color: {{ $darkText }};
            margin-bottom: 18px;
            padding: 8px 12px;
            background: {{ $lightBlue }};
            border-left: 4px solid {{ $blue }};
        }

        /* ───────── ITEMS TABLE ───────── */
        .items-tbl {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .items-tbl th {
            background: {{ $blue }};
            color: #ffffff;
            font-size: 7.5px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 7px 5px;
            text-align: center;
            border: 1px solid {{ $blue }};
        }
        .items-tbl td {
            border: 1px solid #bfcae0;
            padding: 8px 6px;
            vertical-align: top;
            font-size: 8.5px;
        }
        .items-tbl tr:nth-child(even) td {
            background: #f8f9fc;
        }

        .sno-col   { width: 28px; text-align: center; }
        .desc-col  { /* auto width - takes remaining */ }
        .unit-col  { width: 40px; text-align: center; }
        .qty-col   { width: 45px; text-align: center; }
        .rate-col  { width: 65px; text-align: right; }
        .gst-col   { width: 50px; text-align: center; }
        .amt-col   { width: 75px; text-align: right; }

        .item-svc-name {
            font-weight: bold;
            color: {{ $blue }};
            font-size: 9px;
            display: block;
            margin-bottom: 2px;
        }
        .item-sub-name {
            font-size: 7.5px;
            color: #6c757d;
            font-weight: bold;
            display: block;
            margin-bottom: 2px;
        }
        .item-desc-text {
            font-size: 8px;
            color: {{ $grayText }};
            line-height: 1.5;
            margin-top: 3px;
        }

        /* ───────── TOTALS ───────── */
        .totals-outer {
            width: 100%;
            margin-bottom: 20px;
        }
        .totals-tbl {
            width: 250px;
            margin-left: auto;
            border-collapse: collapse;
        }
        .totals-tbl td {
            padding: 6px 10px;
            font-size: 9px;
            border: 1px solid #bfcae0;
        }
        .tot-label {
            font-weight: bold;
            color: {{ $darkText }};
            background: #f8f9fc;
        }
        .tot-val {
            text-align: right;
            color: {{ $darkText }};
        }
        .grand-label {
            font-weight: bold;
            color: #fff;
            background: {{ $blue }};
            font-size: 10px;
        }
        .grand-val {
            text-align: right;
            font-weight: bold;
            color: {{ $blue }};
            font-size: 11px;
            background: {{ $lightBlue }};
        }

        /* ───────── TERMS ───────── */
        .terms-section {
            margin-top: 25px;
            page-break-inside: avoid;
        }
        .section-heading {
            font-size: 10px;
            font-weight: bold;
            color: {{ $blue }};
            text-transform: uppercase;
            border-bottom: 2px solid {{ $blue }};
            padding-bottom: 4px;
            margin-bottom: 10px;
        }
        .terms-body {
            font-size: 8.5px;
            color: {{ $grayText }};
            line-height: 1.6;
        }
        .terms-body ul {
            padding-left: 15px;
            margin: 0;
        }
        .terms-body li {
            margin-bottom: 4px;
        }

        /* ───────── BANK ───────── */
        .bank-box {
            border: 1px solid #bfcae0;
            padding: 10px 14px;
            margin-top: 18px;
            width: 320px;
            background: #fafbfd;
        }
        .bank-heading {
            font-weight: bold;
            color: {{ $blue }};
            font-size: 9px;
            text-transform: uppercase;
            margin-bottom: 6px;
            border-bottom: 1px solid #bfcae0;
            padding-bottom: 4px;
        }
        .bank-tbl {
            width: 100%;
            border-collapse: collapse;
        }
        .bank-tbl td {
            padding: 2px 0;
            font-size: 8.5px;
        }
        .bank-key {
            font-weight: bold;
            color: {{ $darkText }};
            width: 90px;
        }
        .bank-value {
            color: {{ $grayText }};
        }

        /* ───────── SIGNATURE ───────── */
        .sig-tbl {
            width: 100%;
            border-collapse: collapse;
            margin-top: 45px;
        }
        .sig-tbl td {
            vertical-align: bottom;
        }
        .sig-left {
            font-style: italic;
            color: #888;
            font-size: 8.5px;
            width: 50%;
        }
        .sig-right {
            text-align: right;
            width: 50%;
        }
        .sig-for {
            font-weight: bold;
            color: {{ $darkText }};
            font-size: 10px;
            margin-bottom: 50px;
        }
        .sig-img {
            max-height: 50px;
            max-width: 120px;
            margin-bottom: 5px;
        }
        .sig-line {
            font-weight: bold;
            color: {{ $blue }};
            font-size: 9px;
            text-decoration: underline;
        }
        .sig-note {
            font-size: 7px;
            color: #aaa;
            margin-top: 3px;
        }
    </style>
</head>
<body>

    {{-- ═══════════════════════════════════════ --}}
    {{-- HEADER BAND                            --}}
    {{-- ═══════════════════════════════════════ --}}
    <table class="header-band">
        <tr>
            <td class="hdr-logo-cell">
                @if($companyLogoSrc)
                    <img src="{{ $companyLogoSrc }}" class="logo-img">
                @else
                    <span class="company-name-text">{{ $companyName }}</span>
                @endif
            </td>
            <td class="hdr-info-cell">
                @if($companyLogoSrc)
                    <div class="company-name-text">{{ $companyName }}</div>
                @endif
                <div class="company-addr">
                    {!! nl2br(e(optional($quotation->company)->address)) !!}
                </div>
                <div style="font-size: 8.5px; margin-top: 4px;">
                    Ph: {{ optional($quotation->company)->mobile }}
                    @if(optional($quotation->company)->email)
                        | {{ $quotation->company->email }}
                    @endif
                </div>
                @if(optional($quotation->company)->gst_no)
                    <div style="font-size: 9px; font-weight: bold; color: {{ $blue }}; margin-top: 3px;">
                        GSTIN: {{ $quotation->company->gst_no }}
                    </div>
                @endif
            </td>
        </tr>
    </table>

    {{-- ═══════════════════════════════════════ --}}
    {{-- TO / REFERENCE                         --}}
    {{-- ═══════════════════════════════════════ --}}
    <table class="details-grid">
        <tr>
            <td class="to-cell">
                <div class="to-label">To</div>
                <div class="to-name">
                    {{ $quotation->customer_name ?: (optional($quotation->enquiry)->name ?? 'Valued Customer') }}
                </div>
                <div class="to-addr">
                    @if($quotation->customer_address)
                        {!! nl2br(e($quotation->customer_address)) !!}
                    @elseif(optional($quotation->enquiry)->location)
                        {!! nl2br(e($quotation->enquiry->location)) !!}
                    @endif
                    @if(!$quotation->customer_address && optional($quotation->enquiry)->mobile)
                        <br>Mobile: {{ $quotation->enquiry->mobile }}
                    @endif
                </div>
            </td>
            <td class="ref-cell">
                <table class="ref-table">
                    <tr>
                        <td class="ref-label">DATE:</td>
                        <td class="ref-val">{{ $quotation->quotation_date->format('d.m.Y') }}</td>
                    </tr>
                    <tr>
                        <td class="ref-label">REF NO:</td>
                        <td class="ref-val">{{ $quotation->quotation_no }}</td>
                    </tr>
                    @if($quotation->valid_till)
                    <tr>
                        <td class="ref-label">VALID TILL:</td>
                        <td class="ref-val">{{ $quotation->valid_till->format('d.m.Y') }}</td>
                    </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    @if($quotation->kind_att)
    <div style="font-size: 10px; font-weight: bold; margin-bottom: 10px;">
        Kind Att: {{ $quotation->kind_att }}
    </div>
    @endif

    {{-- ═══════════════════════════════════════ --}}
    {{-- SUBJECT                                --}}
    {{-- ═══════════════════════════════════════ --}}
    <div class="subject-line">
        Sub: Quotation for {{ optional($quotation->items->first()?->service)->name ?? 'requested services' }}
        @if($quotation->customer_name)
            for {{ $quotation->customer_name }} site.
        @endif
    </div>

    {{-- ═══════════════════════════════════════ --}}
    {{-- ITEMS TABLE                            --}}
    {{-- ═══════════════════════════════════════ --}}
    <table class="items-tbl">
        <thead>
            <tr>
                <th class="sno-col">S.No</th>
                <th class="desc-col">Description of Goods &amp; Services</th>
                <th class="unit-col">Unit</th>
                <th class="qty-col">Qty</th>
                <th class="rate-col">Rate (&#x20B9;)</th>
                <th class="gst-col">GST %</th>
                <th class="amt-col">Amount (&#x20B9;)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quotation->items as $index => $item)
            <tr>
                <td class="sno-col">{{ $index + 1 }}</td>
                <td class="desc-col">
                    @php
                        $itemName = $item->serviceItem?->item_name ?? $item->manual_item_name;
                    @endphp
                    @if($itemName)
                        <div class="item-svc-name" style="font-size: 9.5px; margin-bottom: 4px;">{{ $itemName }}</div>
                    @endif
                    @if($item->description)
                        <div class="item-desc-text" style="font-size: 8.5px; margin-top: 2px;">{!! nl2br(e($item->description)) !!}</div>
                    @endif
                </td>
                <td class="unit-col">{{ $item->unit }}</td>
                <td class="qty-col">{{ $item->quantity }}</td>
                <td class="rate-col">{{ $fmt($item->selling_rate) }}</td>
                <td class="gst-col">{{ (float)$item->gst_percentage }}%</td>
                <td class="amt-col">{{ $fmt($item->line_total) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ═══════════════════════════════════════ --}}
    {{-- TOTALS                                 --}}
    {{-- ═══════════════════════════════════════ --}}
    <div class="totals-outer">
        <table class="totals-tbl">
            <tr>
                <td class="tot-label">Sub-Total</td>
                <td class="tot-val">&#x20B9; {{ $fmt($quotation->subtotal) }}</td>
            </tr>
            <tr>
                <td class="tot-label">GST</td>
                <td class="tot-val">&#x20B9; {{ $fmt($quotation->gst_total) }}</td>
            </tr>
            <tr>
                <td class="grand-label">Grand Total</td>
                <td class="grand-val">&#x20B9; {{ $fmt($quotation->grand_total) }}</td>
            </tr>
        </table>
    </div>

    {{-- ═══════════════════════════════════════ --}}
    {{-- TERMS & CONDITIONS                     --}}
    {{-- ═══════════════════════════════════════ --}}
    <div class="terms-section">
        <div class="section-heading">Terms &amp; Conditions</div>
        <div class="terms-body">
            @if($quotation->terms_content)
                {!! $quotation->terms_content !!}
            @else
                <ul>
                    <li><strong>Validity:</strong> This quotation is valid for 10 days from the date of issue.</li>
                    <li><strong>Taxes:</strong> Rates are exclusive of GST. GST will be charged as applicable at prevailing rates.</li>
                    <li><strong>Payment Terms (40-40-20):</strong> 40% Advance upon work order confirmation, 40% against RA bill / milestone, 20% on completion and final measurement.</li>
                    <li><strong>Scope &amp; Measurement:</strong> Final billing based on actual site measurements and work executed as per agreed scope.</li>
                    <li><strong>Changes / Additional Works:</strong> Any changes in scope, quantities, or specifications will be treated as a variation and charged extra with prior confirmation.</li>
                    <li><strong>Site Readiness:</strong> Client to ensure the site is ready, clear, and accessible as per agreed schedule.</li>
                    <li><strong>Utilities:</strong> Client to provide power and water at site, free of cost, for smooth execution.</li>
                </ul>
            @endif
        </div>
    </div>

    {{-- ═══════════════════════════════════════ --}}
    {{-- BANK DETAILS                           --}}
    {{-- ═══════════════════════════════════════ --}}
    <div class="bank-box">
        <div class="bank-heading">Bank Account Details</div>
        <table class="bank-tbl">
            <tr><td class="bank-key">Beneficiary:</td><td class="bank-value">MAYON INTERIORS INFRA SOLUTION</td></tr>
            <tr><td class="bank-key">Bank:</td><td class="bank-value">State Bank of India</td></tr>
            <tr><td class="bank-key">Account No:</td><td class="bank-value">40933397145</td></tr>
            <tr><td class="bank-key">Branch:</td><td class="bank-value">Old Madras Road, KR Puram</td></tr>
            <tr><td class="bank-key">IFSC Code:</td><td class="bank-value">SBIN0040744</td></tr>
        </table>
    </div>

    {{-- ═══════════════════════════════════════ --}}
    {{-- SIGNATURE                              --}}
    {{-- ═══════════════════════════════════════ --}}
    <table class="sig-tbl">
        <tr>
            <td class="sig-left">
                Thank you for your business!<br>
                We look forward to working with you.
            </td>
            <td class="sig-right">
                <div class="sig-for">For {{ $companyName }}</div>
                @if($authSigSrc)
                    <img src="{{ $authSigSrc }}" class="sig-img"><br>
                @endif
                <div class="sig-line">Authorized Signatory</div>
                <div class="sig-note">(Computer generated document)</div>
            </td>
        </tr>
    </table>

</body>
</html>
