<?php

namespace LaraGo\Socket;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use LaraGo\Socket\Console\Commands\LaraGoRunCommand;
use LaraGo\Socket\Console\Commands\LaraGoStopCommand;
use LaraGo\Socket\Console\Commands\LaraGoGenerateTokenCommand;

class GoSocketServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Broadcast::extend('larago', function ($app) {
            return new GoBroadcaster();
        });

        $this->registerTestRoutes();

        // Register console commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                LaraGoRunCommand::class,
                LaraGoStopCommand::class,
                LaraGoGenerateTokenCommand::class,
            ]);
        }
    }

    private function registerTestRoutes(): void
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        $testPage = __DIR__ . '/../public/larago-test.html';

        if (!file_exists($testPage)) {
            return;
        }

        Route::middleware('web')->group(function () use ($testPage) {
            Route::get('/larago-test', static function () use ($testPage) {
                return response()->file($testPage);
            });

            Route::get('/larago-test.html', static function () use ($testPage) {
                return response()->file($testPage);
            });
        });
    }
}
