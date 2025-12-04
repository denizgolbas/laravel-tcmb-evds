<?php

declare(strict_types=1);

namespace Denizgolbas\LaravelTcmbEvds;

use Carbon\Carbon;

class ResponseData
{
    public function __construct(
        public readonly string $code,
        public readonly float $rate,
        public readonly Carbon $date,
        public readonly string $type = 'sell', // buy|sell (Buying/Selling)
        public readonly string $marketType = 'forex', // forex|banknote (Forex/Banknote)
        public readonly array $meta = []
    ) {
    }

    /**
     * Create from array
     *
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            code: $data['code'] ?? '',
            rate: (float) ($data['rate'] ?? 0),
            date: Carbon::parse($data['date'] ?? 'now'),
            type: $data['type'] ?? 'sell',
            marketType: $data['market_type'] ?? 'forex',
            meta: $data['meta'] ?? []
        );
    }

    /**
     * Convert to array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'rate' => $this->rate,
            'date' => $this->date->format('Y-m-d'),
            'type' => $this->type,
            'market_type' => $this->marketType,
            'meta' => $this->meta,
        ];
    }
}

