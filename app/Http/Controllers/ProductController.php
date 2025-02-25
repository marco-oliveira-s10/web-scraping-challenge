<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    /**
     * Display a listing of products for public users.
     */
    public function index(Request $request)
    {
        $category = $request->get('category');
        
        // Using cache for better performance
        $cacheKey = 'products_' . ($category ?? 'all') . '_page_' . $request->get('page', 1);
        
        $products = Cache::remember($cacheKey, 600, function () use ($category) {
            $query = Product::query()->orderBy('name');
            
            if ($category && $category !== 'all') {
                $query->where('category', $category);
            }
            
            return $query->paginate(12);
        });
        
        $categories = Cache::remember('product_categories', 3600, function () {
            return Product::select('category')
                ->distinct()
                ->whereNotNull('category')
                ->pluck('category');
        });
        
        return view('products.index', compact('products', 'categories', 'category'));
    }

    /**
     * Display the specified product.
     */
    public function show($id)
    {
        $product = Cache::remember('product_' . $id, 600, function () use ($id) {
            return Product::findOrFail($id);
        });
        
        return view('products.show', compact('product'));
    }

    /**
     * Display products by category.
     */
    public function byCategory($category)
    {
        $products = Cache::remember('products_category_' . $category, 600, function () use ($category) {
            return Product::where('category', $category)->paginate(12);
        });
        
        $categories = Cache::remember('product_categories', 3600, function () {
            return Product::select('category')
                ->distinct()
                ->whereNotNull('category')
                ->pluck('category');
        });
        
        return view('products.index', compact('products', 'categories', 'category'));
    }
}