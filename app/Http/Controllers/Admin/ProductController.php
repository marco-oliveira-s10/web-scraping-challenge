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

    /**
     * Constructor with dependency injection
     */
    public function __construct(ScrapingService $scrapingService)
    {
        $this->scrapingService = $scrapingService;
    }

    /**
     * Display a listing of the products with optional category filtering and pagination
     */
    public function index(Request $request)
    {
        try {
            // Get all available categories for the filter dropdown
            $categories = Product::distinct('category')->pluck('category')->filter()->values();
            
            // Build the query with optional category filter
            $query = Product::query();
            
            if ($request->has('category') && $request->category !== 'all') {
                $query->where('category', $request->category);
            }
            
            // Get the products with pagination (10 per page)
            $products = $query->orderBy('name')->paginate(9);
            
            // Get the currently selected category for the view
            $selectedCategory = $request->category ?? 'all';
            
            return view('admin.products.index', compact('products', 'categories', 'selectedCategory'));
        } catch (\Exception $e) {
            Log::error('Error in product index: ' . $e->getMessage());
            return view('admin.products.index')
                ->with('error', 'An error occurred while fetching products.')
                ->with('products', collect());
        }
    }

    /**
     * Run the scraping process and redirect back with a status message
     */
    public function scrape(Request $request)
    {
        try {
            // Check if we're scraping a specific category
            if ($request->has('category') && $request->category !== 'all') {
                $count = $this->scrapingService->scrapeProductsByCategory($request->category);
                $success = ($count > 0);
                $message = $success 
                    ? "Coletados com sucesso {$count} produtos na categoria: {$request->category}" 
                    : "Falha ao coletar produtos na categoria: {$request->category}";
            } else {
                // Scrape all products
                $success = $this->scrapingService->scrapeProducts();
                $message = $success 
                    ? "Produtos coletados com sucesso de todas as categorias" 
                    : "Falha ao coletar produtos";
            }
            
            Log::info($message);
            
            if ($success) {
                return redirect()->route('admin.products.index')
                    ->with('success', $message);
            } else {
                return redirect()->route('admin.products.index')
                    ->with('error', $message);
            }
        } catch (\Exception $e) {
            Log::error('Error in scrape action: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('admin.products.index')
                ->with('error', 'Ocorreu um erro inesperado durante o processo de coleta.');
        }
    }
    
    /**
     * Show available categories that can be scraped from the website
     */
    public function categories()
    {
        try {
            $availableCategories = $this->scrapingService->getAvailableCategories();
            
            return view('admin.products.categories', compact('availableCategories'));
        } catch (\Exception $e) {
            Log::error('Error fetching available categories: ' . $e->getMessage());
            
            return redirect()->route('admin.products.index')
                ->with('error', 'Failed to fetch available categories from the website.');
        }
    }
    
    /**
     * Display a single product's detailed view
     */
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
    
    /**
     * Show the form for editing the specified product.
     */
    public function edit($id)
    {
        try {
            $product = Product::findOrFail($id);
            $categories = Product::distinct('category')->pluck('category');
            
            return view('admin.products.edit', compact('product', 'categories'));
        } catch (\Exception $e) {
            Log::error('Error in product edit: ' . $e->getMessage());
            
            return redirect()->route('admin.products.index')
                ->with('error', 'Product not found or cannot be edited.');
        }
    }
    
    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'description' => 'nullable|string',
                'image_url' => 'nullable|url',
                'category' => 'nullable|string|max:100',
            ]);
            
            $product = Product::findOrFail($id);
            $product->update($validated);
            
            return redirect()->route('admin.products.show', $id)
                ->with('success', 'Product updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating product: ' . $e->getMessage());
            
            return back()->withInput()
                ->with('error', 'Failed to update the product. Please try again.');
        }
    }
    
    /**
     * Remove the specified product from storage.
     */
    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);
            $product->delete();
            
            return redirect()->route('admin.products.index')
                ->with('success', 'Product deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting product: ' . $e->getMessage());
            
            return back()
                ->with('error', 'Failed to delete the product. Please try again.');
        }
    }
}