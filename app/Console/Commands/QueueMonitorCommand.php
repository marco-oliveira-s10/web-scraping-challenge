<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\ScrapingLog;

class QueueMonitorCommand extends Command
{
    protected $signature = 'queue:monitor';
    protected $description = 'Monitor queue health and performance';

    protected $thresholds = [
        'max_pending_time' => 3600, // 1 hour
        'max_pending_jobs' => 1000,
        'max_failed_jobs' => 100,
        'max_memory_usage' => 512, // MB
    ];

    public function handle()
    {
        $this->info('Starting queue monitoring...');
        $issues = [];
        $metrics = [];

        try {
            // Check pending jobs
            $pendingJobs = DB::table('jobs')->count();
            $metrics['pending_jobs'] = $pendingJobs;
            
            if ($pendingJobs > $this->thresholds['max_pending_jobs']) {
                $issues[] = "High number of pending jobs: {$pendingJobs}";
            }

            // Check oldest pending job
            $oldestJob = DB::table('jobs')
                ->orderBy('created_at')
                ->first();
                
            if ($oldestJob) {
                $oldestJobAge = Carbon::parse($oldestJob->created_at)->diffInSeconds();
                $metrics['oldest_job_age'] = $oldestJobAge;
                
                if ($oldestJobAge > $this->thresholds['max_pending_time']) {
                    $issues[] = "Job pending for too long: " . Carbon::parse($oldestJob->created_at)->diffForHumans();
                }
            }

            // Check failed jobs
            $failedJobs = DB::table('failed_jobs')->count();
            $metrics['failed_jobs'] = $failedJobs;
            
            if ($failedJobs > $this->thresholds['max_failed_jobs']) {
                $issues[] = "High number of failed jobs: {$failedJobs}";
            }

            // Check recent failures
            $recentFailures = DB::table('failed_jobs')
                ->where('failed_at', '>=', now()->subHours(1))
                ->count();
            $metrics['recent_failures'] = $recentFailures;
            
            if ($recentFailures > 10) {
                $issues[] = "High failure rate in last hour: {$recentFailures} failures";
            }

            // Check memory usage
            $memoryUsage = memory_get_usage(true) / 1024 / 1024; // Convert to MB
            $metrics['memory_usage'] = round($memoryUsage, 2);
            
            if ($memoryUsage > $this->thresholds['max_memory_usage']) {
                $issues[] = "High memory usage: {$metrics['memory_usage']}MB";
            }

            // Check queue worker status
            $activeWorkers = $this->getActiveWorkers();
            $metrics['active_workers'] = $activeWorkers;
            
            if ($activeWorkers < 1) {
                $issues[] = "No active queue workers found";
            }

            // Store metrics in cache
            Cache::put('queue_metrics', [
                'metrics' => $metrics,
                'issues' => $issues,
                'last_check' => now(),
            ], now()->addHours(1));

            // Log status
            if (count($issues) > 0) {
                $this->logIssues($issues, $metrics);
                $this->error('Queue monitoring found issues:');
                foreach ($issues as $issue) {
                    $this->error("- {$issue}");
                }
            } else {
                $this->info('Queue monitoring completed successfully');
                $this->logSuccess($metrics);
            }

            // Display metrics
            $this->table(
                ['Metric', 'Value'],
                collect($metrics)->map(function ($value, $key) {
                    return [
                        'metric' => str_replace('_', ' ', ucfirst($key)),
                        'value' => $value
                    ];
                })->toArray()
            );

            return count($issues) === 0 ? 0 : 1;

        } catch (\Exception $e) {
            $this->error('Error monitoring queue: ' . $e->getMessage());
            Log::error('Queue monitoring failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return 1;
        }
    }

    protected function getActiveWorkers()
    {
        // No Linux, podemos usar o comando ps para verificar os workers ativos
        if (PHP_OS_FAMILY === 'Linux') {
            exec("ps aux | grep 'queue:work' | grep -v grep | wc -l", $output);
            return (int) ($output[0] ?? 0);
        }

        // No Windows, podemos verificar através dos jobs em execução
        return DB::table('jobs')
            ->where('reserved_at', '>=', now()->subMinutes(5))
            ->distinct('queue')
            ->count();
    }

    protected function logIssues(array $issues, array $metrics)
    {
        ScrapingLog::create([
            'type' => 'warning',
            'message' => 'Queue monitoring found issues',
            'context' => [
                'issues' => $issues,
                'metrics' => $metrics
            ],
            'occurred_at' => now()
        ]);
        
        if (count($issues) > 3) {
            // Notificar admins se houver muitos problemas
            \Illuminate\Support\Facades\Notification::send(
                \App\Models\AdminUser::all(),
                //new \App\Notifications\QueueIssuesNotification($issues, $metrics)
            );
        }
    }

    protected function logSuccess(array $metrics)
    {
        ScrapingLog::create([
            'type' => 'success',
            'message' => 'Queue monitoring completed successfully',
            'context' => [
                'metrics' => $metrics
            ],
            'occurred_at' => now()
        ]);
    }
}