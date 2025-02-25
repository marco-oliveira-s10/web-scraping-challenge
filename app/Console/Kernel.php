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
        // Principal job de scraping - roda a cada 6 horas
        $schedule->job(new ScrapeProductsJob)
            ->everyFourHours()
            ->withoutOverlapping()
            ->onSuccess(function () {
                Cache::put('last_successful_scrape', now());
                Log::info('Scheduled scraping completed successfully');
            })
            ->onFailure(function () {
                Log::error('Scheduled scraping failed');
            });
        
        // Job de categorias específicas - roda duas vezes por dia
        $schedule->command('scraper:categories')
            ->twiceDaily(1, 13)
            ->withoutOverlapping()
            ->emailOutputOnFailure(['admin@example.com']);
        
        // Limpeza semanal
        $schedule->command('scraper:cleanup')
            ->weekly()
            ->sundays()
            ->at('00:00')
            ->withoutOverlapping();
            
        // Monitoramento de saúde do sistema - a cada 15 minutos
        $schedule->command('health:check')
            ->everyFifteenMinutes()
            ->withoutOverlapping();
            
        // Limpeza de cache antiga - diariamente
        $schedule->command('cache:prune-stale-tags')->daily();
        
        // Manutenção do banco de dados - semanalmente
        $schedule->command('db:clean')
            ->weekly()
            ->saturdays()
            ->at('02:00')
            ->withoutOverlapping();
            
        // Monitoramento do Redis
        $schedule->command('queue:monitor')
            ->everyTenMinutes()
            ->withoutOverlapping();
            
        // Backup do banco - diariamente
        $schedule->command('backup:run')
            ->dailyAt('01:00')
            ->onFailure(function () {
                Log::error('Database backup failed');
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