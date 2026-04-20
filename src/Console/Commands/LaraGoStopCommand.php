<?php

namespace LaraGo\Socket\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class LaraGoStopCommand extends Command
{
    protected $signature = 'larago:stop {--force : Force kill without graceful shutdown}';
    protected $description = 'Stop the running LaraGo WebSocket engine';

    public function handle()
    {
        $socketPath = '/tmp/larago.sock';
        
        if ($this->option('force')) {
            // Force kill all go-engine processes
            $process = new Process(['pkill', '-9', '-f', 'go-engine']);
            $process->run();
            
            $this->info('🛑 Force killed all engine processes');
        } else {
            // Graceful shutdown using SIGTERM
            $process = new Process(['pkill', '-TERM', '-f', 'go-engine']);
            $process->run();
            
            $this->info('🛑 Sent shutdown signal to engine');
            
            // Wait for graceful shutdown
            sleep(2);
        }
        
        // Clean up stale socket file
        if (file_exists($socketPath)) {
            @unlink($socketPath);
            $this->info('🧹 Cleaned up socket file');
        }
        
        // Verify process is stopped
        $checkProcess = new Process(['pgrep', '-f', 'go-engine']);
        $checkProcess->run();
        
        if (!$checkProcess->isSuccessful()) {
            $this->info('✅ Engine stopped successfully');
            return 0;
        } else {
            $this->warn('⚠️  Some engine processes may still be running');
            $this->line('Force stop with: <comment>php artisan larago:stop --force</comment>');
            return 1;
        }
    }
}
