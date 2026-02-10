<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Enquiry;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\EnquiryReminder;

class SendEnquiryReminders extends Command
{
    protected $signature = 'enquiries:send-reminders';
    protected $description = 'Send follow-up reminders for enquiries';

    public function handle()
    {
        $now = Carbon::now();
        $items = Enquiry::whereNotNull('next_follow_up_at')
            ->where('next_follow_up_at', '<=', $now)
            ->whereNull('reminder_sent_at')
            ->get();

        $sent = 0;
        foreach ($items as $enq) {
            try {
                $to = config('mail.from.address');
                if ($to) {
                    Mail::to($to)->send(new EnquiryReminder($enq));
                    $enq->reminder_sent_at = $now;
                    $enq->save();
                    $sent++;
                } else {
                    Log::warning('No mail.from.address configured; skipping reminder for enquiry '.$enq->id);
                }
            } catch (\Exception $e) {
                Log::error('Failed to send enquiry reminder for '.$enq->id.': '.$e->getMessage());
            }
        }

        $this->info('Processed '.count($items).' enquiry reminders; sent: '.$sent);
        return 0;
    }
}
