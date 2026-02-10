<div style="font-family: Arial, Helvetica, sans-serif; color:#222;">
    <p>Dear {{ optional($po->supplier)->name ?? 'Supplier' }},</p>
    <p>Please find attached the Purchase Order <strong>{{ $po->po_number }}</strong>.</p>
    <p>Regards,<br>{{ optional($po->company)->name ?? config('app.name') }}</p>
</div>
