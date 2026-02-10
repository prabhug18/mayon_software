<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>{{ config('app.name','App') }}</title>
    <style>
        /* Basic reset and printable defaults */
        :root{
            --po-accent: #D6336C; /* rose pink */
            --po-accent-2: #FFE8F0; /* very light pink */
            --po-soft: #FFF8FB;
            --po-text: #2B1F2E;
        }

        body { font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif; color: #222; font-size: 13px; margin:0; padding:0; }
        img { max-width:100%; height:auto; }
        table { border-collapse: collapse; }
        th, td { vertical-align: top; }

        /* PO-specific helpers (mirror show.blade.php) */
        .po-top { background: linear-gradient(90deg, rgba(17,24,39,0.04), rgba(243,244,246,0.03)); padding:12px; border-radius:8px; }
        .po-badge { background: var(--po-accent); color: #fff; padding:6px 10px; border-radius:8px; font-weight:700; font-size:0.95rem }
        .supplier-card, .card-box { background: linear-gradient(180deg, #fff, var(--po-soft)); border-radius:8px; padding:10px; border:1px solid rgba(17,24,39,0.06); }
        .table-items thead th, table.items thead th { background: var(--po-accent-2); color: var(--po-accent); font-weight:700; border-bottom: 1px solid rgba(17,24,39,0.06); }
        .table-items tbody tr:nth-child(odd), table.items tbody tr:nth-child(odd) { background: #fff; }
        .table-items tbody tr:nth-child(even), table.items tbody tr:nth-child(even) { background: #FAFAFA; }
        .amount-cell, .amount { color: var(--po-accent); font-weight:700; }
        .totals-panel, .totals-box, .totals .box { background: linear-gradient(180deg,var(--po-accent), #0B1220); color:white; padding:12px; border-radius:8px; }

          /* Page and print adjustments: force A4 size for print and PDF */
          @page { size: A4 portrait; margin: 18mm; }

          /* Constrain layout to A4 content width (210mm wide minus left/right margins)
              Using mm units gives predictable PDF output across renderers */
          html, body { width: 210mm; height: 297mm; box-sizing: border-box; }
          .card { max-width: calc(210mm - 36mm); margin: 0 auto; }
        @media print{
            /* Preserve colors when printing from browser */
            html, body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }

            /* Solid fallbacks for PDF engines that strip gradients */
            .po-top { background: #FFE8F0 !important; color: #000 !important; }
            .supplier-card, .card-box { background: #FFFFFF !important; color: #000 !important; border: 1px solid #e6e6e6 !important; }
            .totals-panel, .totals-box, .totals .box { background: #D6336C !important; color: #fff !important; }

            /* Remove heavy shadows and rounded clipping that some renderers mis-handle */
            .card, .card-body { box-shadow: none !important; border-radius: 0 !important; }

            /* Avoid breaking inside table rows */
            table { width:100%; border-collapse: collapse; }
            tr, td, th { page-break-inside: avoid; }

            /* Hide interactive UI in print */
            .no-print{display:none!important;}
        }
    </style>
    @stack('head')
</head>
<body>
    @yield('content')
    @stack('scripts')
</body>
</html>
