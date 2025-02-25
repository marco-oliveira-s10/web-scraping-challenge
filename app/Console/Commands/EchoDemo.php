<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class EchoDemo extends Command
{
    protected $signature = 'demo:echo';
    protected $description = 'Echo demo command';

    public function handle()
    {
        $this->info('Echo demo at ' . now());
        Log::info('Scheduled task ran at ' . now());
        return 0;
    }
}
