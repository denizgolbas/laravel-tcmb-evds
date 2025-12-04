<?php

declare(strict_types=1);

namespace Denizgolbas\LaravelTcmbEvds\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Denizgolbas\LaravelTcmbEvds\Builder currency(array|string $codes)
 * @method static \Illuminate\Support\Collection get(?\Denizgolbas\LaravelTcmbEvds\Builder $builder = null)
 * @method static \Illuminate\Support\Collection save(?\Denizgolbas\LaravelTcmbEvds\Builder $builder = null)
 * @method static \Denizgolbas\LaravelTcmbEvds\Builder newBuilder()
 *
 * @see \Denizgolbas\LaravelTcmbEvds\Evds
 */
class Evds extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return \Denizgolbas\LaravelTcmbEvds\Evds::class;
    }
}

