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
        $packagePath = base_path('vendor/larago/socket');
        $enginePath = $packagePath . '/bin/go-engine';

        // Auto-build if binary doesn't exist
        if (!file_exists($enginePath)) {
            $this->warn('⚠️  Go engine binary not found. Building now...');
            $this->newLine();
            
            if (!$this->buildEngine($packagePath)) {
                return 1;
            }
            
            $this->newLine();
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

    /**
     * Build the Go engine binary
     */
    private function buildEngine($packagePath)
    {
        // Check if build.sh exists
        $buildScript = $packagePath . '/build.sh';
        if (!file_exists($buildScript)) {
            $this->error('build.sh not found at: ' . $buildScript);
            return false;
        }

        // Run build.sh
        $process = new Process(['bash', 'build.sh'], $packagePath);
        
        try {
            $process->mustRun(function ($type, $buffer) {
                echo $buffer;
            });
            
            $this->info('✅ Build completed successfully!');
            return true;
        } catch (\Exception $e) {
            $this->error('❌ Build failed: ' . $e->getMessage());
            $this->error('Make sure Go is installed and available in your PATH');
            return false;
        }
    }
}
