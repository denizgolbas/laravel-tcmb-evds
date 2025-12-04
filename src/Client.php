<?php

declare(strict_types=1);

namespace Denizgolbas\LaravelTcmbEvds;

use Denizgolbas\LaravelTcmbEvds\Exceptions\ApiException;
use Denizgolbas\LaravelTcmbEvds\Exceptions\InvalidConfigurationException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class Client
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->validateConfig();
    }

    /**
     * Validate configuration
     *
     * @return void
     * @throws InvalidConfigurationException
     */
    protected function validateConfig(): void
    {
        if (empty($this->config['api_key'])) {
            throw new InvalidConfigurationException('TCMB EVDS API key is required. Please set TCMB_EVDS_API_KEY in your .env file.');
        }

        if (empty($this->config['base_endpoint'])) {
            throw new InvalidConfigurationException('TCMB EVDS base endpoint is required.');
        }
    }

    /**
     * Make API request
     *
     * @param string $url
     * @return Response
     * @throws ApiException
     */
    public function request(string $url): Response
    {
        try {
            $headers = [];
            
            // Add API key to headers
            if (! empty($this->config['api_key'])) {
                $headers['key'] = $this->config['api_key'];
            }

            $response = Http::timeout(30)
                ->withHeaders($headers)
                ->get($url);

            if ($response->failed()) {
                throw new ApiException(
                    "API request failed with status {$response->status()}: {$response->body()}"
                );
            }

            return $response;
        } catch (\Exception $e) {
            if ($e instanceof ApiException) {
                throw $e;
            }

            throw new ApiException("API request failed: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Parse API response
     *
     * @param Response $response
     * @return array
     * @throws ApiException
     */
    public function parseResponse(Response $response): array
    {
        $data = $response->json();

        if (! is_array($data)) {
            throw new ApiException('Invalid API response format.');
        }

        // EVDS2 API returns data in a specific format
        // Check if response contains error
        if (isset($data['error'])) {
            throw new ApiException("API error: {$data['error']}");
        }

        return $data;
    }

    /**
     * Get response data
     *
     * @param string $url
     * @return array
     * @throws ApiException
     */
    public function get(string $url): array
    {
        $response = $this->request($url);

        return $this->parseResponse($response);
    }
}

