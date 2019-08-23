<?php

namespace Potelo\GuPayment;

use Illuminate\Support\ServiceProvider;

class GuPaymentServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'gu-payment');

        $this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/gu-payment'),
        ]);

        $this->publishes([
            __DIR__.'/migrations' => database_path('migrations')
        ], 'migrations');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
