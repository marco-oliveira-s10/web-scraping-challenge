<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\ScrapeProductsJob;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
{
    $schedule->call(function () {
       echo 'Scheduled scraping started';
        try {
            $productController = app()->make(\App\Http\Controllers\Admin\ProductController::class);
            $response = $productController->scrape(request());
            
            echo 'Scheduled scraping completed';
        } catch (\Exception $e) {
            echo 'Scheduled scraping failed';
        }
    })->everyMinute(); // Mudei para everyMinute para facilitar teste
}
}