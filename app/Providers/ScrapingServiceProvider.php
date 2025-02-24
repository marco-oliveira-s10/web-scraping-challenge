<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\ScrapingService;

class ScrapingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(ScrapingService::class, function ($app) {
            return new ScrapingService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}