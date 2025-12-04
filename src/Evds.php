<?php

declare(strict_types=1);

namespace Denizgolbas\LaravelTcmbEvds;

use Carbon\Carbon;
use Denizgolbas\LaravelTcmbEvds\Exceptions\ApiException;
use Illuminate\Support\Collection;

class Evds
{
    protected Builder $builder;

    protected Client $client;

    protected array $config;

    public function __construct(array $config, Builder $builder, Client $client)
    {
        $this->config = $config;
        $this->builder = $builder;
        $this->client = $client;
    }

    /**
     * Create new builder instance
     *
     * @return Builder
     */
    public function newBuilder(): Builder
    {
        return new Builder($this->config);
    }

    /**
     * Set currency codes
     *
     * @param array|string $codes
     * @return Builder
     */
    public function currency(array|string $codes): Builder
    {
        return $this->newBuilder()->currency($codes);
    }

    /**
     * Get data from API
     *
     * @param Builder|null $builder
     * @return Collection
     * @throws ApiException
     */
    public function get(?Builder $builder = null): Collection
    {
        $builder = $builder ?? $this->newBuilder();

        $url = $builder->toUrl();
        $data = $this->client->get($url);

        return $this->transformResponse($data, $builder);
    }

    /**
     * Save data to database
     *
     * @param Builder|null $builder
     * @return Collection
     * @throws ApiException
     */
    public function save(?Builder $builder = null): Collection
    {
        $data = $this->get($builder);

        $modelClass = $this->config['model'];

        $saved = collect();

        foreach ($data as $item) {
            // Skip empty or invalid data
            if (empty($item->code) || $item->rate <= 0 || $item->date === null) {
                continue;
            }

            $model = $modelClass::updateOrCreate(
                [
                    'code' => $item->code,
                    'type' => $item->type,
                    'market_type' => $item->marketType,
                    'date' => $item->date,
                ],
                [
                    'rate' => $item->rate,
                    'meta' => $item->meta,
                ]
            );

            $saved->push($model);
        }

        return $saved;
    }

    /**
     * Transform API response to ResponseData collection
     *
     * @param array $data
     * @param Builder $builder
     * @return Collection
     */
    protected function transformResponse(array $data, Builder $builder): Collection
    {
        $results = collect();

        // EVDS2 API response structure with series parameter
        // Response format: { "items": [{ "Tarih": "01-01-2024", "TP_DK_USD_A_YTL": "30.50", ... }] }

        if (! isset($data['items']) || ! is_array($data['items'])) {
            // Try alternative response formats
            if (isset($data[0]) && is_array($data[0])) {
                $items = $data;
            } else {
                return $results;
            }
        } else {
            $items = $data['items'];
        }

        $series = $builder->buildSeries();
        $seriesArray = explode('-', $series);
        $nullHandling = $builder->getNullValueHandling();

        // Store rates for null handling strategies
        $rateCache = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $date = $item['Tarih'] ?? $item['date'] ?? $builder->getStartDateValue() ?? date('Y-m-d');
            
            // Parse date format (DD-MM-YYYY to Y-m-d)
            try {
                $dateCarbon = \Carbon\Carbon::createFromFormat('d-m-Y', $date);
            } catch (\Exception $e) {
                try {
                    $dateCarbon = \Carbon\Carbon::parse($date);
                } catch (\Exception $e2) {
                    continue;
                }
            }

            // Parse each series code
            foreach ($seriesArray as $seriesCode) {
                // Convert series code to response key
                // TP.DK.USD.A.YTL -> TP_DK_USD_A_YTL
                // TP.DK.USD.A.EF.YTL -> TP_DK_USD_A_EF_YTL
                $responseKey = str_replace('.', '_', $seriesCode);
                
                // Extract information from series code
                if (! preg_match('/TP\.DK\.([A-Z]{3})\.([AS])\.(YTL|EF\.YTL)/', $seriesCode, $matches)) {
                    continue;
                }

                $currency = $matches[1];
                $typeCode = $matches[2];
                $marketCode = $matches[3];

                $type = $typeCode === 'A' ? 'buy' : 'sell';
                $marketType = $marketCode === 'YTL' ? 'forex' : 'banknote';

                // Get rate value (can be null for weekends/holidays)
                $rateValue = $item[$responseKey] ?? null;

                // Handle null values
                if ($rateValue === null || $rateValue === '' || $rateValue === 'null') {
                    $rateValue = $this->handleNullValue(
                        $currency,
                        $type,
                        $marketType,
                        $dateCarbon,
                        $nullHandling,
                        $rateCache,
                        $results
                    );

                    // If skip strategy and no value found, continue to next
                    if ($nullHandling === 'skip' && $rateValue === null) {
                        continue;
                    }
                }

                // If still null after handling, skip
                if ($rateValue === null) {
                    continue;
                }

                $cacheKey = "{$currency}_{$type}_{$marketType}";
                $rateCache[$cacheKey][$dateCarbon->format('Y-m-d')] = (float) $rateValue;

                $results->push(new ResponseData(
                    code: $currency,
                    rate: (float) $rateValue,
                    date: $dateCarbon,
                    type: $type,
                    marketType: $marketType,
                    meta: [
                        'series_code' => $seriesCode,
                        'response_key' => $responseKey,
                        'original_data' => $item,
                        'is_null_handled' => ! isset($item[$responseKey]) || $item[$responseKey] === null,
                    ]
                ));
            }
        }

        return $results;
    }

    /**
     * Handle null values based on strategy
     *
     * @param string $currency
     * @param string $type
     * @param string $marketType
     * @param Carbon $date
     * @param string $strategy
     * @param array $rateCache
     * @param Collection $allResults
     * @return float|null
     */
    protected function handleNullValue(
        string $currency,
        string $type,
        string $marketType,
        Carbon $date,
        string $strategy,
        array &$rateCache,
        Collection $allResults
    ): ?float {
        $cacheKey = "{$currency}_{$type}_{$marketType}";

        switch ($strategy) {
            case 'previous_day':
                // Try to find previous day's rate from cache
                $maxAttempts = 7; // Try up to 7 days back

                for ($i = 1; $i <= $maxAttempts; $i++) {
                    $checkDate = $date->copy()->subDays($i);
                    $dateKey = $checkDate->format('Y-m-d');

                    // Check cache first
                    if (isset($rateCache[$cacheKey][$dateKey]) && $rateCache[$cacheKey][$dateKey] > 0) {
                        return $rateCache[$cacheKey][$dateKey];
                    }

                    // Check allResults for non-null values
                    $found = $allResults->first(function ($item) use ($currency, $type, $marketType, $checkDate) {
                        return $item->code === $currency
                            && $item->type === $type
                            && $item->marketType === $marketType
                            && $item->date->format('Y-m-d') === $checkDate->format('Y-m-d')
                            && (! isset($item->meta['is_null']) || ! $item->meta['is_null'])
                            && $item->rate > 0;
                    });

                    if ($found && $found->rate > 0) {
                        return $found->rate;
                    }
                }

                return null;

            case 'last_week_avg':
                // Calculate average of last week (same day of week)
                $lastWeekStart = $date->copy()->subWeek()->startOfWeek();
                $lastWeekEnd = $date->copy()->subWeek()->endOfWeek();

                $rates = [];
                for ($d = $lastWeekStart->copy(); $d <= $lastWeekEnd; $d->addDay()) {
                    $dateKey = $d->format('Y-m-d');

                    // Check cache
                    if (isset($rateCache[$cacheKey][$dateKey]) && $rateCache[$cacheKey][$dateKey] > 0) {
                        $rates[] = $rateCache[$cacheKey][$dateKey];
                        continue;
                    }

                    // Check allResults for non-null values
                    $found = $allResults->first(function ($item) use ($currency, $type, $marketType, $d) {
                        return $item->code === $currency
                            && $item->type === $type
                            && $item->marketType === $marketType
                            && $item->date->format('Y-m-d') === $d->format('Y-m-d')
                            && (! isset($item->meta['is_null']) || ! $item->meta['is_null'])
                            && $item->rate > 0;
                    });

                    if ($found && $found->rate > 0) {
                        $rates[] = $found->rate;
                    }
                }

                if (count($rates) > 0) {
                    return array_sum($rates) / count($rates);
                }

                return null;

            case 'skip':
            default:
                return null;
        }
    }

}

