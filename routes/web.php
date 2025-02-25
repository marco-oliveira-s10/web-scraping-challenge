<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\AuthController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register admin routes for your application.
|
*/

// Admin authentication routes
Route::get('/admin/login', [AuthController::class, 'loginForm'])->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'login'])->name('admin.login.post');
Route::post('/admin/logout', [AuthController::class, 'logout'])->name('admin.logout');

// Admin routes (protected by auth middleware)
Route::middleware(['auth:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    // System monitoring
    Route::get('/logs', [DashboardController::class, 'logs'])->name('logs');
    Route::get('/tasks', [DashboardController::class, 'tasks'])->name('tasks');
    Route::get('/status', [DashboardController::class, 'status'])->name('status');
    
    // Actions
    Route::post('/run-scrape', [DashboardController::class, 'runScrape'])->name('run-scrape');
    Route::post('/retry-job', [DashboardController::class, 'retryJob'])->name('retry-job');
    Route::post('/clear-failed-jobs', [DashboardController::class, 'clearFailedJobs'])->name('clear-failed-jobs');
    Route::post('/clear-cache', [DashboardController::class, 'clearCache'])->name('clear-cache');
    
    // Product management
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/{id}', [ProductController::class, 'show'])->name('products.show');
    Route::get('/products/{id}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{id}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('products.destroy');
});