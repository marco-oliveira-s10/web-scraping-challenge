<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\ScrapingService;
use Illuminate\Support\Facades\Log;
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
     * Create a new job instance.
     */
    public function __construct(string $category = null)
    {
        $this->category = $category;
    }

    /**
     * Execute the job.
     */
    public function handle(ScrapingService $scrapingService): void
    {
        try {
            Log::info('Starting scheduled product scraping job', [
                'category' => $this->category ?? 'all',
                'job_id' => $this->job->getJobId()
            ]);

            // Perform the scraping
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

            // Log the result
            if ($success) {
                Log::info($message, [
                    'job_id' => $this->job->getJobId(),
                    'category' => $this->category ?? 'all'
                ]);
                
                // Send notification about success to admin users
                $this->notifyAdmins(true, $message);
            } else {
                Log::warning($message, [
                    'job_id' => $this->job->getJobId(),
                    'category' => $this->category ?? 'all'
                ]);
                
                // Send notification about failure to admin users
                $this->notifyAdmins(false, $message);
            }
        } catch (\Exception $e) {
            Log::error('Error in scheduled scraping job: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
                'job_id' => $this->job->getJobId(),
                'category' => $this->category ?? 'all'
            ]);
            
            // Send notification about exception to admin users
            $this->notifyAdmins(false, 'An error occurred during the scraping process: ' . $e->getMessage());
            
            // Re-throw the exception to trigger the job's failed method
            throw $e;
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
        
        // Send a notification about the critical failure
        $this->notifyAdmins(false, 'Critical failure: Scraping job failed after all attempts', true);
    }
    
    /**
     * Send notifications to admin users
     */
    private function notifyAdmins(bool $success, string $message, bool $critical = false): void
    {
        try {
            // In a real application, you would fetch admin users who should receive notifications
            // For this example, we're just logging since we don't have an admin user system yet
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