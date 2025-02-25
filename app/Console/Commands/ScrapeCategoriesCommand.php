<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ScrapingService;
use App\Jobs\ScrapeProductsJob;
use Illuminate\Support\Facades\Log;

class ScrapeCategoriesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scraper:categories';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape products by each category separately';

    /**
     * Execute the console command.
     */
    public function handle(ScrapingService $scrapingService)
    {
        try {
            $this->info('Starting to scrape products by categories...');
            Log::info('Starting category scraping command');

            // Get all available categories
            $categories = $scrapingService->getAvailableCategories();
            
            if (empty($categories)) {
                $this->error('No categories found to scrape');
                Log::warning('No categories found for scraping in command');
                return 1;
            }
            
            $this->info('Found ' . count($categories) . ' categories');
            Log::info('Found categories to scrape', ['count' => count($categories)]);
            
            // Create a progress bar
            $bar = $this->output->createProgressBar(count($categories));
            $bar->start();
            
            // Queue a job for each category
            foreach ($categories as $category) {
                $this->info("Dispatching job for category: {$category}");
                
                // Use delay to spread out the load (1 minute between each category)
                ScrapeProductsJob::dispatch($category)->delay(now()->addMinutes($bar->getProgress()));
                
                $bar->advance();
            }
            
            $bar->finish();
            $this->newLine();
            $this->info('All category scraping jobs have been queued successfully');
            Log::info('Category scraping command completed', ['categories_queued' => count($categories)]);
            
            return 0;
        } catch (\Exception $e) {
            $this->error('Error dispatching category scraping jobs: ' . $e->getMessage());
            Log::error('Error in category scraping command: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            return 1;
        }
    }
}