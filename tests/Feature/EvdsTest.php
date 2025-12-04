<?php

declare(strict_types=1);

namespace Denizgolbas\LaravelTcmbEvds\Tests\Feature;

use Denizgolbas\LaravelTcmbEvds\Evds;
use Denizgolbas\LaravelTcmbEvds\Facades\Evds as EvdsFacade;
use Denizgolbas\LaravelTcmbEvds\Models\EvdsCurrencyRate;
use Denizgolbas\LaravelTcmbEvds\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

class EvdsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }

    public function test_facade_can_be_resolved(): void
    {
        $evds = EvdsFacade::getFacadeRoot();

        $this->assertInstanceOf(Evds::class, $evds);
    }

    public function test_currency_method_returns_builder(): void
    {
        $builder = EvdsFacade::currency('USD');

        $this->assertInstanceOf(\Denizgolbas\LaravelTcmbEvds\Builder::class, $builder);
    }

    public function test_can_build_url_with_facade(): void
    {
        $url = EvdsFacade::currency(['USD', 'EUR'])
            ->startDate('2024-01-01')
            ->endDate('2024-01-10')
            ->build();

        $this->assertStringContainsString('https://evds2.tcmb.gov.tr/service/evds', $url);
        $this->assertStringContainsString('series=', $url);
        $this->assertStringContainsString('TP.DK.USD', $url);
        $this->assertStringContainsString('TP.DK.EUR', $url);
    }

    public function test_api_request_with_mock_response(): void
    {
        Http::fake([
            'evds2.tcmb.gov.tr/*' => Http::response([
                'items' => [
                    [
                        'Tarih' => '01-01-2024',
                        'TP_DK_USD_A_YTL' => '30.45',
                        'TP_DK_USD_S_YTL' => '30.50',
                        'TP_DK_EUR_A_YTL' => '33.15',
                        'TP_DK_EUR_S_YTL' => '33.20',
                    ],
                ],
            ], 200),
        ]);

        $data = EvdsFacade::currency(['USD', 'EUR'])
            ->startDate('2024-01-01')
            ->endDate('2024-01-01')
            ->get();

        $this->assertNotEmpty($data);
        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'evds2.tcmb.gov.tr') &&
                   str_contains($request->url(), 'series=') &&
                   str_contains($request->url(), 'TP.DK.USD') &&
                   str_contains($request->url(), 'TP.DK.EUR');
        });
    }

    public function test_api_request_auto_generates_buy_and_sell_fields(): void
    {
        Http::fake([
            'evds2.tcmb.gov.tr/*' => Http::response([
                'items' => [
                    [
                        'Tarih' => '01-01-2024',
                        'TP_DK_USD_A_YTL' => '30.45',
                        'TP_DK_USD_S_YTL' => '30.50',
                        'TP_DK_USD_A_EF_YTL' => '30.40',
                        'TP_DK_USD_S_EF_YTL' => '30.55',
                    ],
                ],
            ], 200),
        ]);

        $data = EvdsFacade::currency('USD')
            ->startDate('2024-01-01')
            ->endDate('2024-01-01')
            ->get();

        $this->assertNotEmpty($data);
        $this->assertGreaterThanOrEqual(2, $data->count()); // Should have both buy and sell
        
        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'TP.DK.USD.A.YTL') &&
                   str_contains($request->url(), 'TP.DK.USD.S.YTL');
        });
    }

    public function test_save_method_saves_to_database(): void
    {
        Http::fake([
            'evds2.tcmb.gov.tr/*' => Http::response([
                'items' => [
                    [
                        'Tarih' => '01-01-2024',
                        'TP_DK_USD_S_YTL' => '30.50',
                    ],
                ],
            ], 200),
        ]);

        EvdsFacade::currency('USD')
            ->startDate('2024-01-01')
            ->endDate('2024-01-01')
            ->type('sell')
            ->marketType('forex')
            ->save();

        $this->assertDatabaseHas('evds_currency_rates', [
            'code' => 'USD',
            'type' => 'sell',
            'market_type' => 'forex',
            'date' => '2024-01-01',
        ]);
    }

    public function test_save_method_updates_existing_record(): void
    {
        EvdsCurrencyRate::create([
            'code' => 'USD',
            'type' => 'sell',
            'market_type' => 'forex',
            'rate' => 30.00,
            'date' => '2024-01-01',
            'meta' => [],
        ]);

        Http::fake([
            'evds2.tcmb.gov.tr/*' => Http::response([
                'items' => [
                    [
                        'Tarih' => '01-01-2024',
                        'TP_DK_USD_S_YTL' => '30.50',
                    ],
                ],
            ], 200),
        ]);

        EvdsFacade::currency('USD')
            ->startDate('2024-01-01')
            ->endDate('2024-01-01')
            ->type('sell')
            ->marketType('forex')
            ->save();

        $this->assertDatabaseHas('evds_currency_rates', [
            'code' => 'USD',
            'type' => 'sell',
            'market_type' => 'forex',
            'date' => '2024-01-01',
            'rate' => 30.50,
        ]);

        $this->assertEquals(1, EvdsCurrencyRate::where('code', 'USD')
            ->where('type', 'sell')
            ->where('market_type', 'forex')
            ->count());
    }
}

