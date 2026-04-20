<?php

namespace LaraGo\Socket\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class LaraGoRunCommand extends Command
{
    protected $signature = 'larago:run {--host=0.0.0.0 : The host to run on} {--port=8080 : The port to run on} {--force : Kill existing engine instance and start fresh} {--background : Run engine in background}';
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

        // Check if socket is already in use
        if ($this->option('force')) {
            $this->killExistingEngine();
        } else {
            if ($this->isSocketInUse()) {
                $this->warn('⚠️  LaraGo engine is already running!');
                $this->newLine();
                $this->line('Options:');
                $this->line('  • To start a new instance, kill the existing one:');
                $this->line('    <comment>pkill -f "go-engine"</comment>');
                $this->line('  • Or use the --force flag:');
                $this->line('    <comment>php artisan larago:run --force</comment>');
                return 0;
            }
        }

        $this->info('🚀 Starting LaraGo Engine...');
        $this->info("📡 Listening on {$this->option('host')}:{$this->option('port')}");
        $this->info('📍 Unix socket: /tmp/larago.sock');
        $this->info('🔐 Supports both public and private channels');
        
        if ($this->option('background')) {
            $this->info('🔄 Running in background mode');
        } else {
            $this->info('Press Ctrl+C to stop');
        }
        $this->newLine();

        // Set environment variables
        $env = $_ENV;
        $env['LARAGO_HOST'] = $this->option('host');
        $env['LARAGO_PORT'] = $this->option('port');
        $env['LARAGO_JWT_SECRET'] = config('app.key') ?: 'larago-secret-key';

        if ($this->option('background')) {
            // Run in background using nohup to properly detach from parent process
            $logFile = storage_path('logs/larago-engine.log');
            $command = "nohup '$enginePath' > '$logFile' 2>&1 &";
            
            // Build environment string
            $envStr = '';
            $envStr .= "LARAGO_HOST={$this->option('host')} ";
            $envStr .= "LARAGO_PORT={$this->option('port')} ";
            $secret = config('app.key') ?: 'larago-secret-key';
            $envStr .= "LARAGO_JWT_SECRET='$secret' ";
            
            $fullCommand = $envStr . $command;
            
            // Execute the background command
            shell_exec($fullCommand);
            
            // Wait a moment for process to start
            sleep(1);
            
            // Verify process is running
            $pidProcess = new Process(['pgrep', '-f', 'go-engine']);
            $pidProcess->run();
            
            if ($pidProcess->isSuccessful()) {
                $pid = trim($pidProcess->getOutput());
                $this->info('✅ Engine started successfully and running in background');
                $this->line('PID: <comment>' . $pid . '</comment>');
                $this->line('Log: <comment>' . $logFile . '</comment>');
                $this->line('Stop with: <comment>php artisan larago:stop</comment> or <comment>pkill -f go-engine</comment>');
                return 0;
            } else {
                $this->error('❌ Engine failed to start in background');
                return 1;
            }
        } else {
            // Create and run the process
            $process = new Process([$enginePath], null, $env);
            // Run in foreground with TTY
            $process->setTty(true);
            
            try {
                $process->mustRun(function ($type, $buffer) {
                    echo $buffer;
                });
            } catch (ProcessFailedException $e) {
                $output = $e->getProcess()->getErrorOutput();
                
                // Check if it's a socket in use error
                if (strpos($output, 'bind: address already in use') !== false || 
                    strpos($output, 'listen unix') !== false) {
                    $this->error('❌ Unix socket already in use!');
                    $this->newLine();
                    $this->line('The engine is already running or the socket file is locked.');
                    $this->line('Options:');
                    $this->line('  1. Kill the existing engine:');
                    $this->line('     <comment>pkill -f "go-engine"</comment>');
                    $this->line('  2. Or remove the stale socket file:');
                    $this->line('     <comment>rm /tmp/larago.sock</comment>');
                    $this->line('  3. Or use --force flag to auto-kill existing:');
                    $this->line('     <comment>php artisan larago:run --force</comment>');
                    return 1;
                }
                
                $this->error('❌ Engine failed to start: ' . $e->getMessage());
                return 1;
            }
        }

        return 0;
    }

    /**
     * Check if Unix socket is already in use
     */
    private function isSocketInUse()
    {
        $socketPath = '/tmp/larago.sock';
        
        if (!file_exists($socketPath)) {
            return false;
        }
        
        // Socket file exists, check if any process is listening
        // Use lsof if available, otherwise assume it's in use
        $process = new Process(['lsof', $socketPath]);
        $process->run();
        
        if ($process->isSuccessful() && !empty($process->getOutput())) {
            return true;
        }
        
        // Fallback: if socket exists, assume it might be in use
        return true;
    }

    /**
     * Kill existing Go engine processes
     */
    private function killExistingEngine()
    {
        $process = new Process(['pkill', '-9', '-f', 'go-engine']);
        $process->run();
        
        // Give process time to fully terminate
        sleep(1);
        
        // Clean up stale socket file
        if (file_exists('/tmp/larago.sock')) {
            @unlink('/tmp/larago.sock');
            $this->info('🧹 Cleaned up stale socket file');
        } else {
            $this->info('✅ Killed existing engine instance');
        }
        
        $this->newLine();
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
