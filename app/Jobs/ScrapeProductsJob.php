<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\ScrapingService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ScrapingCompletedNotification;
use App\Notifications\ScrapingFailedNotification;
use App\Models\AdminUser;

class ScrapeProductsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 60;

    /**
     * The category to scrape, null means all categories
     *
     * @var string|null
     */
    protected $category;

    /**
     * The source for scraping (sync or async)
     *
     * @var string
     */
    protected $source;

    /**
     * Create a new job instance.
     */
    public function __construct(string $category = null, string $source = 'async')
    {
        $this->category = $category;
        $this->source = $source;
    }

    /**
     * Execute the job.
     */
    public function handle(ScrapingService $scrapingService): void
    {
        try {
            Log::info('Starting product scraping job', [
                'category' => $this->category ?? 'all',
                'source' => $this->source,
                'job_id' => $this->job->getJobId()
            ]);

            // Definir o status de execução
            Cache::put("task_last_run_product:fetch", now());
            
            // Atualizar status para em progresso
            Cache::put("task_status_product:fetch", 'running');

            // Perform the scraping
            $result = $this->performScraping($scrapingService);

            // Atualizar status de conclusão
            Cache::put("task_status_product:fetch", $result['success'] ? 'completed' : 'failed');

            // Log e notificação
            $this->handleScrapingResult($result);

        } catch (\Exception $e) {
            // Em caso de exceção, marcar como falha
            Cache::put("task_status_product:fetch", 'failed');

            Log::error('Error in scraping job: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
                'job_id' => $this->job->getJobId(),
                'category' => $this->category ?? 'all'
            ]);
            
            // Notificar sobre falha crítica
            $this->notifyAdmins(false, 'An error occurred during the scraping process: ' . $e->getMessage());
            
            // Re-throw para acionar o método failed()
            throw $e;
        }
    }

    /**
     * Realizar o scraping com base na fonte
     */
    protected function performScraping(ScrapingService $scrapingService): array
    {
        try {
            // Verificar a fonte do scraping
            if ($this->source === 'sync') {
                // Se for síncrono, usar método equivalente ao botão de scraping
                $success = $scrapingService->syncScrapeProducts($this->category);
                $message = $success 
                    ? "Successfully scraped products" 
                    : "Failed to scrape products";
            } else {
                // Modo assíncrono padrão
                if ($this->category) {
                    $count = $scrapingService->scrapeProductsByCategory($this->category);
                    $success = ($count > 0);
                    $message = $success 
                        ? "Successfully scraped {$count} products in category: {$this->category}" 
                        : "Failed to scrape products in category: {$this->category}";
                } else {
                    $success = $scrapingService->scrapeProducts();
                    $message = $success 
                        ? "Successfully scraped products from all categories" 
                        : "Failed to scrape products";
                }
            }

            return [
                'success' => $success,
                'message' => $message
            ];
        } catch (\Exception $e) {
            Log::error('Scraping failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Processar resultado do scraping
     */
    protected function handleScrapingResult(array $result): void
    {
        if ($result['success']) {
            Log::info($result['message'], [
                'job_id' => $this->job->getJobId(),
                'category' => $this->category ?? 'all'
            ]);
            
            // Enviar notificação de sucesso para admins
            $this->notifyAdmins(true, $result['message']);
        } else {
            Log::warning($result['message'], [
                'job_id' => $this->job->getJobId(),
                'category' => $this->category ?? 'all'
            ]);
            
            // Enviar notificação de falha para admins
            $this->notifyAdmins(false, $result['message']);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Scraping job failed after all attempts', [
            'exception' => $exception->getMessage(),
            'job_id' => $this->job->getJobId(),
            'category' => $this->category ?? 'all'
        ]);
        
        // Atualizar status final de falha
        Cache::put("task_status_product:fetch", 'failed');
        
        // Enviar notificação sobre falha crítica
        $this->notifyAdmins(false, 'Critical failure: Scraping job failed after all attempts', true);
    }
    
    /**
     * Send notifications to admin users
     */
    private function notifyAdmins(bool $success, string $message, bool $critical = false): void
    {
        try {
            if ($success) {
                Log::info('Would send success notification to admins: ' . $message);
                // Uncomment in a real application with AdminUser model
                // Notification::send(AdminUser::all(), new ScrapingCompletedNotification($message));
            } else {
                Log::error($critical ? 'Would send critical notification to admins: ' : 'Would send failure notification to admins: ' . $message);
                // Uncomment in a real application with AdminUser model
                // Notification::send(AdminUser::all(), new ScrapingFailedNotification($message, $critical));
            }
        } catch (\Exception $e) {
            Log::error('Failed to send admin notifications: ' . $e->getMessage());
        }
    }
}