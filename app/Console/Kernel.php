<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\ScrapeProductsJob;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Principal job de scraping - roda a cada 2 minutos
        $schedule->job(new ScrapeProductsJob)
            ->everyTwoMinutes()
            ->withoutOverlapping()
            ->onSuccess(function () {
                Cache::put('last_successful_scrape', now());
                Log::info('Scheduled scraping completed successfully');
            })
            ->onFailure(function () {
                Log::error('Scheduled scraping failed');
            });
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