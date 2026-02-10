<?php

namespace App\Console;

use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Console\Scheduling\Schedule;

class Kernel extends ConsoleKernel
{
    /**
     * The application's command schedule.
     *
     * @var \Illuminate\Console\Scheduling\Schedule
     */
    protected $schedule;

    /**
     * The application's command registry.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\MakeModule::class,
        \App\Console\Commands\SendEnquiryReminders::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        // run daily at 9am
        $schedule->command('enquiries:send-reminders')->dailyAt('09:00');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}