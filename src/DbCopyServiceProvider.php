<?php

namespace ipxDigital\DbCopy;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class DbCopyServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        
    }

    /**
     * Register any package services.
     *
     * @return void
     *
     * @throws \Exception
     */
    public function register()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\CopyCommand::class,
            ]);
        }
    }
}