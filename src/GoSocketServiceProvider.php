<?php

namespace LaraGo\Socket;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;
use LaraGo\Socket\Console\Commands\LaraGoRunCommand;

class GoSocketServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Broadcast::extend('larago', function ($app) {
            return new GoBroadcaster();
        });

        // Register console commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                LaraGoRunCommand::class,
            ]);
        }
    }
}
