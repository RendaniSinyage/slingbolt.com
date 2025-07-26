<?php

namespace App\Jobs;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class CleanupUnverifiedCompaniesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 300;

    protected $hoursThreshold;

    public function __construct(int $hoursThreshold = 48)
    {
        $this->hoursThreshold = $hoursThreshold;
    }

    public function handle()
    {
        Log::info("Starting automated cleanup of unverified companies (>{$this->hoursThreshold}h)");

        try {
            // Count companies to be cleaned before running
            $cutoffTime = Carbon::now()->subHours($this->hoursThreshold);
            $count = User::where('type', 'company')
                ->whereNull('email_verified_at')
                ->where('created_at', '<', $cutoffTime)
                ->count();

            if ($count > 0) {
                Log::info("Found {$count} unverified companies for cleanup");
                
                // Run the cleanup command
                Artisan::call('cleanup:unverified-companies', [
                    '--hours' => $this->hoursThreshold
                ]);

                Log::info("Automated cleanup completed");
            } else {
                Log::info("No unverified companies found for cleanup");
            }

        } catch (\Exception $e) {
            Log::error("Automated cleanup failed: " . $e->getMessage());
            throw $e;
        }
    }

    public function failed(\Exception $exception)
    {
        Log::error("Cleanup job failed after all retries: " . $exception->getMessage());
    }
}