<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Services\ScrapingService;

class ProductController extends Controller
{
    protected $scrapingService;

    public function __construct(ScrapingService $scrapingService)
    {
        $this->scrapingService = $scrapingService;
    }

    /**
     * Exibe a lista de produtos
     */
    public function index()
    {
        $products = Product::orderBy('name')->get();
        
        return view('products.index', compact('products'));
    }

    /**
     * Realiza o scraping e atualiza o banco de dados
     */
    public function scrape()
    {
        $success = $this->scrapingService->basicScraping();
        
        if ($success) {
            return redirect()->route('products.index')
                ->with('success', 'Produtos coletados com sucesso!');
        } else {
            return redirect()->route('products.index')
                ->with('error', 'Erro ao coletar produtos.');
        }
    }
}