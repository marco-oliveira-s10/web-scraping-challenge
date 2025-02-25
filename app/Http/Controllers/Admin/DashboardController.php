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
        $taskController = app()->make(\App\Http\Controllers\Admin\TaskController::class);
        return $taskController->index();
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

        // Check essential services
        $services = [
            'Database' => $this->checkDatabaseConnection(),
            'Cache' => $this->checkCacheService(),
            'Queue' => $this->checkQueueService(),
            //'Redis' => $redisConnected,
            //'Scheduler' => $isSchedulerRunning,
        ];

        return view('admin.status', compact(
            'queueConnection',
            'cacheDriver',
            'dbConnection',
            'systemInfo',
            'services',
            'isSchedulerRunning'
        ));
    }

    /**
     * Clear all cache
     */
    public function clearCache()
    {
        try {
            Artisan::call('cache:clear');

            return redirect()->route('admin.status')
                ->with('success', 'Cache cleared successfully');
        } catch (\Exception $e) {
            return redirect()->route('admin.status')
                ->with('error', 'Failed to clear cache: ' . $e->getMessage());
        }
    }

    /**
     * Check database connection health
     */
    private function checkDatabaseConnection()
    {
        try {
            // Simple query to check connection
            DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check cache service health
     */
    private function checkCacheService()
    {
        try {
            $testKey = 'system_status_test_' . time();
            Cache::put($testKey, true, 60);
            $result = Cache::get($testKey) === true;
            Cache::forget($testKey);
            return $result;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check queue service health
     */
    private function checkQueueService()
    {
        try {
            // This is a simplified check - in production would be more thorough
            return config('queue.default') !== 'sync';
        } catch (\Exception $e) {
            return false;
        }
    }


    public function retryJob(Request $request)
    {
        return redirect()->route('admin.tasks.index')
            ->with('info', 'This function has been simplified.');
    }

    public function clearFailedJobs()
    {
        return redirect()->route('admin.tasks.index')
            ->with('info', 'This function has been simplified.');
    }
}
