<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    /**
     * Listar produtos com opção de filtro por categoria
     */
    public function index(Request $request)
    {
        $category = $request->get('category');
        
        // Cache para melhor performance
        $cacheKey = 'products_' . ($category ?? 'all') . '_page_' . $request->get('page', 1);
        
        $products = Cache::remember($cacheKey, 600, function () use ($category) {
            $query = Product::query()->orderBy('name');
            
            if ($category && $category !== 'all') {
                $query->where('category', $category);
            }
            
            return $query->paginate(12);
        });

        // Cache para categorias
        $categories = Cache::remember('product_categories', 3600, function () {
            return Product::select('category')
                ->distinct()
                ->whereNotNull('category')
                ->pluck('category');
        });
                
        return view('products.index', compact('products', 'categories', 'category'));
    }

    /**
     * Mostrar detalhes de um produto
     */
    public function show($id)
    {
        $product = Cache::remember('product_' . $id, 600, function () use ($id) {
            return Product::findOrFail($id);
        });
        
        return view('products.show', compact('product'));
    }

    /**
     * Listar produtos por categoria
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

    /**
     * Listar todas as categorias
     */
    public function listCategories()
    {
        $categories = Cache::remember('product_categories', 3600, function () {
            return Product::select('category')
                ->distinct()
                ->whereNotNull('category')
                ->pluck('category');
        });
        
        return view('products.categories', compact('categories'));
    }
}