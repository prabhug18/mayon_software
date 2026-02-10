<div style="font-family: Arial, sans-serif; font-size:14px; color:#333;">
    <h3>Enquiry Follow-up Reminder</h3>
    <p>This is a reminder for the following enquiry that requires follow-up:</p>
    <ul>
        <li><strong>ID:</strong> {{ $enquiry->id }}</li>
        <li><strong>Name:</strong> {{ $enquiry->name }}</li>
        <li><strong>Mobile:</strong> {{ $enquiry->mobile }}</li>
        <li><strong>Source:</strong> {{ optional($enquiry->source)->name }}</li>
        <li><strong>Next Follow-up:</strong> {{ optional($enquiry->next_follow_up_at)->toDayDateTimeString() }}</li>
    </ul>
    <p><strong>Reminder Notes:</strong></p>
    <div style="background:#f8f9fa;padding:10px;border-radius:4px;">{{ $enquiry->reminder_notes }}</div>

    <p style="margin-top:16px;">View details: <a href="{{ config('app.url') }}/enquiries/{{ $enquiry->id }}">Open Enquiry</a></p>
</div>
