<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\ScrapingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    protected $scrapingService;

    public function __construct(ScrapingService $scrapingService)
    {
        $this->scrapingService = $scrapingService;
    }

    public function index(Request $request)
    {
        try {
            $categories = Product::distinct('category')->pluck('category')->filter()->values();

            $query = Product::query();

            if ($request->has('category') && $request->category !== 'all') {
                $query->where('category', $request->category);
            }

            $products = $query->orderBy('name')->paginate(9);

            $selectedCategory = $request->category ?? 'all';

            return view('admin.products.index', compact('products', 'categories', 'selectedCategory'));
        } catch (\Exception $e) {
            Log::error('Error in product index: ' . $e->getMessage());
            return view('admin.products.index')
                ->with('error', 'An error occurred while fetching products.')
                ->with('products', collect());
        }
    }

    public function show($id)
    {
        try {
            $product = Product::findOrFail($id);
            return view('admin.products.show', compact('product'));
        } catch (\Exception $e) {
            Log::error('Error showing product: ' . $e->getMessage());

            return redirect()->route('admin.products.index')
                ->with('error', 'Product not found or cannot be displayed.');
        }
    }

    public function scrape(Request $request)
    {
        try {
            $category = $request->input('category', 'all');

            if ($category !== 'all') {
                $count = $this->scrapingService->scrapeProductsByCategory($category);
                $success = ($count > 0);
                $message = $success
                    ? "Successfully collected {$count} products in category: {$category}"
                    : "Failed to collect products in category: {$category}";
            } else {
                $success = $this->scrapingService->scrapeProducts();
                $message = $success
                    ? "Successfully collected products from all categories"
                    : "Failed to collect products";
            }

            Log::info($message);

            return redirect()->route('admin.products.index')
                ->with($success ? 'success' : 'error', $message);
        } catch (\Exception $e) {
            Log::error('Scraping error: ' . $e->getMessage());

            return redirect()->route('admin.products.index')
                ->with('error', 'An unexpected error occurred during collection.');
        }
    }

    public function categories()
    {
        try {
            $availableCategories = $this->scrapingService->getAvailableCategories();

            return view('admin.products.categories', compact('availableCategories'));
        } catch (\Exception $e) {
            Log::error('Error fetching categories: ' . $e->getMessage());

            return redirect()->route('admin.products.index')
                ->with('error', 'Failed to fetch available categories.');
        }
    }

    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);
            $product->delete();

            return redirect()->route('admin.products.index')
                ->with('success', 'Produto excluído com sucesso.');
        } catch (\Exception $e) {
            Log::error('Erro ao excluir produto: ' . $e->getMessage());

            return redirect()->route('admin.products.index')
                ->with('error', 'Não foi possível excluir o produto.');
        }
    }
}
