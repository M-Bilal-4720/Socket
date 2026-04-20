<?php

namespace LaraGo\Socket;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class GoSocketServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Broadcast::extend('larago', function ($app) {
            return new GoBroadcaster();
        });
    }
}
