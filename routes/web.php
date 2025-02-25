<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Aqui é onde você pode registrar as rotas da web para sua aplicação.
| Estas rotas são carregadas pelo RouteServiceProvider dentro de um grupo
| que contém o middleware "web". Agora crie algo incrível!
|
*/

// Rota pública - Página inicial (frontend)
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Rotas para visualização de produtos para usuários regulares
Route::get('/products', [App\Http\Controllers\ProductController::class, 'index'])->name('products.index');
Route::get('/products/{id}', [App\Http\Controllers\ProductController::class, 'show'])->name('products.show');
Route::get('/categories/{category}', [App\Http\Controllers\ProductController::class, 'byCategory'])->name('products.category');

// Rota de compatibilidade para login
Route::get('/login', function() {
    return redirect('/admin/login');
})->name('login');

// Rotas de autenticação para Admin
Route::prefix('admin')->name('admin.')->group(function () {
    // Rotas públicas para área administrativa
    Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    
    // Rotas protegidas por autenticação admin
    Route::middleware(['auth:admin'])->group(function () {
        // Logout
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        
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
        
        // Executar scrape de uma categoria específica
        Route::post('/products/scrape-category', [ProductController::class, 'scrape'])->name('products.scrape');
        
        // Ver categorias disponíveis para scraping
        Route::get('/products/available-categories', [ProductController::class, 'categories'])->name('products.categories');
    });
});

// Rota para demonstração do dashboard (APENAS PARA TESTES - remover em produção)
Route::get('/dashboard-demo', function() {
    // Fornecer dados simulados para o dashboard
    $productCount = 120;
    $categoryCounts = [
        'Phones' => 45,
        'Laptops' => 35,
        'Tablets' => 25,
        'Monitors' => 15
    ];
    
    $recentLogs = collect([
        (object)[
            'type' => 'success',
            'category' => 'Phones',
            'message' => 'Successfully scraped 45 products',
            'formatted_occurred_at' => now()->subHours(2)->format('M d, Y H:i:s')
        ],
        (object)[
            'type' => 'info',
            'category' => 'All',
            'message' => 'Starting product scraping process',
            'formatted_occurred_at' => now()->subHours(3)->format('M d, Y H:i:s')
        ]
    ]);
    
    $logTypeCounts = [
        'success' => 45,
        'info' => 30,
        'warning' => 10,
        'error' => 5
    ];
    
    $failedJobs = 2;
    $pendingJobs = 1;
    $isScraperRunning = false;
    $lastScrapeTime = now()->subHours(12);
    
    return view('admin.dashboard', compact(
        'productCount',
        'categoryCounts',
        'recentLogs',
        'logTypeCounts',
        'failedJobs',
        'pendingJobs',
        'isScraperRunning',
        'lastScrapeTime'
    ));
})->name('dashboard.demo');