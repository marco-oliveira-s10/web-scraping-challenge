<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use App\Models\ScrapingLog;
use App\Models\Product;

class DbCleanCommand extends Command
{
    protected $signature = 'db:clean';
    protected $description = 'Clean up old records from database';

    public function handle()
    {
        $this->info('Starting database cleanup...');
        $deletedCounts = [];

        try {
            DB::beginTransaction();

            // Limpar logs antigos
            $oldLogsCount = ScrapingLog::where('occurred_at', '<', now()->subMonths(3))
                ->delete();
            $deletedCounts['old_logs'] = $oldLogsCount;
            $this->info("Deleted {$oldLogsCount} old logs");

            // Limpar failed_jobs antigos
            $oldFailedJobsCount = DB::table('failed_jobs')
                ->where('failed_at', '<', now()->subMonths(1))
                ->delete();
            $deletedCounts['old_failed_jobs'] = $oldFailedJobsCount;
            $this->info("Deleted {$oldFailedJobsCount} old failed jobs");

            // Limpar job_batches antigos
            if (Schema::hasTable('job_batches')) {
                $oldBatchesCount = DB::table('job_batches')
                    ->where('created_at', '<', now()->subWeeks(2))
                    ->delete();
                $deletedCounts['old_batches'] = $oldBatchesCount;
                $this->info("Deleted {$oldBatchesCount} old job batches");
            }

            // Limpar produtos obsoletos
            $oldProductsCount = Product::where('updated_at', '<', now()->subMonths(6))
                ->whereNull('category')
                ->delete();
            $deletedCounts['old_products'] = $oldProductsCount;
            $this->info("Deleted {$oldProductsCount} obsolete products");

            // Otimizar tabelas
            if (config('database.default') === 'mysql') {
                $tables = DB::select('SHOW TABLES');
                foreach ($tables as $table) {
                    $tableName = array_values((array)$table)[0];
                    DB::statement("OPTIMIZE TABLE {$tableName}");
                    $this->info("Optimized table: {$tableName}");
                }
            }

            DB::commit();

            // Log sucesso
            ScrapingLog::create([
                'type' => 'success',
                'message' => 'Database cleanup completed successfully',
                'context' => [
                    'deleted_counts' => $deletedCounts,
                    'execution_time' => now()->diffInSeconds(Carbon::now())
                ],
                'occurred_at' => now()
            ]);

            $this->info('Database cleanup completed successfully!');
            return 0;

        } catch (\Exception $e) {
            DB::rollBack();

            $error = "Database cleanup failed: " . $e->getMessage();
            $this->error($error);
            
            Log::error($error, [
                'exception' => $e,
                'deleted_counts' => $deletedCounts
            ]);

            ScrapingLog::create([
                'type' => 'error',
                'message' => $error,
                'context' => [
                    'exception' => $e->getMessage(),
                    'deleted_counts' => $deletedCounts
                ],
                'occurred_at' => now()
            ]);

            return 1;
        }
    }

    protected function analyzeTableSizes()
    {
        if (config('database.default') !== 'mysql') {
            return [];
        }

        $sizes = [];
        $tables = DB::select('SHOW TABLE STATUS');
        
        foreach ($tables as $table) {
            $sizes[$table->Name] = [
                'rows' => $table->Rows,
                'size' => round(($table->Data_length + $table->Index_length) / 1024 / 1024, 2) . 'MB',
                'engine' => $table->Engine
            ];
        }

        return $sizes;
    }
}