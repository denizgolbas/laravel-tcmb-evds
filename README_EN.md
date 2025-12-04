# Laravel TCMB EVDS2 Daily Exchange Rates Package

🇬🇧 **English** | [🇹🇷 Türkçe](README.md)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/denizgolbas/laravel-tcmb-evds.svg?style=flat-square)](https://packagist.org/packages/denizgolbas/laravel-tcmb-evds)
[![Tests](https://github.com/denizgolbas/laravel-tcmb-evds/actions/workflows/tests.yml/badge.svg)](https://github.com/denizgolbas/laravel-tcmb-evds/actions/workflows/tests.yml)

A modern Laravel package specifically developed for TCMB EVDS2 API's "Daily Exchange Rates (Daily FX Rates)" service.

The package supports a chainable URL/Query Builder, flexible API client, publishable migration + model structure, and optional database storage of retrieved data.

This package is designed for Laravel developers who want to easily fetch, process, store, and automate daily exchange rates within their projects.

## ⚠️ IMPORTANT DISCLAIMER

**This package is a tool created to connect to the official TCMB EVDS2 API and easily process exchange rates. It is not an official distribution.**

**You are responsible for any financial errors and losses that may occur during use.**

- This package is not officially supported by TCMB
- Always verify before using in financial transactions
- Use official TCMB sources for critical financial decisions
- Package developers are not responsible for any damages arising from package usage

## 🚀 Features

- 🔗 **Fluent URL Builder** - Easily create TCMB EVDS2 endpoints with chainable functions
- 📅 **Ready Date Shortcuts** - Quick date ranges with functions like `today()`, `yesterday()`, `lastDays(7)`, `thisMonth()`
- 💾 **Optional Database Storage** - Save exchange rates from API to database using models
- ⚙️ **Publishable Model and Migration** - Customize model/migration files in your project if needed
- 🧩 **Config-Based Flexibility** - Manage default currencies, date settings, and API key through config
- 🎯 **Buy/Sell and Forex/Banknote Separation** - Fetch forex buying, forex selling, banknote buying, banknote selling rates separately
- 🔄 **Null Value Management** - Flexible null value handling strategies for weekends and holidays
- 🧪 **Unit, Feature, and Matrix Tests** - GitHub Actions test workflow for Laravel 10/11 + PHP 8.2/8.3 combinations
- 📡 **API Communication with Laravel Http Client** - Clean, secure, and performant API client

## 📦 Installation

Install the package via Composer:

```bash
composer require denizgolbas/laravel-tcmb-evds
```

### Publishing Config File

To publish the config file:

```bash
php artisan vendor:publish --tag=evds-config
```

This creates the `config/evds.php` file. Add your API key to your `.env` file:

```env
TCMB_EVDS_API_KEY=your-api-key-here
TCMB_EVDS_BASE_ENDPOINT=https://evds2.tcmb.gov.tr/service/evds/
TCMB_EVDS_NULL_HANDLING=previous_day
```

**Important:** You can obtain your API key from [TCMB EVDS2 User Documentation](https://evds2.tcmb.gov.tr/index.php?/evds/userDocs).

### Publishing Migrations

To publish migration files:

```bash
php artisan vendor:publish --tag=evds-migrations
```

Then run the migrations:

```bash
php artisan migrate
```

### Publishing Model

To publish the model file:

```bash
php artisan vendor:publish --tag=evds-model
```

This creates the `app/Models/EvdsCurrencyRate.php` file. You can extend and customize this model if needed.

## 🛠️ Usage Examples

### Basic Usage

```php
use Denizgolbas\LaravelTcmbEvds\Facades\Evds;

// Fetch single currency rate (automatically fetches both buy and sell, both forex and banknote)
$data = Evds::currency('USD')
    ->today()
    ->get();

// Fetch multiple currency rates
$data = Evds::currency(['USD', 'EUR'])
    ->startDate('2024-01-01')
    ->endDate('2024-01-10')
    ->get();
```

### Buy/Sell Selection

```php
// Only selling rates (Forex Selling)
$data = Evds::currency('USD')
    ->startDate('2024-01-01')
    ->endDate('2024-01-10')
    ->type('sell')
    ->get();

// Only buying rates (Forex Buying)
$data = Evds::currency('USD')
    ->startDate('2024-01-01')
    ->endDate('2024-01-10')
    ->type('buy')
    ->get();

// Both buy and sell (default)
$data = Evds::currency('USD')
    ->startDate('2024-01-01')
    ->endDate('2024-01-10')
    ->get();
```

### Forex/Banknote Selection

```php
// Only Forex rates
$data = Evds::currency('USD')
    ->startDate('2024-01-01')
    ->endDate('2024-01-10')
    ->marketType('forex')
    ->get();

// Only Banknote rates
$data = Evds::currency('USD')
    ->startDate('2024-01-01')
    ->endDate('2024-01-10')
    ->marketType('banknote')
    ->get();

// Both forex and banknote (default)
$data = Evds::currency('USD')
    ->startDate('2024-01-01')
    ->endDate('2024-01-10')
    ->get();
```

### Combination Examples

```php
// Forex Selling
$data = Evds::currency(['USD', 'EUR'])
    ->startDate('2024-01-01')
    ->endDate('2024-01-10')
    ->type('sell')
    ->marketType('forex')
    ->get();

// Banknote Buying
$data = Evds::currency('USD')
    ->startDate('2024-01-01')
    ->endDate('2024-01-10')
    ->type('buy')
    ->marketType('banknote')
    ->get();

// Banknote Selling
$data = Evds::currency('EUR')
    ->today()
    ->type('sell')
    ->marketType('banknote')
    ->get();
```

### Date Shortcuts

```php
// Today
$data = Evds::currency('USD')->today()->get();

// Yesterday
$data = Evds::currency('USD')->yesterday()->get();

// Last 7 days
$data = Evds::currency('USD')->lastDays(7)->get();

// This week
$data = Evds::currency('USD')->thisWeek()->get();

// Last week
$data = Evds::currency('USD')->lastWeek()->get();

// This month
$data = Evds::currency('USD')->thisMonth()->get();

// Last month
$data = Evds::currency('USD')->lastMonth()->get();
```

### Null Value Management (Weekends and Holidays)

```php
// Use previous day's value (default)
$data = Evds::currency('USD')
    ->startDate('2024-01-01')
    ->endDate('2024-01-10')
    ->nullValueHandling('previous_day')
    ->get();

// Use last week's average
$data = Evds::currency('USD')
    ->startDate('2024-01-01')
    ->endDate('2024-01-10')
    ->nullValueHandling('last_week_avg')
    ->get();

// Skip null values (don't save)
$data = Evds::currency('USD')
    ->startDate('2024-01-01')
    ->endDate('2024-01-10')
    ->nullValueHandling('skip')
    ->get();
```

### Saving to Database

```php
// Simple save
$saved = Evds::currency('USD')
    ->today()
    ->save();

// Combination save
$saved = Evds::currency(['USD', 'EUR'])
    ->startDate('2024-01-01')
    ->endDate('2024-01-10')
    ->type('sell')
    ->marketType('forex')
    ->nullValueHandling('previous_day')
    ->roundDecimals(2) // Round to 2 decimal places
    ->save();

// Using saved data
foreach ($saved as $rate) {
    echo "{$rate->code}: {$rate->rate} ({$rate->date->format('Y-m-d')})\n";
    echo "Type: {$rate->type}, Market: {$rate->marketType}\n";
}
```

### URL Building

```php
// To see the URL
$url = Evds::currency(['USD', 'EUR'])
    ->startDate('2024-01-01')
    ->endDate('2024-01-10')
    ->type('sell')
    ->marketType('forex')
    ->build();

echo $url;
// Output: https://evds2.tcmb.gov.tr/service/evds/?series=TP.DK.USD.S.YTL-TP.DK.EUR.S.YTL&startDate=01-01-2024&endDate=10-01-2024&type=json&key=...
```

### Fetching Data from Database Using Model

```php
use Denizgolbas\LaravelTcmbEvds\Models\EvdsCurrencyRate;
use Carbon\Carbon;

// Today's USD rate
$rate = EvdsCurrencyRate::where('code', 'USD')
    ->where('date', Carbon::today())
    ->first();

// Rates for a specific date range
$rates = EvdsCurrencyRate::where('code', 'USD')
    ->whereBetween('date', ['2024-01-01', '2024-01-10'])
    ->get();

// Only selling rates
$sellRates = EvdsCurrencyRate::where('code', 'USD')
    ->sell()
    ->get();

// Only forex rates
$forexRates = EvdsCurrencyRate::where('code', 'USD')
    ->forex()
    ->get();

// Combination: USD Forex Selling
$usdForexSell = EvdsCurrencyRate::where('code', 'USD')
    ->sell()
    ->forex()
    ->whereBetween('date', ['2024-01-01', '2024-01-10'])
    ->orderBy('date', 'asc')
    ->get();

// Latest saved USD rate
$latestUsd = EvdsCurrencyRate::where('code', 'USD')
    ->latest('date')
    ->first();

// Latest rates for each currency
$latestRates = EvdsCurrencyRate::whereIn('code', ['USD', 'EUR'])
    ->latest('date')
    ->get()
    ->groupBy('code');
```

### Real-World Examples

#### Example 1: Fetching and Saving Daily Exchange Rates

```php
use Denizgolbas\LaravelTcmbEvds\Facades\Evds;

// A scheduled task that runs daily
public function handle()
{
    // Fetch and save today's rates for USD, EUR, GBP
    $saved = Evds::currency(['USD', 'EUR', 'GBP'])
        ->today()
        ->type('sell')
        ->marketType('forex')
        ->nullValueHandling('previous_day')
        ->save();

    return "{$saved->count()} rates saved.";
}
```

#### Example 2: Reporting Rates for a Date Range

```php
use Denizgolbas\LaravelTcmbEvds\Facades\Evds;

$data = Evds::currency(['USD', 'EUR'])
    ->startDate('2024-01-01')
    ->endDate('2024-01-31')
    ->type('sell')
    ->marketType('forex')
    ->get();

$report = [];
foreach ($data as $item) {
    $report[$item->code][$item->date->format('Y-m-d')] = $item->rate;
}

return view('exchange-rates', ['report' => $report]);
```

#### Example 3: Data Analysis with Model

```php
use Denizgolbas\LaravelTcmbEvds\Models\EvdsCurrencyRate;

// Average USD rate for last 30 days
$avgRate = EvdsCurrencyRate::where('code', 'USD')
    ->sell()
    ->forex()
    ->where('date', '>=', Carbon::now()->subDays(30))
    ->avg('rate');

// Highest and lowest rates
$maxRate = EvdsCurrencyRate::where('code', 'USD')
    ->sell()
    ->forex()
    ->max('rate');

$minRate = EvdsCurrencyRate::where('code', 'USD')
    ->sell()
    ->forex()
    ->min('rate');
```

#### Example 4: Error Handling

```php
use Denizgolbas\LaravelTcmbEvds\Facades\Evds;
use Denizgolbas\LaravelTcmbEvds\Exceptions\InvalidConfigurationException;
use Denizgolbas\LaravelTcmbEvds\Exceptions\ApiException;

try {
    $data = Evds::currency('USD')
        ->today()
        ->get();
} catch (InvalidConfigurationException $e) {
    // API key missing or misconfigured
    logger()->error('EVDS Config Error: ' . $e->getMessage());
    return response()->json(['error' => 'API configuration error'], 500);
} catch (ApiException $e) {
    // API request failed
    logger()->error('EVDS API Error: ' . $e->getMessage());
    return response()->json(['error' => 'API request failed'], 500);
} catch (\Exception $e) {
    // General error
    logger()->error('EVDS Error: ' . $e->getMessage());
    return response()->json(['error' => 'An unexpected error occurred'], 500);
}
```

## 📚 Builder Methods Reference

### `currency(array|string $codes)`

Sets currency codes. Accepts single or multiple currency codes.

```php
Evds::currency('USD')->get();
Evds::currency(['USD', 'EUR', 'GBP'])->get();
```

### `startDate(Carbon|string $date)`

Sets start date. Accepts Carbon instance or string formatted date.

```php
Evds::currency('USD')->startDate('2024-01-01')->get();
Evds::currency('USD')->startDate(Carbon::parse('2024-01-01'))->get();
```

### `endDate(Carbon|string $date)`

Sets end date. Accepts Carbon instance or string formatted date.

```php
Evds::currency('USD')->endDate('2024-01-10')->get();
```

### `type(array|string $types)`

Sets buy/sell type. Accepts `'buy'`, `'sell'`, or `['buy', 'sell']`.

```php
Evds::currency('USD')->type('sell')->get();
Evds::currency('USD')->type('buy')->get();
Evds::currency('USD')->type(['buy', 'sell'])->get();
```

### `marketType(array|string $marketTypes)`

Sets market type. Accepts `'forex'`, `'banknote'`, or `['forex', 'banknote']`.

```php
Evds::currency('USD')->marketType('forex')->get();
Evds::currency('USD')->marketType('banknote')->get();
Evds::currency('USD')->marketType(['forex', 'banknote'])->get();
```

### `nullValueHandling(string $strategy)`

Sets null value handling strategy. Accepts `'previous_day'`, `'last_week_avg'`, `'skip'`.

```php
Evds::currency('USD')->nullValueHandling('previous_day')->get();
Evds::currency('USD')->nullValueHandling('last_week_avg')->get();
Evds::currency('USD')->nullValueHandling('skip')->get();
```

### `build()` or `toUrl()`

Builds and returns the URL.

```php
$url = Evds::currency('USD')->today()->build();
```

### `get()`

Fetches data from API and returns a Collection.

```php
$data = Evds::currency('USD')->today()->get();
```

### `save()`

Fetches data from API and saves to database. Returns a Collection.

```php
// Simple save (saves API value as-is)
$saved = Evds::currency('USD')->today()->save();

// Save with rounded decimals
$saved = Evds::currency('USD')
    ->today()
    ->roundDecimals(2) // Round to 2 decimal places (e.g., 45.67)
    ->save();

// Different rounding modes
$saved = Evds::currency('USD')
    ->today()
    ->roundDecimals(2, 'floor') // Round down (e.g., 45.67 -> 45.67, 45.678 -> 45.67)
    ->save();

$saved = Evds::currency('USD')
    ->today()
    ->roundDecimals(2, 'ceil') // Round up (e.g., 45.67 -> 45.67, 45.671 -> 45.68)
    ->save();
```

## 🧪 Tests

To run tests:

```bash
composer test
```

or

```bash
vendor/bin/phpunit --testdox
```

### Test Coverage

- ✅ Builder URL creation tests
- ✅ Date method tests
- ✅ API client tests (with mocks)
- ✅ Database save tests
- ✅ Facade tests
- ✅ Null value management tests

### Matrix Tests

The following combinations are tested on GitHub Actions:

- PHP 8.2 + Laravel 10.x
- PHP 8.2 + Laravel 11.x
- PHP 8.3 + Laravel 10.x
- PHP 8.3 + Laravel 11.x

## ⚙️ Configuration

The config file (`config/evds.php`) contains the following settings:

```php
return [
    'api_key' => env('TCMB_EVDS_API_KEY', ''),
    'base_endpoint' => env('TCMB_EVDS_BASE_ENDPOINT', 'https://evds2.tcmb.gov.tr/service/evds/'),
    'default_currencies' => ['USD', 'EUR', 'GBP'],
    'default_start_date' => null,
    'default_end_date' => null,
    'default_frequency' => 'daily',
    'null_value_handling' => env('TCMB_EVDS_NULL_HANDLING', 'previous_day'),
    'model' => \Denizgolbas\LaravelTcmbEvds\Models\EvdsCurrencyRate::class,
];
```

### Null Value Management

You can configure how null values are handled for weekends and holidays:

- `'previous_day'`: Use previous day's value (default)
- `'last_week_avg'`: Use last week's average
- `'skip'`: Skip null values, don't save

### Model Customization

To use your own model, change the `model` setting in the config file:

```php
'model' => \App\Models\CustomCurrencyRate::class,
```

Your model must extend the `EvdsCurrencyRate` model:

```php
namespace App\Models;

use Denizgolbas\LaravelTcmbEvds\Models\EvdsCurrencyRate as BaseModel;

class CustomCurrencyRate extends BaseModel
{
    // Your customizations
}
```

## 🔧 Development

### Requirements

- PHP >= 8.2
- Laravel >= 10.0
- Composer

### Development Environment Setup

```bash
git clone https://github.com/denizgolbas/laravel-tcmb-evds.git
cd laravel-tcmb-evds
composer install
```

### Code Standards

The package follows PSR-12 code standards. To check code:

```bash
composer lint
```

## 📄 License

This package is licensed under the MIT license. See the [LICENSE](LICENSE) file for details.

## 🤝 Contributing

We welcome your contributions! Please:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Create a Pull Request

## 📞 Support

For questions or suggestions:

- Open an issue: [GitHub Issues](https://github.com/denizgolbas/laravel-tcmb-evds/issues)
- Email: denizgolbas@example.com

## 🙏 Acknowledgments

- Thanks to TCMB EVDS2 API team
- Thanks to Laravel community

---

⭐ If you liked this package, don't forget to give it a star!

