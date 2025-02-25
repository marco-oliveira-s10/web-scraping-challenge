<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Home page redirects to products index
Route::get('/', function () {
    return redirect()->route('products.index');
});

// Products routes
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/scrape', [ProductController::class, 'scrape'])->name('products.scrape');
Route::get('/products/categories', [ProductController::class, 'categories'])->name('products.categories');
Route::get('/products/{id}', [ProductController::class, 'show'])->name('products.show');