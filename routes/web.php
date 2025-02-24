<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [ProductController::class, 'index'])->name('products.index');
Route::get('/produtos', [ProductController::class, 'index'])->name('products.index');
Route::get('/scrape', [ProductController::class, 'scrape'])->name('products.scrape');