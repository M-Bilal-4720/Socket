<?php

namespace LaraGo\Socket\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class LaraGoRunCommand extends Command
{
    protected $signature = 'larago:run {--host=0.0.0.0 : The host to run on} {--port=8080 : The port to run on}';
    protected $description = 'Start the LaraGo WebSocket engine';

    public function handle()
    {
        $enginePath = base_path('vendor/larago/socket/bin/go-engine');

        if (!file_exists($enginePath)) {
            $this->error('Go engine binary not found at: ' . $enginePath);
            $this->info('Run: cd vendor/larago/socket && bash build.sh');
            return 1;
        }

        $this->info('🚀 Starting LaraGo Engine...');
        $this->info("📡 Listening on port: {$this->option('port')}");
        $this->info('📍 Unix socket: /tmp/larago.sock');
        $this->info('Press Ctrl+C to stop');
        $this->newLine();

        // Set environment variables if custom host/port provided
        $env = $_ENV;
        if ($this->option('host') !== '0.0.0.0' || $this->option('port') !== '8080') {
            $env['LARAGO_HOST'] = $this->option('host');
            $env['LARAGO_PORT'] = $this->option('port');
        }

        // Create and run the process
        $process = new Process([$enginePath], null, $env);
        $process->setTty(true);
        $process->mustRun(function ($type, $buffer) {
            echo $buffer;
        });

        return 0;
    }
}
