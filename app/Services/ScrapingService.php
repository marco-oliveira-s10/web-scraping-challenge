<?php

namespace App\Services;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\Product;
use Illuminate\Support\Str;

class ScrapingService
{
    protected $client;
    protected $url;

    public function __construct()
    {
        $this->client = new Client();
        // URL de exemplo para scraping (site de demonstração)
        $this->url = 'https://webscraper.io/test-sites/e-commerce/allinone';
    }

    /**
     * Realiza o scraping básico (nível Júnior)
     */
    public function basicScraping()
    {
        try {
            $response = $this->client->get($this->url);
            $html = (string) $response->getBody();
            
            $crawler = new Crawler($html);
            
            // Encontrar os produtos na página
            $products = $crawler->filter('.thumbnail');
            
            foreach ($products as $productNode) {
                $product = new Crawler($productNode);
                
                // Extrair nome e preço (tarefa de nível Júnior)
                $name = $product->filter('a.title')->text();
                $priceText = $product->filter('h4.price')->text();
                $price = (float) str_replace('$', '', $priceText);
                
                // Gerar um ID único para o produto baseado no nome (usado no nível Sênior)
                $productId = Str::slug($name);
                
                // Salvar no banco de dados
                Product::updateOrCreate(
                    ['product_id' => $productId],
                    [
                        'name' => $name,
                        'price' => $price
                    ]
                );
            }
            
            return true;
        } catch (\Exception $e) {
            // Log simples do erro
            \Log::error('Erro ao fazer scraping: ' . $e->getMessage());
            return false;
        }
    }
}