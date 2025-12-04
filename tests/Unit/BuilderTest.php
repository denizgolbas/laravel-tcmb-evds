<?php

declare(strict_types=1);

namespace Denizgolbas\LaravelTcmbEvds\Tests\Unit;

use Carbon\Carbon;
use Denizgolbas\LaravelTcmbEvds\Builder;
use Denizgolbas\LaravelTcmbEvds\Tests\TestCase;

class BuilderTest extends TestCase
{
    protected function getConfig(): array
    {
        return [
            'api_key' => 'test-api-key',
            'base_endpoint' => 'https://evds2.tcmb.gov.tr/service/evds/',
            'default_currencies' => ['USD', 'EUR'],
            'default_start_date' => null,
            'default_end_date' => null,
            'default_frequency' => 'daily',
            'null_value_handling' => 'previous_day',
        ];
    }

    public function test_currency_method_sets_single_currency(): void
    {
        $builder = new Builder($this->getConfig());
        $builder->currency('USD');

        $this->assertEquals(['USD'], $builder->getCurrencies());
    }

    public function test_currency_method_sets_multiple_currencies(): void
    {
        $builder = new Builder($this->getConfig());
        $builder->currency(['USD', 'EUR', 'GBP']);

        $this->assertEquals(['USD', 'EUR', 'GBP'], $builder->getCurrencies());
    }

    public function test_start_date_and_end_date_methods(): void
    {
        $builder = new Builder($this->getConfig());
        $builder->startDate('2024-01-01')->endDate('2024-01-10');

        $this->assertEquals('2024-01-01', $builder->getStartDateValue());
        $this->assertEquals('2024-01-10', $builder->getEndDateValue());
    }

    public function test_start_date_and_end_date_with_carbon(): void
    {
        $builder = new Builder($this->getConfig());
        $startDate = Carbon::parse('2024-01-01');
        $endDate = Carbon::parse('2024-01-10');

        $builder->startDate($startDate)->endDate($endDate);

        $this->assertEquals('2024-01-01', $builder->getStartDateValue());
        $this->assertEquals('2024-01-10', $builder->getEndDateValue());
    }

    public function test_today_method(): void
    {
        $builder = new Builder($this->getConfig());
        $builder->today();

        $today = Carbon::today()->format('Y-m-d');
        $this->assertEquals($today, $builder->getStartDateValue());
        $this->assertEquals($today, $builder->getEndDateValue());
    }

    public function test_yesterday_method(): void
    {
        $builder = new Builder($this->getConfig());
        $builder->yesterday();

        $yesterday = Carbon::yesterday()->format('Y-m-d');
        $this->assertEquals($yesterday, $builder->getStartDateValue());
        $this->assertEquals($yesterday, $builder->getEndDateValue());
    }

    public function test_last_days_method(): void
    {
        $builder = new Builder($this->getConfig());
        $builder->lastDays(7);

        $endDate = Carbon::today()->format('Y-m-d');
        $startDate = Carbon::today()->subDays(6)->format('Y-m-d');

        $this->assertEquals($startDate, $builder->getStartDateValue());
        $this->assertEquals($endDate, $builder->getEndDateValue());
    }

    public function test_this_week_method(): void
    {
        $builder = new Builder($this->getConfig());
        $builder->thisWeek();

        $startDate = Carbon::now()->startOfWeek()->format('Y-m-d');
        $endDate = Carbon::now()->endOfWeek()->format('Y-m-d');

        $this->assertEquals($startDate, $builder->getStartDateValue());
        $this->assertEquals($endDate, $builder->getEndDateValue());
    }

    public function test_last_week_method(): void
    {
        $builder = new Builder($this->getConfig());
        $builder->lastWeek();

        $startDate = Carbon::now()->subWeek()->startOfWeek()->format('Y-m-d');
        $endDate = Carbon::now()->subWeek()->endOfWeek()->format('Y-m-d');

        $this->assertEquals($startDate, $builder->getStartDateValue());
        $this->assertEquals($endDate, $builder->getEndDateValue());
    }

    public function test_this_month_method(): void
    {
        $builder = new Builder($this->getConfig());
        $builder->thisMonth();

        $startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = Carbon::now()->endOfMonth()->format('Y-m-d');

        $this->assertEquals($startDate, $builder->getStartDateValue());
        $this->assertEquals($endDate, $builder->getEndDateValue());
    }

    public function test_last_month_method(): void
    {
        $builder = new Builder($this->getConfig());
        $builder->lastMonth();

        $startDate = Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d');
        $endDate = Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d');

        $this->assertEquals($startDate, $builder->getStartDateValue());
        $this->assertEquals($endDate, $builder->getEndDateValue());
    }

    public function test_fields_method(): void
    {
        $builder = new Builder($this->getConfig());
        $builder->fields(['TP_DK_USD_S', 'TP_DK_EUR_S']);

        $this->assertEquals(['TP_DK_USD_S', 'TP_DK_EUR_S'], $builder->getFields());
    }

    public function test_fields_method_with_single_field(): void
    {
        $builder = new Builder($this->getConfig());
        $builder->fields('TP_DK_USD_S');

        $this->assertEquals(['TP_DK_USD_S'], $builder->getFields());
    }

    public function test_frequency_method(): void
    {
        $builder = new Builder($this->getConfig());
        $builder->frequency('daily');

        $this->assertEquals('daily', $builder->getFrequency());
    }

    public function test_build_url_includes_all_parameters(): void
    {
        $builder = new Builder($this->getConfig());
        $url = $builder->currency(['USD', 'EUR'])
            ->startDate('2024-01-01')
            ->endDate('2024-01-10')
            ->type('sell')
            ->marketType('forex')
            ->build();

        // API key is sent in headers, not in URL
        $this->assertStringNotContainsString('key=', $url);
        $this->assertStringContainsString('series=', $url);
        $this->assertStringContainsString('TP.DK.USD.S.YTL', $url);
        $this->assertStringContainsString('TP.DK.EUR.S.YTL', $url);
        $this->assertStringContainsString('startDate=01-01-2024', $url);
        $this->assertStringContainsString('endDate=10-01-2024', $url);
        $this->assertStringContainsString('type=json', $url);
    }

    public function test_build_url_auto_generates_buy_and_sell_fields_when_not_specified(): void
    {
        $builder = new Builder($this->getConfig());
        $url = $builder->currency('USD')
            ->startDate('2024-01-01')
            ->endDate('2024-01-01')
            ->build();

        // Should automatically include both buy (A) and sell (S) fields for forex and banknote
        $this->assertStringContainsString('TP.DK.USD.A.YTL', $url);
        $this->assertStringContainsString('TP.DK.USD.S.YTL', $url);
        $this->assertStringContainsString('TP.DK.USD.A.EF.YTL', $url);
        $this->assertStringContainsString('TP.DK.USD.S.EF.YTL', $url);
    }

    public function test_build_url_auto_generates_fields_for_multiple_currencies(): void
    {
        $builder = new Builder($this->getConfig());
        $url = $builder->currency(['USD', 'EUR'])
            ->today()
            ->build();

        // Should automatically include both buy and sell fields for each currency (forex and banknote)
        $this->assertStringContainsString('TP.DK.USD.A.YTL', $url);
        $this->assertStringContainsString('TP.DK.USD.S.YTL', $url);
        $this->assertStringContainsString('TP.DK.EUR.A.YTL', $url);
        $this->assertStringContainsString('TP.DK.EUR.S.YTL', $url);
    }

    public function test_build_url_uses_explicit_type_and_market_type(): void
    {
        $builder = new Builder($this->getConfig());
        $url = $builder->currency('USD')
            ->today()
            ->type('sell')
            ->marketType('forex')
            ->build();

        // Should only include sell forex
        $this->assertStringContainsString('TP.DK.USD.S.YTL', $url);
        $this->assertStringNotContainsString('TP.DK.USD.A.YTL', $url);
        $this->assertStringNotContainsString('EF.YTL', $url);
    }

    public function test_to_url_is_alias_of_build(): void
    {
        $builder = new Builder($this->getConfig());
        $url1 = $builder->currency('USD')->today()->build();
        $url2 = $builder->currency('USD')->today()->toUrl();

        $this->assertEquals($url1, $url2);
    }
}

