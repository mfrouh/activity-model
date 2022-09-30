<?php

namespace MFrouh\ActivityModel\Providers;

use Illuminate\Support\ServiceProvider;

class ActivityModelServiceProvider extends ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
    }

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
