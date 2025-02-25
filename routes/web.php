<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\TaskController;

// Rotas de autenticação para Admin
Route::prefix('admin')->name('admin.')->group(function () {
    // Rotas de autenticação
    Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    
    // Rotas protegidas por autenticação de admin
    Route::middleware(['auth:admin'])->group(function () {
        // Logout
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        
        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        
        // Tasks
// Tasks
Route::prefix('tasks')->name('tasks.')->group(function () {
    Route::get('/', [TaskController::class, 'index'])->name('index');
    Route::post('/run', [TaskController::class, 'run'])->name('run');
    Route::get('/status', [TaskController::class, 'status'])->name('status');
    Route::post('/{task}/toggle', [TaskController::class, 'toggle'])->name('toggle');
});
        // Produtos
        Route::prefix('/products')->name('products.')->group(function () {
            Route::get('/', [ProductController::class, 'index'])->name('index');
            Route::get('/{id}', [ProductController::class, 'show'])->name('show');
            Route::post('/scrape', [ProductController::class, 'scrape'])->name('scrape');
            Route::get('/categories', [ProductController::class, 'categories'])->name('categories');
            Route::delete('/{id}', [ProductController::class, 'destroy'])->name('destroy');
        });
        
        // Rotas do sistema
        Route::get('/logs', [DashboardController::class, 'logs'])->name('logs');
        Route::get('/tasks', [DashboardController::class, 'tasks'])->name('tasks');
        Route::get('/status', [DashboardController::class, 'status'])->name('status');
        
        // Ações do sistema
        Route::post('/retry-job', [DashboardController::class, 'retryJob'])->name('retry-job');
        Route::post('/clear-failed-jobs', [DashboardController::class, 'clearFailedJobs'])->name('clear-failed-jobs');
        Route::post('/clear-cache', [DashboardController::class, 'clearCache'])->name('clear-cache');
    });
});

// Rota de compatibilidade para login
Route::get('/login', function () {
    return redirect('/admin/login');
})->name('login');

// Carregue as rotas públicas
require __DIR__ . '/public.php';