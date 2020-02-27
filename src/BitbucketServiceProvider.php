<?php

namespace CoreAlg;

use Illuminate\Support\ServiceProvider;

class BitbucketServiceProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config' => config_path(''),
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    { }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    { }
}