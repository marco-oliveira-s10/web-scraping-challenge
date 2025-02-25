<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\AuthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application.
|
*/

// Rota para login (também disponível como 'login' para compatibilidade)
Route::get('/login', function() {
    return redirect('/admin/login');
})->name('login');

// Rota para o dashboard sem autenticação (APENAS PARA TESTES)
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

// Rota para página inicial
Route::get('/', function() {
    return redirect('/dashboard-demo');
});

// Admin authentication routes
Route::get('/admin/login', [AuthController::class, 'loginForm'])->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'login'])->name('admin.login.post');
Route::post('/admin/logout', [AuthController::class, 'logout'])->name('admin.logout');

// Admin routes (autenticação desativada para teste)
Route::prefix('admin')->name('admin.')->group(function () {
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