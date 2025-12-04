<?php

declare(strict_types=1);

namespace Denizgolbas\LaravelTcmbEvds;

use Carbon\Carbon;
use Denizgolbas\LaravelTcmbEvds\Exceptions\InvalidDateException;
use Denizgolbas\LaravelTcmbEvds\Exceptions\InvalidConfigurationException;
use Illuminate\Support\Collection;

class Builder
{
    protected array $currencies = [];

    protected ?string $startDate = null;

    protected ?string $endDate = null;

    protected array $fields = [];

    protected string $frequency = 'daily';

    protected array $types = []; // ['buy', 'sell'] veya boş (her ikisi de)

    protected array $marketTypes = []; // ['forex', 'banknote'] veya boş (her ikisi de)

    protected ?string $nullValueHandling = null; // 'previous_day', 'last_week_avg', 'skip'

    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Set currency codes
     *
     * @param array|string $codes
     * @return $this
     */
    public function currency(array|string $codes): self
    {
        $this->currencies = is_array($codes) ? $codes : [$codes];

        return $this;
    }

    /**
     * Set start date
     *
     * @param Carbon|string $date
     * @return $this
     * @throws InvalidDateException
     */
    public function startDate(Carbon|string $date): self
    {
        $this->startDate = $this->formatDate($date);

        return $this;
    }

    /**
     * Set end date
     *
     * @param Carbon|string $date
     * @return $this
     * @throws InvalidDateException
     */
    public function endDate(Carbon|string $date): self
    {
        $this->endDate = $this->formatDate($date);

        return $this;
    }

    /**
     * Set date range to today
     *
     * @return $this
     */
    public function today(): self
    {
        $today = Carbon::today()->format('Y-m-d');

        return $this->startDate($today)->endDate($today);
    }

    /**
     * Set date range to yesterday
     *
     * @return $this
     */
    public function yesterday(): self
    {
        $yesterday = Carbon::yesterday()->format('Y-m-d');

        return $this->startDate($yesterday)->endDate($yesterday);
    }

    /**
     * Set date range to last N days
     *
     * @param int $days
     * @return $this
     */
    public function lastDays(int $days): self
    {
        $endDate = Carbon::today();
        $startDate = Carbon::today()->subDays($days - 1);

        return $this->startDate($startDate->format('Y-m-d'))
            ->endDate($endDate->format('Y-m-d'));
    }

    /**
     * Set date range to this week
     *
     * @return $this
     */
    public function thisWeek(): self
    {
        $startDate = Carbon::now()->startOfWeek();
        $endDate = Carbon::now()->endOfWeek();

        return $this->startDate($startDate->format('Y-m-d'))
            ->endDate($endDate->format('Y-m-d'));
    }

    /**
     * Set date range to last week
     *
     * @return $this
     */
    public function lastWeek(): self
    {
        $startDate = Carbon::now()->subWeek()->startOfWeek();
        $endDate = Carbon::now()->subWeek()->endOfWeek();

        return $this->startDate($startDate->format('Y-m-d'))
            ->endDate($endDate->format('Y-m-d'));
    }

    /**
     * Set date range to this month
     *
     * @return $this
     */
    public function thisMonth(): self
    {
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        return $this->startDate($startDate->format('Y-m-d'))
            ->endDate($endDate->format('Y-m-d'));
    }

    /**
     * Set date range to last month
     *
     * @return $this
     */
    public function lastMonth(): self
    {
        $startDate = Carbon::now()->subMonth()->startOfMonth();
        $endDate = Carbon::now()->subMonth()->endOfMonth();

        return $this->startDate($startDate->format('Y-m-d'))
            ->endDate($endDate->format('Y-m-d'));
    }

    /**
     * Set fields
     *
     * @param array|string $fields
     * @return $this
     */
    public function fields(array|string $fields): self
    {
        $this->fields = is_array($fields) ? $fields : [$fields];

        return $this;
    }

    /**
     * Set frequency (currently only 'daily' is supported)
     *
     * @param string $frequency
     * @return $this
     */
    public function frequency(string $frequency): self
    {
        $this->frequency = $frequency;

        return $this;
    }

    /**
     * Set type (buy, sell, or both)
     *
     * @param array|string $types 'buy', 'sell', or ['buy', 'sell']
     * @return $this
     */
    public function type(array|string $types): self
    {
        $this->types = is_array($types) ? $types : [$types];

        return $this;
    }

    /**
     * Set market type (forex, banknote, or both)
     *
     * @param array|string $marketTypes 'forex', 'banknote', or ['forex', 'banknote']
     * @return $this
     */
    public function marketType(array|string $marketTypes): self
    {
        $this->marketTypes = is_array($marketTypes) ? $marketTypes : [$marketTypes];

        return $this;
    }

    /**
     * Set null value handling strategy
     * 
     * @param string $strategy 'previous_day', 'last_week_avg', or 'skip'
     * @return $this
     */
    public function nullValueHandling(string $strategy): self
    {
        $allowedStrategies = ['previous_day', 'last_week_avg', 'skip'];
        
        if (! in_array($strategy, $allowedStrategies)) {
            throw new \InvalidArgumentException("Invalid null value handling strategy. Allowed: ".implode(', ', $allowedStrategies));
        }

        $this->nullValueHandling = $strategy;

        return $this;
    }

    /**
     * Get null value handling strategy
     *
     * @return string
     */
    public function getNullValueHandling(): string
    {
        return $this->nullValueHandling ?? $this->config['null_value_handling'] ?? 'previous_day';
    }

    /**
     * Build URL
     *
     * @return string
     */
    public function build(): string
    {
        return $this->toUrl();
    }

    /**
     * Build URL
     *
     * @return string
     */
    public function toUrl(): string
    {
        $baseEndpoint = rtrim($this->config['base_endpoint'], '/');
        $params = $this->buildQueryParams();

        return $baseEndpoint.'?'.http_build_query($params);
    }

    /**
     * Build query parameters
     *
     * @return array
     * @throws \Denizgolbas\LaravelTcmbEvds\Exceptions\InvalidConfigurationException
     */
    protected function buildQueryParams(): array
    {
        // Validate API key before building URL
        if (empty($this->config['api_key'])) {
            throw new InvalidConfigurationException(
                'TCMB EVDS API key is required. Please set TCMB_EVDS_API_KEY in your .env file.'
            );
        }

        $params = [
            'series' => $this->buildSeries(),
            'startDate' => $this->formatDateForApi($this->getStartDate()),
            'endDate' => $this->formatDateForApi($this->getEndDate()),
            'type' => 'json',
        ];

        // Note: API key is sent in headers, not in URL parameters
        return array_filter($params);
    }

    /**
     * Build series parameter for API
     * Format: TP.DK.{CURRENCY}.{TYPE}.{MARKET}
     * TYPE: A (buy) or S (sell)
     * MARKET: YTL (forex/Döviz) or EF.YTL (banknote/Efektif)
     *
     * @return string
     */
    public function buildSeries(): string
    {
        $currencies = $this->getCurrenciesForFields();
        $types = $this->getTypesForSeries(); // ['buy', 'sell'] or empty (both)
        $marketTypes = $this->getMarketTypesForSeries(); // ['forex', 'banknote'] or empty (both)

        $series = [];

        foreach ($currencies as $currency) {
            foreach ($types as $type) {
                foreach ($marketTypes as $marketType) {
                    $series[] = $this->buildSeriesCode($currency, $type, $marketType);
                }
            }
        }

        return implode('-', $series);
    }

    /**
     * Build single series code
     *
     * @param string $currency
     * @param string $type buy|sell
     * @param string $marketType forex|banknote
     * @return string
     */
    protected function buildSeriesCode(string $currency, string $type, string $marketType): string
    {
        $typeCode = $type === 'buy' ? 'A' : 'S';
        // forex -> YTL (Döviz), banknote -> EF.YTL (Efektif)
        $marketCode = $marketType === 'forex' ? 'YTL' : 'EF.YTL';

        return "TP.DK.{$currency}.{$typeCode}.{$marketCode}";
    }

    /**
     * Get types (buy/sell) for series building
     *
     * @return array
     */
    protected function getTypesForSeries(): array
    {
        if (! empty($this->types)) {
            return $this->types;
        }

        // Default: both buy and sell
        return ['buy', 'sell'];
    }

    /**
     * Get market types (forex/banknote) for series building
     *
     * @return array
     */
    protected function getMarketTypesForSeries(): array
    {
        if (! empty($this->marketTypes)) {
            return $this->marketTypes;
        }

        // Default: both forex and banknote
        return ['forex', 'banknote'];
    }

    /**
     * Format date for API (DD-MM-YYYY)
     *
     * @param string $date Y-m-d format
     * @return string DD-MM-YYYY format
     */
    protected function formatDateForApi(string $date): string
    {
        $carbon = Carbon::parse($date);

        return $carbon->format('d-m-Y');
    }


    /**
     * Get currencies for field generation
     *
     * @return array
     */
    protected function getCurrenciesForFields(): array
    {
        if (! empty($this->currencies)) {
            return $this->currencies;
        }

        return $this->config['default_currencies'] ?? [];
    }

    /**
     * Get currency code for API
     *
     * @return string
     */
    protected function getCurrencyCode(): string
    {
        if (! empty($this->currencies)) {
            return implode(',', $this->currencies);
        }

        return implode(',', $this->config['default_currencies']);
    }

    /**
     * Get start date
     *
     * @return string
     */
    protected function getStartDate(): string
    {
        if ($this->startDate !== null) {
            return $this->startDate;
        }

        if ($this->config['default_start_date'] !== null) {
            return $this->formatDate($this->config['default_start_date']);
        }

        return Carbon::today()->format('Y-m-d');
    }

    /**
     * Get end date
     *
     * @return string
     */
    protected function getEndDate(): string
    {
        if ($this->endDate !== null) {
            return $this->endDate;
        }

        if ($this->config['default_end_date'] !== null) {
            return $this->formatDate($this->config['default_end_date']);
        }

        return Carbon::today()->format('Y-m-d');
    }

    /**
     * Format date to Y-m-d format
     *
     * @param Carbon|string $date
     * @return string
     * @throws InvalidDateException
     */
    protected function formatDate(Carbon|string $date): string
    {
        if ($date instanceof Carbon) {
            return $date->format('Y-m-d');
        }

        try {
            return Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception $e) {
            throw new InvalidDateException("Invalid date format: {$date}");
        }
    }

    /**
     * Get currencies
     *
     * @return array
     */
    public function getCurrencies(): array
    {
        return $this->currencies;
    }

    /**
     * Get start date
     *
     * @return string|null
     */
    public function getStartDateValue(): ?string
    {
        return $this->startDate;
    }

    /**
     * Get end date
     *
     * @return string|null
     */
    public function getEndDateValue(): ?string
    {
        return $this->endDate;
    }

    /**
     * Get fields
     *
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * Get frequency
     *
     * @return string
     */
    public function getFrequency(): string
    {
        return $this->frequency;
    }

    /**
     * Get types
     *
     * @return array
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    /**
     * Get market types
     *
     * @return array
     */
    public function getMarketTypes(): array
    {
        return $this->marketTypes;
    }

    /**
     * Save data to database
     *
     * @return Collection
     * @throws \Denizgolbas\LaravelTcmbEvds\Exceptions\ApiException
     */
    public function save(): Collection
    {
        $evds = app(Evds::class);

        return $evds->save($this);
    }

    /**
     * Get data from API
     *
     * @return Collection
     * @throws \Denizgolbas\LaravelTcmbEvds\Exceptions\ApiException
     */
    public function get(): Collection
    {
        $evds = app(Evds::class);

        return $evds->get($this);
    }
}

