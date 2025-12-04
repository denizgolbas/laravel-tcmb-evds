<?php

declare(strict_types=1);

namespace Denizgolbas\LaravelTcmbEvds;

use Illuminate\Support\ServiceProvider;

class EvdsServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/evds.php',
            'evds'
        );

        $this->app->singleton(Evds::class, function ($app) {
            $config = $app['config']->get('evds', []);

            return new Evds(
                config: $config,
                builder: new Builder($config),
                client: new Client($config)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/evds.php' => config_path('evds.php'),
        ], 'evds-config');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'evds-migrations');

        $this->publishes([
            __DIR__.'/Models/EvdsCurrencyRate.php' => app_path('Models/EvdsCurrencyRate.php'),
        ], 'evds-model');
    }
}

