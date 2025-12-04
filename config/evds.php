<?php

return [
    /*
    |--------------------------------------------------------------------------
    | TCMB EVDS2 API Key
    |--------------------------------------------------------------------------
    |
    | TCMB EVDS2 API'sine erişim için gerekli API anahtarı.
    | API anahtarınızı https://evds2.tcmb.gov.tr adresinden alabilirsiniz.
    |
    */

    'api_key' => env('TCMB_EVDS_API_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Base Endpoint
    |--------------------------------------------------------------------------
    |
    | TCMB EVDS2 API'sinin base endpoint URL'i.
    |
    */

    'base_endpoint' => env('TCMB_EVDS_BASE_ENDPOINT', 'https://evds2.tcmb.gov.tr/service/evds/'),

    /*
    |--------------------------------------------------------------------------
    | Default Currency Codes
    |--------------------------------------------------------------------------
    |
    | Varsayılan olarak kullanılacak döviz kodları.
    | Bu kodlar config üzerinden override edilebilir.
    |
    */

    'default_currencies' => [
        'USD',
        'EUR',
        'GBP',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Date Range
    |--------------------------------------------------------------------------
    |
    | Varsayılan tarih aralığı ayarları.
    | start_date ve end_date null ise, bugünün tarihi kullanılır.
    |
    */

    'default_start_date' => null,
    'default_end_date' => null,

    /*
    |--------------------------------------------------------------------------
    | Default Frequency
    |--------------------------------------------------------------------------
    |
    | Varsayılan veri sıklığı. Şu an için sadece 'daily' desteklenmektedir.
    |
    */

    'default_frequency' => 'daily',

    /*
    |--------------------------------------------------------------------------
    | Null Value Handling
    |--------------------------------------------------------------------------
    |
    | Hafta sonları ve tatil günleri için null değerlerin nasıl işleneceği.
    | 
    | 'previous_day': Bir önceki günün değerini kullan (varsayılan)
    | 'last_week_avg': Geçen haftanın ortalamasını kullan
    | 'skip': Null değerleri atla, kaydetme
    |
    */

    'null_value_handling' => env('TCMB_EVDS_NULL_HANDLING', 'previous_day'),

    /*
    |--------------------------------------------------------------------------
    | Model Configuration
    |--------------------------------------------------------------------------
    |
    | Kullanılacak model sınıfı. İsterseniz kendi modelinizi extend edebilirsiniz.
    |
    */

    'model' => \Denizgolbas\LaravelTcmbEvds\Models\EvdsCurrencyRate::class,
];

