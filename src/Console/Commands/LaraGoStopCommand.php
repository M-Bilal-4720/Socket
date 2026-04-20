<?php

namespace LaraGo\Socket\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class LaraGoStopCommand extends Command
{
    protected $signature = 'larago:stop {--force : Force kill without graceful shutdown}';
    protected $description = 'Stop the running LaraGo WebSocket engine';

    private $isWindows = false;

    public function __construct()
    {
        parent::__construct();
        $this->isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }

    public function handle()
    {
        if ($this->option('force')) {
            $this->forceKill();
        } else {
            $this->gracefulStop();
        }

        // Verify process is stopped
        if ($this->isEngineRunning()) {
            $this->warn('⚠️  Some engine processes may still be running');
            $this->line('Force stop with: <comment>php artisan larago:stop --force</comment>');
            return 1;
        }

        $this->info('✅ Engine stopped successfully');
        return 0;
    }

    /**
     * Gracefully stop the engine
     */
    private function gracefulStop()
    {
        if ($this->isWindows) {
            // Windows: use taskkill without force flag for graceful shutdown
            shell_exec('taskkill /IM go-engine.exe 2>nul');
            $this->info('🛑 Sent shutdown signal to engine');
        } else {
            // Unix/Mac: use SIGTERM for graceful shutdown
            $process = new Process(['pkill', '-TERM', '-f', 'go-engine']);
            $process->run();
            $this->info('🛑 Sent shutdown signal to engine');
        }
        
        // Wait for graceful shutdown
        sleep(2);
    }

    /**
     * Force kill the engine
     */
    private function forceKill()
    {
        if ($this->isWindows) {
            // Windows: use taskkill with /F flag
            shell_exec('taskkill /IM go-engine.exe /F 2>nul');
            $this->info('🛑 Force killed all engine processes');
        } else {
            // Unix/Mac: use -9 signal
            $process = new Process(['pkill', '-9', '-f', 'go-engine']);
            $process->run();
            $this->info('🛑 Force killed all engine processes');
        }
    }

    /**
     * Check if engine is running
     */
    private function isEngineRunning()
    {
        if ($this->isWindows) {
            // Windows: check tasklist
            $output = shell_exec('tasklist 2>&1');
            return stripos($output, 'go-engine.exe') !== false;
        } else {
            // Unix/Mac: use pgrep
            $process = new Process(['pgrep', '-f', 'go-engine']);
            $process->run();
            return $process->isSuccessful() && !empty(trim($process->getOutput()));
        }
    }
}
