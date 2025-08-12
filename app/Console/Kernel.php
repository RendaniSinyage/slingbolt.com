<?php
// app/Console/Kernel.php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\CleanupUnverifiedCompaniesJob;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     */
    protected $commands = [
        Commands\CheckExpiredPlans::class,
        \Modules\LendingTmp\Console\ApplyLoanPenalties::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Clean up unverified companies every 12 hours (2 AM and 2 PM)
        $schedule->job(new CleanupUnverifiedCompaniesJob(48))
                 ->twiceDaily(2, 14)
                 ->withoutOverlapping();

        // Run plan expiration check daily at 9:00 AM
        $schedule->command('plans:check-expired')
                ->dailyAt('09:00')
                ->withoutOverlapping()
                ->runInBackground();

        // Run loan penalty calculation daily
        $schedule->command('lending:apply-penalties')->daily();

        // Run loan document cleanup daily
        $schedule->command('lending:cleanup-documents')->daily();

        // Fetch tenders daily
        $schedule->command('tenders:fetch')->daily();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}