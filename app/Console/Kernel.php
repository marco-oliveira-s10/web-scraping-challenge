<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\ScrapeProductsJob;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Schedule the scraping job to run every day at midnight
        $schedule->job(new ScrapeProductsJob)->dailyAt('00:00');
        
        // Schedule category-specific scraping jobs to run at different times
        $schedule->command('scraper:categories')->dailyAt('02:00');
        
        // Cleanup old logs and failed jobs once a week
        $schedule->command('scraper:cleanup')->weekly();
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