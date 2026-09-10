@php $fmt = fn($v) => number_format((float)$v, 2); @endphp
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,initial-scale:1">
	<title>{{ $po->po_number ?? 'Purchase Order' }}</title>
	<style>
		@php
			$fmt = fn($v) => number_format((float)$v, 2);

			// embed images for dompdf
			$companyLogoSrc = null;
			if (optional($po->company)->logo) {
				$logoPath = public_path(optional($po->company)->logo);
				if (file_exists($logoPath) && is_readable($logoPath)) {
					$type = pathinfo($logoPath, PATHINFO_EXTENSION);
					$companyLogoSrc = 'data:image/' . $type . ';base64,' . base64_encode(file_get_contents($logoPath));
				} else {
					$companyLogoSrc = asset(optional($po->company)->logo);
				}
			}
			$sigSrc = null;
			if (optional($po->company)->authorized_image) {
				$sigPath = public_path(optional($po->company)->authorized_image);
				if (file_exists($sigPath) && is_readable($sigPath)) {
					$type = pathinfo($sigPath, PATHINFO_EXTENSION);
					$sigSrc = 'data:image/' . $type . ';base64,' . base64_encode(file_get_contents($sigPath));
				} else {
					$sigSrc = asset(optional($po->company)->authorized_image);
				}
			}

			$proj = $po->project ?? null;
			$projAddr = $proj ? ($proj->address ?? $proj->location ?? '-') : '-';
			$projAddrHtml = nl2br(e($projAddr));
		@endphp

		<!doctype html>
		<html lang="en">
		<head>
			<meta charset="utf-8">
			<meta name="viewport" content="width=device-width,initial-scale=1">
			<title>{{ $po->po_number ?? 'Purchase Order' }}</title>
			<style>
				@php
					$dejavuPath = base_path('vendor/dompdf/dompdf/lib/fonts/DejaVuSans.ttf');
					$dejavuData = null;
					if (file_exists($dejavuPath) && is_readable($dejavuPath)) {
						try { $dejavuData = base64_encode(file_get_contents($dejavuPath)); } catch(\Exception $e) { $dejavuData = null; }
					}
				@endphp
				@if(!empty($dejavuData))
				@font-face {
					font-family: 'DejaVuEmbedded';
					src: url('data:font/truetype;charset=utf-8;base64,{!! $dejavuData !!}') format('truetype');
					font-weight: normal;
					font-style: normal;
				}
				body{ font-family: 'DejaVuEmbedded', 'DejaVu Sans', Arial, Helvetica, sans-serif; color:#222; font-size:13px; }
				@else
				body{ font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif; color:#222; font-size:13px; }
				@endif
				/* Narrower card so PDF doesn't look too wide */
				.card { max-width:820px; margin:18px auto; padding:12px 18px; background:#fff }
				.po-top { background:#FFF9FB; padding:10px; border-radius:6px; display:table; width:100%; }
				.po-badge { background:#D6336C; color:#fff; padding:6px 10px; border-radius:6px; font-weight:700 }
				.supplier-card { border-radius:6px; padding:8px; border:1px solid rgba(0,0,0,0.06); background:#fff }
				table.items { width:100%; border-collapse:collapse; margin-top:10px; font-size:12.5px }
				table.items thead th { background:#FFE8F0; color:#D6336C; font-weight:700; padding:8px; border-bottom:2px solid #D6336C }
				table.items td, table.items th { padding:6px 8px; border-bottom:1px solid #eee }
				.amount-cell { color:#D6336C; font-weight:700; text-align:right }
				.totals-panel { background:#D6336C; color:#fff; padding:10px; border-radius:6px }
				/* Slightly reduce the width of description column to avoid overflow */
				table.items thead th:nth-child(2) { max-width:320px }
				table.items td:nth-child(2) { max-width:320px; word-wrap:break-word }
				@page { size:A4 portrait; margin:18mm }

				/* Force print colors and avoid color-stripping in some PDF engines */
				.po-badge{ -webkit-print-color-adjust: exact; print-color-adjust: exact; background:#D6336C !important; color:#fff !important }
				.supplier-card{ -webkit-print-color-adjust: exact; print-color-adjust: exact; background:#FFFFFF !important }
				.po-top{ -webkit-print-color-adjust: exact; print-color-adjust: exact; background:#FFF9FB !important }
				.totals-panel{ -webkit-print-color-adjust: exact; print-color-adjust: exact; background:#D6336C !important; color:#fff !important }
			</style>
		</head>
		<body>

		<div class="card">
			<div class="po-top">
				<table style="width:100%;border-collapse:collapse;">
				<tr>
					<td style="vertical-align:top;width:65%;padding:0">
						<div>
							@if($companyLogoSrc)
								<img src="{{ $companyLogoSrc }}" alt="{{ optional($po->company)->name }}" style="max-height:80px; width:auto; border-radius:8px; margin-bottom:8px;">
							@else
								<div style="height:64px;width:64px;border-radius:8px;background:#FFE8F0;display:inline-flex;align-items:center;justify-content:center;color:#D6336C;font-weight:700;margin-bottom:8px;">{{ strtoupper(substr(optional($po->company)->name ?? 'CO',0,2)) }}</div>
							@endif
						</div>
						<div>
							<div style="font-weight:700">{{ optional($po->company)->name }}</div>
							<div style="font-size:12px;color:#666">{!! nl2br(e(optional($po->company)->address ?? '')) !!}</div>
							<div style="font-size:12px;color:#666;margin-top:6px">Mobile: {{ optional($po->company)->mobile ?? '-' }} @if(optional($po->company)->gst_no) | GST: {{ optional($po->company)->gst_no }}@endif</div>
						</div>
					</td>
					<td style="vertical-align:top;width:35%;padding:0;text-align:right">
						<div style="display:inline-block;margin-bottom:6px;">
							<div class="po-badge">{{ $po->po_number }}</div>
						</div><br/>
						<div style="font-size:12px;color:#666;">PO Date: <strong>{{ optional($po->po_date)->format('d M Y') ?? '-' }}</strong></div>
					</td>
				</tr>
				</table>
			</div>

			<table style="width:100%;border-collapse:collapse;margin-top:14px">
				<tr>
					<td style="vertical-align:top;padding:0 6px 0 0;width:50%">
						<div class="supplier-card">
							<div style="font-size:12px;color:#777">Bill To</div>
							<div style="font-weight:700">{{ optional($po->supplier)->name ?? '-' }}</div>
							<div style="color:#555;margin-top:6px">{!! nl2br(e(optional($po->supplier)->address ?? '')) !!}</div>
							<div style="color:#555;margin-top:6px">Mobile: {{ optional($po->supplier)->mobile ?? '-' }}</div>
							<div style="color:#555;margin-top:6px">Email: {{ optional($po->supplier)->email ?? '-' }}</div>
						</div>
					</td>
					<td style="vertical-align:top;padding:0 0 0 6px;width:50%">
						<div class="supplier-card" style="text-align:right">
							<div style="font-size:12px;color:#777">Deliver To</div>
							<div style="font-weight:700">{{ optional($po->project)->name ?? '-' }}</div>
							<div style="color:#555;margin-top:6px">{!! $projAddrHtml !!}</div>
						</div>
					</td>
				</tr>
			</table>

			<table class="items">
				<thead>
					<tr>
						<th style="width:40px">#</th>
						<th>Description</th>
						<th style="width:80px">UOM</th>
						<th style="width:90px;text-align:right">Qty</th>
						<th style="width:130px;text-align:right">Unit Price</th>
						<th style="width:140px;text-align:right">Amount</th>
					</tr>
				</thead>
				<tbody>
					@forelse($po->items as $i => $item)
						<tr>
							<td>{{ $i + 1 }}</td>
							<td>{!! nl2br(e($item->description ?? '-')) !!}</td>
							<td>{{ optional($item->uom)->name ?? '-' }}</td>
							<td style="text-align:right">{{ $fmt($item->quantity) }}</td>
							<td style="text-align:right">{{ $fmt($item->unit_price) }}</td>
							<td class="amount-cell">{{ $fmt($item->total) }}</td>
						</tr>
					@empty
						<tr><td colspan="6" style="text-align:center;color:#999;padding:12px">No items found for this PO.</td></tr>
					@endforelse
				</tbody>
			</table>

			<table style="width:100%;border-collapse:collapse;margin-top:18px">
				<tr>
					<td style="vertical-align:top;padding:0;width:60%">
						<div style="font-size:12px;color:#777">Site Engineer</div>
						<div style="font-weight:700">{{ optional($po->siteEngineer)->name ?? '-' }} @if(optional($po->siteEngineer)->mobile) | {{ optional($po->siteEngineer)->mobile }} @endif</div>
						@if($po->notes)
							<div style="color:#777;margin-top:12px">Notes / Terms</div>
							<div style="margin-top:6px">{!! nl2br(e($po->notes)) !!}</div>
						@endif
					</td>
					<td style="vertical-align:top;padding:0;width:40%">
						<div class="totals-panel">
							<div style="display:flex;justify-content:space-between;font-weight:700">
								<div>Grand Total</div>
								<div>{!! '&#8377;' !!} {{ $fmt($po->amount) }}</div>
							</div>
						</div>
					</td>
				</tr>
			</table>

			<table style="width:100%;border-collapse:collapse;margin-top:28px">
				<tr>
					<td style="vertical-align:top;padding:0;width:60%">
						<div style="font-size:12px;color:#777">Prepared By</div>
						<div style="margin-top:8px">{{ optional($po->createdBy)->name ?? auth()->user()->name ?? '________________' }}</div>
					</td>
					<td style="vertical-align:top;padding:0;width:40%;text-align:right">
						<div style="font-size:12px;color:#777">Authorized Signature</div>
						<div style="margin-top:8px">
							@if($sigSrc)
								<img src="{{ $sigSrc }}" alt="Authorized" style="max-height:80px;" />
							@else
								______________________________
							@endif
						</div>
					</td>
				</tr>
			</table>

		</div>


		</div>

		@if(!empty($autoprint) && $autoprint)
		<script>
		    // Autoprint mode: trigger print dialog when page loads (used when opening in new tab via Print button)
		    window.addEventListener('load', function(){
		        try {
		            // small delay to allow resources (images/fonts) to load
		            setTimeout(function(){
		                window.print();
		            }, 250);
		        } catch(e) {
		            console && console.warn && console.warn('autoprint failed', e);
		        }
		    });
		    // Close the window after printing in browsers that support onafterprint
		    if (window.matchMedia) {
		        window.onafterprint = function(){ try{ window.close(); }catch(e){} };
		    }
		</script>
		@endif

		</body>
		</html>
			</div>
