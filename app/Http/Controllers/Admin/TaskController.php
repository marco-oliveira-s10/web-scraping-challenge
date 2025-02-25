<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Jobs\ScrapeProductsJob;

class TaskController extends Controller
{
    public $availableTasks = [
        'product:fetch' => [
            'name' => 'Fetch Products',
            'description' => 'Fetch all products from external API',
            'schedule' => 'Every 10 minutes',
            'job_class' => ScrapeProductsJob::class
        ]
    ];

    public function index()
    {
        try {
            // Preparar task agendada
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
                ->where('queue', 'default') // Filtra jobs da fila padrão
                ->orderBy('created_at', 'desc')
                ->limit(50) // Limita para evitar sobrecarga
                ->get();

            // Buscar jobs falhos
            $failedJobs = DB::table('failed_jobs')
                ->select(['uuid', 'connection', 'queue', 'payload', 'exception', 'failed_at'])
                ->orderBy('failed_at', 'desc')
                ->limit(50) // Limita para evitar sobrecarga
                ->get();

            // Buscar batches ativos
            $jobBatches = [];
            if (DB::getSchemaBuilder()->hasTable('job_batches')) {
                $jobBatches = DB::table('job_batches')
                    ->whereNull('finished_at')
                    ->orderBy('created_at', 'desc')
                    ->get();
            }

            return view('admin.tasks', compact(
                'scheduledTasks',
                'pendingJobs',
                'failedJobs',
                'jobBatches'
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
            return response()->json(['error' => 'Invalid task: ' . $task], 400);
        }

        try {
            // Pega a classe de job associada à tarefa
            $jobClass = $this->availableTasks[$task]['job_class'] ?? null;

            if ($jobClass && class_exists($jobClass)) {
                // Dispatch o job diretamente
                $job = new $jobClass();
                dispatch($job);

                return response()->json([
                    'message' => 'Task queued successfully'
                ]);
            } else {
                throw new \Exception('Job class not found');
            }
        } catch (\Exception $e) {
            Log::error('Task execution failed', [
                'task' => $task,
                'error' => $e->getMessage()
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

    protected function getLastRunTime($command)
    {
        $lastRun = Cache::get("task_last_run_{$command}");
        return $lastRun ? Carbon::parse($lastRun)->format('Y-m-d H:i:s') : null;
    }

    protected function calculateNextRun($command)
    {
        // Calcula o próximo horário baseado no último horário de execução
        $lastRun = Cache::get("task_last_run_{$command}");
        
        if ($lastRun) {
            $nextRun = Carbon::parse($lastRun)->addMinutes(10);
            return $nextRun->format('Y-m-d H:i:s');
        }

        // Se nunca rodou, próxima execução é agora + 10 minutos
        return now()->addMinutes(10)->format('Y-m-d H:i:s');
    }

    protected function getTaskStatus($command)
    {
        $lastRun = Cache::get("task_last_run_{$command}");
        $status = Cache::get("task_status_{$command}", 'idle');

        // Se o status for 'enabled' e já passou o tempo de execução, muda para 'waiting'
        if ($status === 'enabled' && $lastRun) {
            $nextRun = Carbon::parse($lastRun)->addMinutes(10);
            return $nextRun->isPast() ? 'waiting' : 'scheduled';
        }

        return $status;
    }
}