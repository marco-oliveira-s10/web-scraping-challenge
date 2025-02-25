<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ScrapingLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Cache;
use App\Jobs\ScrapeProductsJob;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display admin dashboard.
     */
    public function index()
    {
        // Count products
        $productCount = Product::count();
        $categoryCounts = Product::select('category', DB::raw('count(*) as count'))
            ->groupBy('category')
            ->get()
            ->pluck('count', 'category')
            ->toArray();
        
        // Get latest logs
        $recentLogs = ScrapingLog::orderBy('occurred_at', 'desc')
            ->limit(5)
            ->get();
        
        // Count logs by type for chart
        $logTypeCounts = ScrapingLog::select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->get()
            ->pluck('count', 'type')
            ->toArray();
        
        // Get queue status
        $failedJobs = DB::table('failed_jobs')->count();
        $pendingJobs = DB::table('jobs')->count();
        
        // Check if scraper is currently running
        $isScraperRunning = Cache::get('scraper_running', false);
        
        // Get last scrape time
        $lastScrapeTime = Cache::get('last_scrape_time');
        if ($lastScrapeTime) {
            $lastScrapeTime = Carbon::parse($lastScrapeTime);
        }
        
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
    }
    
    /**
     * Display logs page.
     */
    public function logs(Request $request)
    {
        $filter = $request->get('filter', 'all');
        $category = $request->get('category');
        
        $logsQuery = ScrapingLog::query()->orderBy('occurred_at', 'desc');
        
        if ($filter !== 'all') {
            $logsQuery->where('type', $filter);
        }
        
        if ($category) {
            $logsQuery->where('category', $category);
        }
        
        $logs = $logsQuery->paginate(20);
        
        $categories = ScrapingLog::select('category')
            ->distinct()
            ->whereNotNull('category')
            ->pluck('category');
        
        return view('admin.logs', compact('logs', 'filter', 'category', 'categories'));
    }
    
    /**
     * Display tasks page.
     */
    public function tasks()
    {
        $pendingJobs = DB::table('jobs')
            ->select('queue', 'payload', 'attempts', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();
        
        $failedJobs = DB::table('failed_jobs')
            ->select('uuid', 'connection', 'queue', 'payload', 'exception', 'failed_at')
            ->orderBy('failed_at', 'desc')
            ->get();
        
        $jobBatches = [];
        if (DB::getSchemaBuilder()->hasTable('job_batches')) {
            $jobBatches = DB::table('job_batches')
                ->select('id', 'name', 'total_jobs', 'pending_jobs', 'failed_jobs', 'created_at', 'finished_at')
                ->orderBy('created_at', 'desc')
                ->get();
        }
        
        return view('admin.tasks', compact('pendingJobs', 'failedJobs', 'jobBatches'));
    }
    
    /**
     * Display system status page.
     */
    public function status()
    {
        $queueConnection = config('queue.default');
        $cacheDriver = config('cache.default');
        $dbConnection = config('database.default');
        
        // Check if scheduler is running
        $schedulerLastRun = Cache::get('scheduler_last_run');
        $isSchedulerRunning = $schedulerLastRun && Carbon::parse($schedulerLastRun)->isAfter(Carbon::now()->subHours(1));
        
        // Check Redis connection if used
        $redisConnected = false;
        if (in_array($queueConnection, ['redis']) || in_array($cacheDriver, ['redis'])) {
            try {
                $redisConnected = app('redis')->connection()->ping() === true;
            } catch (\Exception $e) {
                $redisConnected = false;
            }
        }
        
        // Get system info
        $systemInfo = [
            'PHP Version' => PHP_VERSION,
            'Laravel Version' => app()->version(),
            'Server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'Memory Limit' => ini_get('memory_limit'),
            'Max Execution Time' => ini_get('max_execution_time') . 's',
            'Upload Max Filesize' => ini_get('upload_max_filesize'),
            'Post Max Size' => ini_get('post_max_size'),
        ];
        
        return view('admin.status', compact(
            'queueConnection',
            'cacheDriver',
            '