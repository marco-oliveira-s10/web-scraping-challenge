<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use App\Models\ScrapingLog;

class HealthCheckCommand extends Command
{
    protected $signature = 'health:check';
    protected $description = 'Check the health status of all system components';

    public function handle()
    {
        $this->info('Starting system health check...');
        $status = [];

        // Check Database
        try {
            DB::connection()->getPdo();
            $status['database'] = true;
            $this->info('✓ Database connection: OK');
        } catch (\Exception $e) {
            $status['database'] = false;
            $this->error('✗ Database connection: Failed - ' . $e->getMessage());
            Log::error('Database health check failed', ['error' => $e->getMessage()]);
        }

        // Check Redis
        try {
            Redis::ping();
            $status['redis'] = true;
            $this->info('✓ Redis connection: OK');
        } catch (\Exception $e) {
            $status['redis'] = false;
            $this->error('✗ Redis connection: Failed - ' . $e->getMessage());
            Log::error('Redis health check failed', ['error' => $e->getMessage()]);
        }

        // Check Cache
        try {
            $testKey = 'health_check_' . time();
            Cache::put($testKey, true, 1);
            $testResult = Cache::get($testKey);
            $status['cache'] = ($testResult === true);
            $this->info('✓ Cache system: OK');
        } catch (\Exception $e) {
            $status['cache'] = false;
            $this->error('✗ Cache system: Failed - ' . $e->getMessage());
            Log::error('Cache health check failed', ['error' => $e->getMessage()]);
        }

        // Check Queue
        try {
            $queueCount = DB::table('jobs')->count();
            $status['queue'] = true;
            $this->info("✓ Queue system: OK ({$queueCount} jobs in queue)");
        } catch (\Exception $e) {
            $status['queue'] = false;
            $this->error('✗ Queue system: Failed - ' . $e->getMessage());
            Log::error('Queue health check failed', ['error' => $e->getMessage()]);
        }

        // Check Disk Space
        $diskFree = disk_free_space(base_path());
        $diskTotal = disk_total_space(base_path());
        $diskUsedPercentage = round((($diskTotal - $diskFree) / $diskTotal) * 100, 2);
        
        if ($diskUsedPercentage > 90) {
            $status['disk'] = false;
            $this->error("✗ Disk space critical: {$diskUsedPercentage}% used");
            Log::warning('Disk space usage critical', ['usage_percentage' => $diskUsedPercentage]);
        } else {
            $status['disk'] = true;
            $this->info("✓ Disk space: OK ({$diskUsedPercentage}% used)");
        }

        // Check Recent Failed Jobs
        $recentFailedJobs = DB::table('failed_jobs')
            ->where('failed_at', '>=', now()->subHours(24))
            ->count();
            
        if ($recentFailedJobs > 0) {
            $this->warn("! Failed Jobs: {$recentFailedJobs} in last 24 hours");
            Log::warning('Failed jobs detected', ['count' => $recentFailedJobs]);
        } else {
            $this->info('✓ Failed Jobs: None in last 24 hours');
        }

        // Check Memory Usage
        $memoryLimit = ini_get('memory_limit');
        $memoryUsage = memory_get_usage(true);
        $memoryUsageFormatted = round($memoryUsage / 1048576, 2);
        $this->info("✓ Memory Usage: {$memoryUsageFormatted}MB / {$memoryLimit}");

        // Save status to cache
        Cache::put('system_health', [
            'status' => $status,
            'last_check' => now(),
            'memory_usage' => $memoryUsageFormatted,
            'disk_usage' => $diskUsedPercentage,
            'failed_jobs_24h' => $recentFailedJobs
        ], now()->addHours(1));

        // Log health check
        ScrapingLog::create([
            'type' => array_sum($status) == count($status) ? 'success' : 'warning',
            'message' => 'System health check completed',
            'context' => [
                'status' => $status,
                'memory_usage' => $memoryUsageFormatted,
                'disk_usage' => $diskUsedPercentage,
                'failed_jobs_24h' => $recentFailedJobs
            ],
            'occurred_at' => now()
        ]);

        return array_sum($status) == count($status) ? 0 : 1;
    }
}