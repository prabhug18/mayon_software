@extends('layouts.print')

@section('content')
@php $fmt = fn($v) => number_format((float)$v, 2); @endphp

<div style="width:800px;margin:0 auto;padding:20px;font-family: Arial, Helvetica, sans-serif;color:#222;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
        <div style="display:flex;align-items:center;gap:12px;">
            @if(optional($po->company)->logo)
                <img src="{{ asset(optional($po->company)->logo) }}" alt="{{ optional($po->company)->name }}" style="height:64px;object-fit:contain;" />
            @endif
            <div>
                <div style="font-weight:700;font-size:18px">{{ optional($po->company)->name }}</div>
                <div style="font-size:12px;color:#555">{{ optional($po->company)->address ?? '' }}</div>
                <div style="font-size:12px;color:#555">Mobile: {{ optional($po->company)->mobile ?? '-' }} @if(optional($po->company)->gst_no) | GST: {{ optional($po->company)->gst_no }} @endif</div>
            </div>
        </div>
        <div style="text-align:right;">
            <div style="background:#D6336C;color:#fff;padding:8px 12px;border-radius:6px;font-weight:700">{{ $po->po_number }}</div>
            <div style="font-size:12px;color:#666;margin-top:8px">PO Date: <strong>{{ optional($po->po_date)->format('d M Y') ?? '-' }}</strong></div>
        </div>
    </div>

    <div style="display:flex;gap:20px;margin-bottom:16px;">
        <div style="flex:1;border:1px solid #eee;padding:10px;border-radius:6px;">
            <div style="font-size:12px;color:#777">Bill To</div>
            <div style="font-weight:700">{{ optional($po->supplier)->name ?? '-' }}</div>
            <div style="font-size:12px;color:#555;margin-top:6px">{!! nl2br(e(optional($po->supplier)->address ?? '')) !!}</div>
            <div style="font-size:12px;color:#555;margin-top:6px">Mobile: {{ optional($po->supplier)->mobile ?? '-' }}</div>
        </div>
        <div style="flex:1;border:1px solid #eee;padding:10px;border-radius:6px;">
            <div style="font-size:12px;color:#777">Deliver To</div>
            <div style="font-weight:700">{{ optional($po->project)->name ?? '-' }}</div>
            <div style="font-size:12px;color:#555;margin-top:6px">{!! nl2br(e(optional($po->project)->address ?: optional($po->project)->location ?: '-')) !!}</div>
        </div>
    </div>

    <table style="width:100%;border-collapse:collapse;margin-bottom:14px;font-size:13px;">
        <thead>
            <tr style="background:#FFE8F0;color:#D6336C;font-weight:700;">
                <th style="padding:8px;border:1px solid #eee;width:40px;text-align:left">#</th>
                <th style="padding:8px;border:1px solid #eee;text-align:left">Description</th>
                <th style="padding:8px;border:1px solid #eee;width:90px;text-align:right">Qty</th>
                <th style="padding:8px;border:1px solid #eee;width:120px;text-align:right">Unit Price</th>
                <th style="padding:8px;border:1px solid #eee;width:140px;text-align:right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($po->items as $i => $item)
            <tr>
                <td style="padding:8px;border:1px solid #eee">{{ $i+1 }}</td>
                <td style="padding:8px;border:1px solid #eee">{!! nl2br(e($item->description ?? '-')) !!}</td>
                <td style="padding:8px;border:1px solid #eee;text-align:right">{{ $fmt($item->quantity) }}</td>
                <td style="padding:8px;border:1px solid #eee;text-align:right">{{ $fmt($item->unit_price) }}</td>
                <td style="padding:8px;border:1px solid #eee;text-align:right">{{ $fmt($item->total) }}</td>
            </tr>
            @empty
            <tr><td colspan="5" style="padding:12px;text-align:center;color:#999">No items found for this PO.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:20px;">
        <div style="width:50%;">
            <div style="font-size:12px;color:#777">Prepared By</div>
            <div style="margin-top:12px;font-weight:700">{{ optional($po->createdBy)->name ?? auth()->user()->name ?? '________________' }}</div>
        </div>
        <div style="width:40%;text-align:right;">
            <div style="font-size:12px;color:#777">Authorized Signature</div>
            <div style="margin-top:12px;">
                @if(optional($po->company)->authorized_image)
                    <img src="{{ asset(optional($po->company)->authorized_image) }}" alt="Authorized" style="max-height:80px;object-fit:contain;" />
                @else
                    ______________________________
                @endif
            </div>
        </div>
    </div>

</div>
@endsection
