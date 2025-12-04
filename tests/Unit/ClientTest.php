<?php

declare(strict_types=1);

namespace Denizgolbas\LaravelTcmbEvds\Tests\Unit;

use Denizgolbas\LaravelTcmbEvds\Client;
use Denizgolbas\LaravelTcmbEvds\Exceptions\ApiException;
use Denizgolbas\LaravelTcmbEvds\Exceptions\InvalidConfigurationException;
use Denizgolbas\LaravelTcmbEvds\Tests\TestCase;
use Illuminate\Support\Facades\Http;

class ClientTest extends TestCase
{
    public function test_client_throws_exception_when_api_key_is_missing(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('TCMB EVDS API key is required');

        new Client([
            'api_key' => '',
            'base_endpoint' => 'https://evds2.tcmb.gov.tr/service/evds/',
        ]);
    }

    public function test_client_throws_exception_when_base_endpoint_is_missing(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('TCMB EVDS base endpoint is required');

        new Client([
            'api_key' => 'test-key',
            'base_endpoint' => '',
        ]);
    }

    public function test_client_makes_successful_request(): void
    {
        Http::fake([
            'evds2.tcmb.gov.tr/*' => Http::response([
                'items' => [
                    ['Tarih' => '01-01-2024', 'TP_DK_USD_S_YTL' => '30.50'],
                ],
            ], 200),
        ]);

        $client = new Client([
            'api_key' => 'test-key',
            'base_endpoint' => 'https://evds2.tcmb.gov.tr/service/evds/',
        ]);

        $data = $client->get('https://evds2.tcmb.gov.tr/service/evds/?series=TP.DK.USD.S.YTL&startDate=01-01-2024&endDate=01-01-2024&type=json');

        $this->assertIsArray($data);
        $this->assertArrayHasKey('items', $data);
        
        // Verify API key is sent in headers
        Http::assertSent(function ($request) {
            return $request->hasHeader('key', 'test-key');
        });
    }

    public function test_client_throws_exception_on_failed_request(): void
    {
        Http::fake([
            'evds2.tcmb.gov.tr/*' => Http::response([], 500),
        ]);

        $client = new Client([
            'api_key' => 'test-key',
            'base_endpoint' => 'https://evds2.tcmb.gov.tr/service/evds/',
        ]);

        $this->expectException(ApiException::class);

        $client->get('https://evds2.tcmb.gov.tr/service/evds/?series=TP.DK.USD.S.YTL&type=json');
    }

    public function test_client_throws_exception_on_invalid_response(): void
    {
        Http::fake([
            'evds2.tcmb.gov.tr/*' => Http::response('invalid json', 200),
        ]);

        $client = new Client([
            'api_key' => 'test-key',
            'base_endpoint' => 'https://evds2.tcmb.gov.tr/service/evds/',
        ]);

        $this->expectException(ApiException::class);

        $client->get('https://evds2.tcmb.gov.tr/service/evds/?series=TP.DK.USD.S.YTL&type=json');
    }

    public function test_client_throws_exception_on_api_error_response(): void
    {
        Http::fake([
            'evds2.tcmb.gov.tr/*' => Http::response([
                'error' => 'Invalid API key',
            ], 200),
        ]);

        $client = new Client([
            'api_key' => 'test-key',
            'base_endpoint' => 'https://evds2.tcmb.gov.tr/service/evds/',
        ]);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('API error: Invalid API key');

        $client->get('https://evds2.tcmb.gov.tr/service/evds/?series=TP.DK.USD.S.YTL&type=json');
    }
}

