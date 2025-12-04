<?php

declare(strict_types=1);

namespace Denizgolbas\LaravelTcmbEvds\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $code
 * @property string $type buy|sell (Buying/Selling)
 * @property string $market_type forex|banknote (Forex/Banknote)
 * @property float $rate
 * @property string $date
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class EvdsCurrencyRate extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'evds_currency_rates';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'type',
        'market_type',
        'rate',
        'date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'rate' => 'float',
        'date' => 'date',
    ];

    /**
     * Get the currency code
     *
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * Get the rate
     *
     * @return float
     */
    public function getRate(): float
    {
        return $this->rate;
    }

    /**
     * Get the date
     *
     * @return Carbon
     */
    public function getDate(): Carbon
    {
        return $this->date;
    }

    /**
     * Get the type (buy or sell)
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get the market type (market or effective)
     *
     * @return string
     */
    public function getMarketType(): string
    {
        return $this->market_type;
    }

    /**
     * Scope for buy rates
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBuy($query)
    {
        return $query->where('type', 'buy');
    }

    /**
     * Scope for sell rates
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSell($query)
    {
        return $query->where('type', 'sell');
    }

    /**
     * Scope for forex rates (Döviz)
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForex($query)
    {
        return $query->where('market_type', 'forex');
    }

    /**
     * Scope for banknote rates (Efektif)
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBanknote($query)
    {
        return $query->where('market_type', 'banknote');
    }

    /**
     * Scope for market rates (alias for forex - backward compatibility)
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeMarket($query)
    {
        return $query->where('market_type', 'forex');
    }

    /**
     * Scope for effective rates (alias for banknote - backward compatibility)
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEffective($query)
    {
        return $query->where('market_type', 'banknote');
    }
}

