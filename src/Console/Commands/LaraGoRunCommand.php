<?php

namespace LaraGo\Socket\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class LaraGoRunCommand extends Command
{
    protected $signature = 'larago:run {--host=0.0.0.0 : The host to run on} {--port=8080 : The port to run on} {--force : Kill existing engine instance and start fresh} {--background : Run engine in background}';
    protected $description = 'Start the LaraGo WebSocket engine';

    private $isWindows = false;
    private $isMac = false;
    private $laravelPort = '6001';

    public function __construct()
    {
        parent::__construct();
        $this->isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        $this->isMac = PHP_OS_FAMILY === 'Darwin';
    }

    public function handle()
    {
        $packagePath = base_path('vendor/larago/socket');
        $enginePath = $packagePath . '/bin/go-engine';
        
        // On Windows, add .exe extension
        if ($this->isWindows) {
            $enginePath .= '.exe';
        }

        // Auto-build if binary doesn't exist
        if (!file_exists($enginePath)) {
            $this->warn('⚠️  Go engine binary not found. Building now...');
            $this->newLine();
            
            if (!$this->buildEngine($packagePath)) {
                return 1;
            }
            
            $this->newLine();
        }

        // Check if engine is already running
        if ($this->option('force')) {
            $this->killExistingEngine();
        } else {
            if ($this->isEngineRunning()) {
                $this->warn('⚠️  LaraGo engine is already running!');
                $this->newLine();
                $this->line('Options:');
                $this->line('  • To start a new instance, kill the existing one:');
                if ($this->isWindows) {
                    $this->line('    <comment>taskkill /IM go-engine.exe /F</comment>');
                } else {
                    $this->line('    <comment>pkill -f "go-engine"</comment>');
                }
                $this->line('  • Or use the --force flag:');
                $this->line('    <comment>php artisan larago:run --force</comment>');
                return 0;
            }
        }

        $this->info('🚀 Starting LaraGo Engine...');
        $this->info("📡 WebSocket on {$this->option('host')}:{$this->option('port')}");
        $this->info("🔗 Laravel Communication on 127.0.0.1:{$this->laravelPort}");
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
        $env['LARAGO_LARAVEL_PORT'] = $this->laravelPort;
        $env['LARAGO_JWT_SECRET'] = config('app.key') ?: 'larago-secret-key';

        if ($this->option('background')) {
            return $this->runInBackground($enginePath, $env);
        } else {
            return $this->runInForeground($enginePath, $env);
        }
    }

    /**
     * Run engine in background mode
     */
    private function runInBackground($enginePath, $env)
    {
        $logFile = storage_path('logs/larago-engine.log');
        
        if ($this->isWindows) {
            // Windows background execution using START command
            $secret = config('app.key') ?: 'larago-secret-key';
            $envStr = '';
            $envStr .= "set LARAGO_HOST=" . $this->option('host') . "&& ";
            $envStr .= "set LARAGO_PORT=" . $this->option('port') . "&& ";
            $envStr .= "set LARAGO_LARAVEL_PORT=" . $this->laravelPort . "&& ";
            $envStr .= "set LARAGO_JWT_SECRET=" . $secret . "&& ";
            
            $logFile = storage_path('logs/larago-engine.log');
            $cmd = "START /B {$envStr} \"{$enginePath}\"";
            
            // Use proc_open for more reliable background execution
            $descriptors = array(
                0 => array("pipe", "r"),
                1 => array("file", $logFile, "a"),
                2 => array("file", $logFile, "a")
            );
            
            $process = proc_open($cmd, $descriptors, $pipes, null, null);
            
            if (is_resource($process)) {
                proc_close($process);
                sleep(4);
                $this->info('✅ Engine started successfully in background');
                $this->line('Log: <comment>' . $logFile . '</comment>');
                $this->line('Stop with: <comment>php artisan larago:stop</comment> or <comment>taskkill /IM go-engine.exe /F</comment>');
                return 0;
            } else {
                $this->error('❌ Failed to start engine');
                return 1;
            }
        } else {
            // Unix/Mac background execution using nohup
            $secret = config('app.key') ?: 'larago-secret-key';
            $cmd = sprintf(
                "LARAGO_HOST='%s' LARAGO_PORT='%s' LARAGO_LARAVEL_PORT='%s' LARAGO_JWT_SECRET='%s' nohup '%s' > '%s' 2>&1 &",
                $this->option('host'),
                $this->option('port'),
                $this->laravelPort,
                $secret,
                $enginePath,
                $logFile
            );
            
            shell_exec($cmd);
            sleep(1);
            
            // Get PID
            $pidProcess = new Process(['pgrep', '-f', 'go-engine']);
            $pidProcess->run();
            $pid = trim($pidProcess->getOutput());
            
            if (!empty($pid)) {
                $this->info('✅ Engine started successfully in background');
                $this->line('PID: <comment>' . $pid . '</comment>');
                $this->line('Log: <comment>' . $logFile . '</comment>');
                $this->line('Stop with: <comment>php artisan larago:stop</comment> or <comment>pkill -f go-engine</comment>');
                return 0;
            } else {
                $this->error('❌ Engine failed to start in background');
                return 1;
            }
        }
    }

    /**
     * Run engine in foreground mode
     */
    private function runInForeground($enginePath, $env)
    {
        $process = new Process([$enginePath], null, $env);
        
        // Only set TTY mode on Unix-like systems (not Windows)
        if (!$this->isWindows) {
            $process->setTty(true);
        }
        
        try {
            $process->mustRun(function ($type, $buffer) {
                echo $buffer;
            });
        } catch (ProcessFailedException $e) {
            $output = $e->getProcess()->getErrorOutput();
            
            if (strpos($output, 'address already in use') !== false || 
                strpos($output, 'bind:') !== false) {
                $this->error('❌ Port already in use!');
                $this->newLine();
                $this->line('The engine is already running or the port is occupied.');
                $this->line('Options:');
                $this->line('  1. Kill the existing engine:');
                if ($this->isWindows) {
                    $this->line('     <comment>taskkill /IM go-engine.exe /F</comment>');
                } else {
                    $this->line('     <comment>pkill -f "go-engine"</comment>');
                }
                $this->line('  2. Or use a different port:');
                $this->line('     <comment>php artisan larago:run --port=9000</comment>');
                $this->line('  3. Or use --force flag to auto-kill existing:');
                $this->line('     <comment>php artisan larago:run --force</comment>');
                return 1;
            }
            
            $this->error('❌ Engine failed to start: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Check if Go engine is running
     */
    private function isEngineRunning()
    {
        $port = $this->option('port') ?: 8080;
        
        // Check if port is listening
        if ($this->isWindows) {
            // Windows: check if port is in LISTENING state
            $output = shell_exec('netstat -ano 2>nul | findstr "LISTENING.*:' . $port . '"');
            return !empty($output);
        } else {
            // Unix/Mac: use lsof to check port
            $process = new Process(['lsof', '-i', ':' . $port]);
            $process->run();
            return $process->isSuccessful() && !empty(trim($process->getOutput()));
        }
    }

    /**
     * Kill existing Go engine processes
     */
    private function killExistingEngine()
    {
        if ($this->isWindows) {
            // Windows: use taskkill
            shell_exec('taskkill /IM go-engine.exe /F 2>nul');
            $this->info('✅ Killed existing engine instance');
        } else {
            // Unix/Mac: use pkill
            $process = new Process(['pkill', '-9', '-f', 'go-engine']);
            $process->run();
            $this->info('✅ Killed existing engine instance');
        }
        
        sleep(1);
        $this->newLine();
    }

    /**
     * Build the Go engine binary
     */
    private function buildEngine($packagePath)
    {
        // Determine which build script to use
        if ($this->isWindows) {
            $buildBat = $packagePath . '/build.bat';
            if (file_exists($buildBat)) {
                // Use batch script on Windows if available
                $process = new Process(['cmd', '/C', $buildBat], $packagePath);
            } else {
                // Fallback to bash if available
                $buildSh = $packagePath . '/build.sh';
                if (!file_exists($buildSh)) {
                    $this->error('❌ Neither build.bat nor build.sh found in: ' . $packagePath);
                    $this->error('Make sure you have the complete LaraGo package installed');
                    return false;
                }
                $process = new Process(['bash', 'build.sh'], $packagePath);
            }
        } else {
            // Unix/Mac: use build.sh
            $buildSh = $packagePath . '/build.sh';
            if (!file_exists($buildSh)) {
                $this->error('❌ build.sh not found at: ' . $buildSh);
                return false;
            }
            $process = new Process(['bash', 'build.sh'], $packagePath);
        }
        
        try {
            $process->mustRun(function ($type, $buffer) {
                echo $buffer;
            });
            
            $this->info('✅ Build completed successfully!');
            return true;
        } catch (\Exception $e) {
            $this->error('❌ Build failed: ' . $e->getMessage());
            if ($this->isWindows) {
                $this->error('Make sure Go is installed and available in your PATH');
                $this->error('Download Go from: https://golang.org/dl/');
            } else {
                $this->error('Make sure Go is installed and available in your PATH');
            }
            return false;
        }
    }
}
