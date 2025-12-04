<?php

declare(strict_types=1);

namespace Denizgolbas\LaravelTcmbEvds\Tests;

use Denizgolbas\LaravelTcmbEvds\EvdsServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app): array
    {
        return [
            EvdsServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('evds.api_key', 'test-api-key');
        $app['config']->set('evds.base_endpoint', 'https://evds2.tcmb.gov.tr/service/evds/');
        $app['config']->set('evds.null_value_handling', 'previous_day');
        $app['config']->set('evds.default_currencies', ['USD', 'EUR']);
    }
}

