<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\ScrapingLog;

class TaskController extends Controller
{
    protected $availableTasks = [
        'scraper:categories' => [
            'name' => 'Scrape Categories',
            'description' => 'Scrape all product categories',
            'schedule' => 'Every 6 hours'
        ],
        'scraper:cleanup' => [
            'name' => 'Cleanup Old Data',
            'description' => 'Remove old and obsolete data',
            'schedule' => 'Weekly (Sunday)'
        ],
        'health:check' => [
            'name' => 'System Health Check',
            'description' => 'Monitor system health status',
            'schedule' => 'Every 15 minutes'
        ],
        'queue:monitor' => [
            'name' => 'Queue Monitor',
            'description' => 'Monitor queue performance',
            'schedule' => 'Every 10 minutes'
        ],
        'db:clean' => [
            'name' => 'Database Cleanup',
            'description' => 'Optimize database tables',
            'schedule' => 'Weekly (Saturday)'
        ]
    ];

    public function index()
    {
        try {
            // Preparar tasks agendadas
            $scheduledTasks = collect($this->availableTasks)->map(function ($task, $id) {
                return array_merge($task, [
                    'id' => $id,
                    'last_run' => $this->getLastRunTime($id),
                    'next_run' => $this->calculateNextRun($id),
                    'status' => $this->getTaskStatus($id)
                ]);
            })->values()->all();

            // Buscar jobs pendentes
            $pendingJobs = DB::table('jobs')
                ->select(['id', 'queue', 'payload', 'attempts', 'created_at', 'available_at'])
                ->orderBy('created_at', 'desc')
                ->get();

            // Buscar jobs falhos
            $failedJobs = DB::table('failed_jobs')
                ->select(['uuid', 'connection', 'queue', 'payload', 'exception', 'failed_at'])
                ->orderBy('failed_at', 'desc')
                ->get();

            // Buscar batches ativos
            $jobBatches = DB::table('job_batches')
                ->whereNull('finished_at')
                ->orderBy('created_at', 'desc')
                ->get();

            // Métricas do sistema
            $systemMetrics = [
                'queue_size' => DB::table('jobs')->count(),
                'failed_jobs' => DB::table('failed_jobs')->count(),
                'memory_usage' => round(memory_get_usage(true) / 1024 / 1024, 2) . 'MB',
                'disk_free' => round(disk_free_space('/') / 1024 / 1024 / 1024, 2) . 'GB'
            ];

            // Retornar view correta
            return view('admin.tasks', compact(
                'scheduledTasks',
                'pendingJobs',
                'failedJobs',
                'jobBatches',
                'systemMetrics'
            ));

        } catch (\Exception $e) {
            Log::error('Error loading tasks page: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Error loading task information: ' . $e->getMessage());
        }
    }

    public function run(Request $request)
    {
        $task = $request->input('task');

        if (!array_key_exists($task, $this->availableTasks)) {
            return response()->json(['error' => 'Invalid task'], 400);
        }

        try {
            Cache::put("task_status_{$task}", 'running');
            Cache::put("task_start_{$task}", now());

            Artisan::call($task);
            $output = Artisan::output();

            Cache::put("task_last_run_{$task}", now());
            Cache::put("task_status_{$task}", 'completed');
            Cache::forget("task_start_{$task}");

            ScrapingLog::create([
                'type' => 'info',
                'message' => "Manual task execution: {$this->availableTasks[$task]['name']}",
                'context' => ['output' => $output],
                'occurred_at' => now()
            ]);

            return response()->json([
                'message' => 'Task executed successfully',
                'output' => $output
            ]);
        } catch (\Exception $e) {
            Cache::put("task_status_{$task}", 'failed');
            Cache::forget("task_start_{$task}");

            Log::error('Task execution failed', [
                'task' => $task,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            ScrapingLog::create([
                'type' => 'error',
                'message' => "Task execution failed: {$this->availableTasks[$task]['name']}",
                'context' => [
                    'error' => $e->getMessage(),
                    'task' => $task
                ],
                'occurred_at' => now()
            ]);

            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function status()
    {
        $tasks = collect($this->availableTasks)->map(function ($task, $command) {
            return [
                'id' => $command,
                'name' => $task['name'],
                'last_run' => $this->getLastRunTime($command),
                'next_run' => $this->calculateNextRun($command),
                'status' => $this->getTaskStatus($command),
                'schedule' => $task['schedule']
            ];
        });

        return response()->json(['tasks' => $tasks]);
    }

    public function toggle($taskId)
    {
        if (!array_key_exists($taskId, $this->availableTasks)) {
            return response()->json(['error' => 'Invalid task'], 400);
        }

        try {
            $currentStatus = Cache::get("task_status_{$taskId}", 'enabled');
            $newStatus = $currentStatus === 'enabled' ? 'disabled' : 'enabled';

            Cache::put("task_status_{$taskId}", $newStatus);

            ScrapingLog::create([
                'type' => 'info',
                'message' => "Task {$this->availableTasks[$taskId]['name']} {$newStatus}",
                'context' => [
                    'task' => $taskId,
                    'status' => $newStatus
                ],
                'occurred_at' => now()
            ]);

            return response()->json([
                'message' => "Task {$newStatus} successfully",
                'status' => $newStatus
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to toggle task', [
                'task' => $taskId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to toggle task status'
            ], 500);
        }
    }

    public function retry(Request $request)
    {
        try {
            $uuid = $request->input('uuid');
            Artisan::call('queue:retry', ['id' => [$uuid]]);

            ScrapingLog::create([
                'type' => 'info',
                'message' => "Failed job retry attempted",
                'context' => ['uuid' => $uuid],
                'occurred_at' => now()
            ]);

            return response()->json(['message' => 'Job queued for retry']);
        } catch (\Exception $e) {
            Log::error('Failed to retry job', [
                'uuid' => $request->input('uuid'),
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to retry job'], 500);
        }
    }

    public function retryAll()
    {
        try {
            Artisan::call('queue:retry', ['--all' => true]);

            ScrapingLog::create([
                'type' => 'info',
                'message' => "All failed jobs queued for retry",
                'occurred_at' => now()
            ]);

            return response()->json(['message' => 'All failed jobs queued for retry']);
        } catch (\Exception $e) {
            Log::error('Failed to retry all jobs', [
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to retry all jobs'], 500);
        }
    }

    public function clear()
    {
        try {
            Artisan::call('queue:flush');

            ScrapingLog::create([
                'type' => 'info',
                'message' => "All failed jobs cleared",
                'occurred_at' => now()
            ]);

            return response()->json(['message' => 'All failed jobs cleared']);
        } catch (\Exception $e) {
            Log::error('Failed to clear jobs', [
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to clear jobs'], 500);
        }
    }

    protected function getLastRunTime($command)
    {
        $lastRun = Cache::get("task_last_run_{$command}");
        return $lastRun ? Carbon::parse($lastRun)->format('Y-m-d H:i:s') : null;
    }

    protected function calculateNextRun($command)
    {
        $schedules = [
            'scraper:categories' => now()->addHours(6),
            'scraper:cleanup' => now()->next('Sunday'),
            'health:check' => now()->addMinutes(15),
            'queue:monitor' => now()->addMinutes(10),
            'db:clean' => now()->next('Saturday')
        ];

        return $schedules[$command] ?? now()->addDay();
    }

    protected function getTaskStatus($command)
    {
        $status = Cache::get("task_status_{$command}", 'idle');

        if ($status === 'running') {
            $startTime = Cache::get("task_start_{$command}");
            if ($startTime && Carbon::parse($startTime)->diffInHours() > 1) {
                return 'stuck';
            }
        }

        return $status;
    }

    protected function getSystemMetrics()
    {
        return [
            'queue_size' => DB::table('jobs')->count(),
            'failed_jobs' => DB::table('failed_jobs')->count(),
            'memory_usage' => round(memory_get_usage(true) / 1024 / 1024, 2) . 'MB',
            'disk_free' => round(disk_free_space('/') / 1024 / 1024 / 1024, 2) . 'GB'
        ];
    }
}
