<?php

namespace App\Services;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ScrapingService
{
    protected $client;
    protected $url;
    
    // Cache keys
    const CACHE_KEY_CATEGORIES = 'scraping_categories';
    const CACHE_DURATION_CATEGORIES = 3600; // 1 hour
    
    const CACHE_KEY_PRODUCTS = 'scraping_products_';
    const CACHE_DURATION_PRODUCTS = 1800; // 30 minutes

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 30,
            'connect_timeout' => 10,
            'verify' => false // Only for testing purposes
        ]);
        
        // URL of the e-commerce demo site
        $this->url = 'https://webscraper.io/test-sites/e-commerce/allinone';
    }

    /**
     * Perform scraping operation with enhanced error handling and caching
     * 
     * @return bool
     */
    public function scrapeProducts()
{
    try {
        Log::info('Starting comprehensive product scraping process');
        
        // Obter categorias disponíveis
        $categories = $this->getAvailableCategories();
        
        $totalScrapedCount = 0;
        $categoryResults = [];
        
        // Iterar sobre cada categoria
        foreach ($categories as $category) {
            Log::info("Scraping category: {$category}");
            
            $scrapedCount = $this->scrapeProductsByCategory($category);
            
            $categoryResults[$category] = $scrapedCount;
            $totalScrapedCount += $scrapedCount;
            
            Log::info("Scraped {$scrapedCount} products in category: {$category}");
        }
        
        $logMessage = "Comprehensive scraping completed. Total products scraped: {$totalScrapedCount}";
        Log::info($logMessage);
        $this->logScrapingEvent('success', null, $logMessage, [
            'total_products' => $totalScrapedCount,
            'category_breakdown' => $categoryResults
        ]);
        
        return $totalScrapedCount > 0;
    } catch (\Exception $e) {
        Log::error('Error during comprehensive scraping process: ' . $e->getMessage(), [
            'exception' => $e,
            'trace' => $e->getTraceAsString()
        ]);
        
        $this->logScrapingEvent('error', null, 'Error during comprehensive scraping process: ' . $e->getMessage(), [
            'exception' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return false;
    }
}
    
    /**
     * Scrape products by specific category with caching and optimization
     * 
     * @param string $category
     * @return int Number of products scraped
     */
    public function scrapeProductsByCategory(string $category)
    {
        try {
            Log::info("Starting product scraping for category: {$category}");
            $this->logScrapingEvent('info', $category, "Starting product scraping for category: {$category}");
            
            // Get the last scrape time to compare product timestamps
            $lastScrapeTime = $this->getLastScrapeTime($category);
            
            // For this site, we need to find the correct category URL
            $categoryUrl = $this->getCategoryUrl($category);
            
            if (!$categoryUrl) {
                Log::warning("Could not determine URL for category: {$category}");
                $this->logScrapingEvent('warning', $category, "Could not determine URL for category: {$category}");
                return 0;
            }
            
            $response = $this->client->get($categoryUrl);
            $html = (string) $response->getBody();
            
            $crawler = new Crawler($html);
            
            // Find all product blocks on the page
            $products = $crawler->filter('.thumbnail');
            
            $scrapedCount = 0;
            $updatedCount = 0;
            $newCount = 0;
            $unchangedCount = 0;
            
            // Begin a database transaction
            DB::beginTransaction();
            
            foreach ($products as $productNode) {
                try {
                    $product = new Crawler($productNode);
                    
                    // Verify if we have a title (some .thumbnail elements might not be products)
                    if ($product->filter('a.title')->count() == 0) {
                        continue;
                    }
                    
                    // Extract product information
                    $name = $product->filter('a.title')->text();
                    $priceText = $product->filter('h4.price')->text();
                    $price = (float) str_replace('$', '', $priceText);
                    
                    // Extract description
                    $description = $product->filter('p.description')->count() > 0 
                        ? $product->filter('p.description')->text() 
                        : null;
                    
                    // Extract image URL
                    $imageUrl = null;
                    if ($product->filter('img')->count() > 0) {
                        $imgSrc = $product->filter('img')->attr('src');
                        // Convert relative URLs to absolute
                        if ($imgSrc && !str_starts_with($imgSrc, 'http')) {
                            $imageUrl = 'https://webscraper.io' . $imgSrc;
                        } else {
                            $imageUrl = $imgSrc;
                        }
                    }
                    
                    // Generate a unique ID for the product based on the name
                    $productId = Str::slug($name);
                    
                    // Check if the product already exists
                    $existingProduct = Product::where('product_id', $productId)->first();
                    
                    if ($existingProduct) {
                        // Create a checksum of the current data to compare with the new data
                        $existingChecksum = md5(
                            $existingProduct->name . 
                            $existingProduct->price . 
                            $existingProduct->description . 
                            $existingProduct->image_url . 
                            $existingProduct->category
                        );
                        
                        $newChecksum = md5(
                            $name . 
                            $price . 
                            $description . 
                            $imageUrl . 
                            $category
                        );
                        
                        // Only update if there are actual changes
                        if ($existingChecksum !== $newChecksum) {
                            $existingProduct->update([
                                'name' => $name,
                                'price' => $price,
                                'description' => $description,
                                'image_url' => $imageUrl,
                                'category' => $category
                            ]);
                            $updatedCount++;
                        } else {
                            $unchangedCount++;
                        }
                    } else {
                        // Create a new product
                        Product::create([
                            'product_id' => $productId,
                            'name' => $name,
                            'price' => $price,
                            'description' => $description,
                            'image_url' => $imageUrl,
                            'category' => $category
                        ]);
                        $newCount++;
                    }
                    
                    $scrapedCount++;
                } catch (\Exception $e) {
                    // Log error for individual product but continue processing others
                    Log::warning('Error processing product: ' . $e->getMessage());
                    $this->logScrapingEvent('warning', $category, 'Error processing product: ' . $e->getMessage());
                }
            }
            
            // Commit the transaction
            DB::commit();
            
            // Clear the category-specific product cache
            $this->clearProductCache($category);
            
            // Update the last scrape time for this category
            $this->updateLastScrapeTime($category);
            
            $logMessage = "Category scraping completed successfully. Processed {$scrapedCount} products in category {$category}: {$newCount} new, {$updatedCount} updated, {$unchangedCount} unchanged.";
            Log::info($logMessage);
            $this->logScrapingEvent('success', $category, $logMessage);
            
            return $scrapedCount;
        } catch (\Exception $e) {
            // Rollback the transaction in case of error
            DB::rollBack();
            
            Log::error("Error during category scraping process: {$e->getMessage()}", [
                'category' => $category,
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->logScrapingEvent('error', $category, "Error during category scraping process: {$e->getMessage()}", [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return 0;
        }
    }
    
    /**
     * Get the correct URL for a category
     * 
     * @param string $category
     * @return string|null
     */
    private function getCategoryUrl(string $category)
    {
        // Category to URL mapping (specific to the webscraper.io site)
        $categoryMap = [
            'Phones' => '/test-sites/e-commerce/allinone/phones',
            'Laptops' => '/test-sites/e-commerce/allinone/computers/laptops',
            'Tablets' => '/test-sites/e-commerce/allinone/computers/tablets',
            'Monitors' => '/test-sites/e-commerce/allinone/computers/monitors',
            'Computers' => '/test-sites/e-commerce/allinone/computers'
        ];
        
        // Check if the category exists in the mapping
        if (isset($categoryMap[$category])) {
            return 'https://webscraper.io' . $categoryMap[$category];
        }
        
        // Try a generic approach if we don't find it in the mapping
        return 'https://webscraper.io/test-sites/e-commerce/allinone/' . Str::slug($category);
    }
    
    /**
     * Get all available categories from the website with caching
     * 
     * @return array
     */
    public function getAvailableCategories()
    {
        // Try to get categories from cache first
        return Cache::remember(self::CACHE_KEY_CATEGORIES, self::CACHE_DURATION_CATEGORIES, function () {
            try {
                $response = $this->client->get($this->url);
                $html = (string) $response->getBody();
                
                $crawler = new Crawler($html);
                
                // On this site, categories are in the sidebar
                $categories = [];
                
                // Using a more specific selector to capture categories
                $categoryLinks = $crawler->filter('.sidebar-categories .sidebar-submenu a');
                
                foreach ($categoryLinks as $link) {
                    $linkCrawler = new Crawler($link);
                    $categoryName = trim($linkCrawler->text());
                    
                    // Skip parent categories and empty ones
                    if (!empty($categoryName) && !Str::contains($categoryName, ['Home', 'All products'])) {
                        $categories[] = $categoryName;
                    }
                }
                
                // Add main categories that we know exist
                $mainCategories = ['Computers', 'Phones'];
                foreach ($mainCategories as $category) {
                    if (!in_array($category, $categories)) {
                        $categories[] = $category;
                    }
                }
                
                Log::info('Retrieved categories from website', ['count' => count($categories)]);
                $this->logScrapingEvent('info', null, 'Retrieved categories from website', ['count' => count($categories)]);
                
                return array_unique($categories);
            } catch (\Exception $e) {
                Log::error('Error fetching categories: ' . $e->getMessage());
                $this->logScrapingEvent('error', null, 'Error fetching categories: ' . $e->getMessage());
                
                // Return known categories as fallback
                return ['Computers', 'Phones', 'Laptops', 'Tablets', 'Monitors'];
            }
        });
    }
    
    /**
     * Determine category from the product's DOM structure
     * 
     * @param Crawler $product
     * @return string|null
     */
    private function determineCategory(Crawler $product)
    {
        // For this demo site, we'll try to extract category from breadcrumbs or product classes
        try {
            // Look for any category information in the HTML structure
            $productHtml = $product->outerHtml();
            
            // Check the product title to help identify the category
            $name = $product->filter('a.title')->text();
            
            // Logic specific to this demo site
            if (Str::contains($productHtml, 'phones') || Str::contains($name, 'phone') || Str::contains($name, 'iPhone') || Str::contains($name, 'Galaxy')) {
                return 'Phones';
            } 
            
            if (Str::contains($productHtml, 'laptops') || Str::contains($name, 'laptop') || Str::contains($name, 'Notebook')) {
                return 'Laptops';
            } 
            
            if (Str::contains($productHtml, 'monitors') || Str::contains($name, 'monitor') || Str::contains($name, 'Display')) {
                return 'Monitors';
            } 
            
            if (Str::contains($productHtml, 'tablets') || Str::contains($name, 'tablet') || Str::contains($name, 'iPad')) {
                return 'Tablets';
            }
            
            // Try to find in price or description
            $priceText = $product->filter('h4.price')->text();
            $description = $product->filter('p.description')->count() > 0 
                ? $product->filter('p.description')->text() 
                : '';
                
            if (Str::contains($description, 'phone') || Str::contains($description, 'smartphone')) {
                return 'Phones';
            }
            
            if (Str::contains($description, 'laptop') || Str::contains($description, 'notebook')) {
                return 'Laptops';
            }
            
            if (Str::contains($description, 'monitor') || Str::contains($description, 'display')) {
                return 'Monitors';
            }
            
            if (Str::contains($description, 'tablet')) {
                return 'Tablets';
            }
            
            // If we can't determine category from classes, use a default
            return 'Computers';
        } catch (\Exception $e) {
            Log::warning('Error determining category: ' . $e->getMessage());
            return 'Uncategorized';
        }
    }
    
    /**
     * Get products with caching
     * 
     * @param string|null $category
     * @param int $page
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getProducts(?string $category = null, int $page = 1, int $perPage = 9)
    {
        $cacheKey = self::CACHE_KEY_PRODUCTS . ($category ?? 'all') . '_' . $page . '_' . $perPage;
        
        return Cache::remember($cacheKey, self::CACHE_DURATION_PRODUCTS, function () use ($category, $page, $perPage) {
            $query = Product::query();
            
            if ($category && $category !== 'all') {
                $query->where('category', $category);
            }
            
            return $query->orderBy('name')->paginate($perPage);
        });
    }
    
    /**
     * Clear the product cache
     * 
     * @param string|null $category
     */
    private function clearProductCache(?string $category = null)
    {
        if ($category) {
            Cache::forget(self::CACHE_KEY_PRODUCTS . $category . '_*');
        } else {
            // Clear all product caches
            $cacheKeys = Cache::get('product_cache_keys', []);
            foreach ($cacheKeys as $key) {
                Cache::forget($key);
            }
            Cache::forget('product_cache_keys');
        }
    }
    
    /**
     * Get the timestamp of the last scrape
     * 
     * @param string|null $category
     * @return \Carbon\Carbon|null
     */
    private function getLastScrapeTime(?string $category = null)
    {
        $cacheKey = 'last_scrape_time' . ($category ? '_' . $category : '');
        
        $lastScrapeTime = Cache::get($cacheKey);
        
        if ($lastScrapeTime) {
            return Carbon::parse($lastScrapeTime);
        }
        
        return null;
    }
    
    /**
     * Update the timestamp of the last scrape
     * 
     * @param string|null $category
     */
    private function updateLastScrapeTime(?string $category = null)
    {
        $cacheKey = 'last_scrape_time' . ($category ? '_' . $category : '');
        
        Cache::forever($cacheKey, now()->toDateTimeString());
    }
    
    /**
     * Log a scraping event to the database
     * 
     * @param string $type
     * @param string|null $category
     * @param string $message
     * @param array $context
     */
    private function logScrapingEvent(string $type, ?string $category, string $message, array $context = [])
    {
        try {
            if (class_exists('App\\Models\\ScrapingLog')) {
                \App\Models\ScrapingLog::create([
                    'type' => $type,
                    'category' => $category,
                    'message' => $message,
                    'context' => $context ? json_encode($context) : null,
                    'occurred_at' => now()
                ]);
            }
        } catch (\Exception $e) {
            // Just log to the standard log if we can't save to the database
            Log::error('Failed to save scraping log to database: ' . $e->getMessage());
        }
    }
}