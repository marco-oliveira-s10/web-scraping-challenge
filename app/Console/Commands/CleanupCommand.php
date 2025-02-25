<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CleanupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scraper:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old logs and failed jobs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $this->info('Starting cleanup process...');
            Log::info('Starting cleanup command');

            // Clean up failed jobs older than 30 days
            $oldJobsCount = DB::table('failed_jobs')
                ->where('failed_at', '<', Carbon::now()->subDays(30))
                ->delete();
            
            $this->info("Deleted {$oldJobsCount} old failed jobs");
            Log::info('Deleted old failed jobs', ['count' => $oldJobsCount]);
            
            // Clean up completed jobs older than 7 days
            if (DB::getSchemaBuilder()->hasTable('jobs')) {
                $completedJobsCount = DB::table('jobs')
                    ->where('created_at', '<', Carbon::now()->subDays(7))
                    ->delete();
                
                $this->info("Deleted {$completedJobsCount} old completed jobs");
                Log::info('Deleted old completed jobs', ['count' => $completedJobsCount]);
            }
            
            // Clean up job batches older than 14 days
            if (DB::getSchemaBuilder()->hasTable('job_batches')) {
                $oldBatchesCount = DB::table('job_batches')
                    ->where('created_at', '<', Carbon::now()->subDays(14))
                    ->delete();
                
                $this->info("Deleted {$oldBatchesCount} old job batches");
                Log::info('Deleted old job batches', ['count' => $oldBatchesCount]);
            }
            
            // Other cleanup tasks can be added here
            
            $this->info('Cleanup process completed successfully');
            Log::info('Cleanup command completed successfully');
            
            return 0;
        } catch (\Exception $e) {
            $this->error('Error during cleanup process: ' . $e->getMessage());
            Log::error('Error in cleanup command: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            return 1;
        }
    }
}