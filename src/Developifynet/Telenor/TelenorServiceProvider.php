<?php

namespace Developifynet\Telenor;

use Illuminate\Support\ServiceProvider;

class TelenorServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('telenor', function () {
            return $this->app->make('Developifynet\Telenor\TelenorSMS');
        });
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {

    }

}
