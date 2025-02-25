<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

// Rotas públicas de produtos
Route::prefix('/')->name('products.')->group(function () {

    Route::get('/', [ProductController::class, 'index'])->name('index');
    Route::get('/{id}', [ProductController::class, 'show'])->name('show');
    Route::get('/categories', [ProductController::class, 'listCategories'])->name('categories');
});

// Rota para listar produtos por categoria
Route::get('/categories/{category}', [ProductController::class, 'byCategory'])->name('products.category');