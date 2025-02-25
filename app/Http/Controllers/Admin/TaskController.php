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
            'schedule' => 'Every 2 minutes',
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
        $taskId = $request->input('task');

        if ($taskId === 'product:fetch') {
            // Cria uma instância do ProductController
            $productController = app()->make(\App\Http\Controllers\Admin\ProductController::class);

            // Chama o método scrape
            $response = $productController->scrape($request);

            // Registra o horário da última execução
            Cache::put("task_last_run_{$taskId}", now());

            return response()->json([
                'message' => 'Scraping task started successfully'
            ]);
        }

        return response()->json(['error' => 'Invalid task'], 400);
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

    // Métodos para calcular last_run e next_run
    protected function getLastRunTime($command)
    {
        $lastRun = Cache::get("task_last_run_{$command}");
        return $lastRun ? Carbon::parse($lastRun)->format('Y-m-d H:i:s') : null;
    }

    protected function calculateNextRun($command)
    {
        $lastRun = Cache::get("task_last_run_{$command}");

        if ($lastRun) {
            $nextRun = Carbon::parse($lastRun)->addMinutes(2);
            return $nextRun->format('Y-m-d H:i:s');
        }

        // Se nunca rodou, próxima execução é agora + 2 minutos
        return now()->addMinutes(2)->format('Y-m-d H:i:s');
    }

    protected function getTaskStatus($command)
    {
        $lastRun = Cache::get("task_last_run_{$command}");
        $status = Cache::get("task_status_{$command}", 'idle');

        // Se o status for 'enabled' e já passou o tempo de execução, muda para 'waiting'
        if ($status === 'enabled' && $lastRun) {
            $nextRun = Carbon::parse($lastRun)->addMinutes(2);
            return $nextRun->isPast() ? 'waiting' : 'scheduled';
        }

        return $status;
    }
}
