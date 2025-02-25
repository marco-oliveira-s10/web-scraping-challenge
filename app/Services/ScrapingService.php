<?php

namespace App\Services;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ScrapingService
{
    protected $client;
    protected $url;

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
     * Perform scraping operation with enhanced error handling
     * 
     * @return bool
     */
    public function scrapeProducts()
    {
        try {
            Log::info('Starting product scraping process');
            
            $response = $this->client->get($this->url);
            $html = (string) $response->getBody();
            
            $crawler = new Crawler($html);
            
            // Find all product blocks on the page
            $products = $crawler->filter('.thumbnail');
            
            $scrapedCount = 0;
            
            foreach ($products as $productNode) {
                try {
                    $product = new Crawler($productNode);
                    
                    // Extract product information
                    $name = $product->filter('a.title')->text();
                    $priceText = $product->filter('h4.price')->text();
                    $price = (float) str_replace('$', '', $priceText);
                    
                    // Extract description (intermediate level requirement)
                    $description = $product->filter('p.description')->count() > 0 
                        ? $product->filter('p.description')->text() 
                        : null;
                    
                    // Extract image URL (intermediate level requirement)
                    $imageUrl = null;
                    if ($product->filter('img')->count() > 0) {
                        $imgSrc = $product->filter('img')->attr('src');
                        // Converter URLs relativas em absolutas
                        if ($imgSrc && !str_starts_with($imgSrc, 'http')) {
                            $imageUrl = 'https://webscraper.io' . $imgSrc;
                        } else {
                            $imageUrl = $imgSrc;
                        }
                    }
                    
                    // Extract category (intermediate level requirement)
                    // On this test site, we can determine category from the product's location in the DOM
                    $category = $this->determineCategory($product);
                    
                    // Generate a unique ID for the product based on the name
                    $productId = Str::slug($name);
                    
                    // Store or update the product in the database
                    Product::updateOrCreate(
                        ['product_id' => $productId],
                        [
                            'name' => $name,
                            'price' => $price,
                            'description' => $description,
                            'image_url' => $imageUrl,
                            'category' => $category
                        ]
                    );
                    
                    $scrapedCount++;
                } catch (\Exception $e) {
                    // Log error for individual product but continue processing others
                    Log::warning('Error processing product: ' . $e->getMessage());
                }
            }
            
            Log::info("Scraping completed successfully. Processed {$scrapedCount} products.");
            return true;
        } catch (\Exception $e) {
            Log::error('Error during scraping process: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
    
    /**
     * Scrape products by specific category
     * 
     * @param string $category
     * @return int Number of products scraped
     */
    public function scrapeProductsByCategory(string $category)
    {
        try {
            Log::info("Starting product scraping for category: {$category}");
            
            // Para este site, precisamos encontrar a URL correta da categoria
            $categoryUrl = $this->getCategoryUrl($category);
            
            if (!$categoryUrl) {
                Log::warning("Could not determine URL for category: {$category}");
                return 0;
            }
            
            $response = $this->client->get($categoryUrl);
            $html = (string) $response->getBody();
            
            $crawler = new Crawler($html);
            
            // Find all product blocks on the page
            $products = $crawler->filter('.thumbnail');
            
            $scrapedCount = 0;
            
            foreach ($products as $productNode) {
                try {
                    $product = new Crawler($productNode);
                    
                    // Verificar se temos um título (alguns elementos .thumbnail podem não ser produtos)
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
                        // Converter URLs relativas em absolutas
                        if ($imgSrc && !str_starts_with($imgSrc, 'http')) {
                            $imageUrl = 'https://webscraper.io' . $imgSrc;
                        } else {
                            $imageUrl = $imgSrc;
                        }
                    }
                    
                    // Generate a unique ID for the product based on the name
                    $productId = Str::slug($name);
                    
                    // Store or update the product in the database
                    Product::updateOrCreate(
                        ['product_id' => $productId],
                        [
                            'name' => $name,
                            'price' => $price,
                            'description' => $description,
                            'image_url' => $imageUrl,
                            'category' => $category
                        ]
                    );
                    
                    $scrapedCount++;
                } catch (\Exception $e) {
                    // Log error for individual product but continue processing others
                    Log::warning('Error processing product: ' . $e->getMessage());
                }
            }
            
            Log::info("Category scraping completed successfully. Processed {$scrapedCount} products in category {$category}.");
            return $scrapedCount;
        } catch (\Exception $e) {
            Log::error("Error during category scraping process: {$e->getMessage()}", [
                'category' => $category,
                'exception' => $e,
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
        // Mapeamento de categorias para URLs (específico para o site webscraper.io)
        $categoryMap = [
            'Phones' => '/test-sites/e-commerce/allinone/phones',
            'Laptops' => '/test-sites/e-commerce/allinone/computers/laptops',
            'Tablets' => '/test-sites/e-commerce/allinone/computers/tablets',
            'Monitors' => '/test-sites/e-commerce/allinone/computers/monitors',
            'Computers' => '/test-sites/e-commerce/allinone/computers'
        ];
        
        // Verificar se a categoria existe no mapeamento
        if (isset($categoryMap[$category])) {
            return 'https://webscraper.io' . $categoryMap[$category];
        }
        
        // Tentar uma abordagem genérica se não encontrarmos no mapeamento
        return 'https://webscraper.io/test-sites/e-commerce/allinone/' . Str::slug($category);
    }
    
    /**
     * Get all available categories from the website
     * 
     * @return array
     */
    public function getAvailableCategories()
    {
        try {
            $response = $this->client->get($this->url);
            $html = (string) $response->getBody();
            
            $crawler = new Crawler($html);
            
            // On this site, categories are in the sidebar
            $categories = [];
            
            // Utilizando um seletor mais específico para capturar as categorias
            $categoryLinks = $crawler->filter('.sidebar-categories .sidebar-submenu a');
            
            foreach ($categoryLinks as $link) {
                $linkCrawler = new Crawler($link);
                $categoryName = trim($linkCrawler->text());
                
                // Skip parent categories and empty ones
                if (!empty($categoryName) && !Str::contains($categoryName, ['Home', 'All products'])) {
                    $categories[] = $categoryName;
                }
            }
            
            // Adicionar categorias principais que sabemos que existem
            $mainCategories = ['Computers', 'Phones'];
            foreach ($mainCategories as $category) {
                if (!in_array($category, $categories)) {
                    $categories[] = $category;
                }
            }
            
            return array_unique($categories);
        } catch (\Exception $e) {
            Log::error('Error fetching categories: ' . $e->getMessage());
            
            // Retornar categorias conhecidas como fallback
            return ['Computers', 'Phones', 'Laptops', 'Tablets', 'Monitors'];
        }
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
        // This is a simplified approach - in a real-world example, you might need to use
        // more sophisticated techniques
        
        try {
            // Look for any category information in the HTML structure
            $productHtml = $product->outerHtml();
            
            // Verificar o título do produto para ajudar a identificar a categoria
            $name = $product->filter('a.title')->text();
            
            // Lógica específica para este site de demonstração
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
            
            // Tentar encontrar no preço ou na descrição
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
            return 'Computers'; // Changed from "Computadores" to "Computers" for consistency
        } catch (\Exception $e) {
            Log::warning('Error determining category: ' . $e->getMessage());
            return 'Uncategorized';
        }
    }
}