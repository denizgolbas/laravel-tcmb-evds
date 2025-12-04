# Laravel TCMB EVDS2 Günlük Döviz Kurları Paketi

🇹🇷 **Türkçe** | [🇬🇧 English](README_EN.md)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/denizgolbas/laravel-tcmb-evds.svg?style=flat-square)](https://packagist.org/packages/denizgolbas/laravel-tcmb-evds)
[![Tests](https://github.com/denizgolbas/laravel-tcmb-evds/actions/workflows/tests.yml/badge.svg)](https://github.com/denizgolbas/laravel-tcmb-evds/actions/workflows/tests.yml)

TCMB EVDS2 API'sinin "Günlük Döviz Kurları (Daily FX Rates)" servisine özel geliştirilmiş, modern bir Laravel paketi.

Paket, chainable bir URL/Query Builder, esnek bir API istemcisi, publish edilebilir migration + model yapısı ve isteğe bağlı olarak gelen verilerin veritabanına kaydedilmesini destekler.

Bu paket; proje içinde kolayca günlük döviz kurlarını çekmek, işlemek, saklamak ve otomatikleştirmek isteyen Laravel geliştiricileri için tasarlanmıştır.

## ⚠️ ÖNEMLİ UYARI

**Bu paket resmi TCMB EVDS2 API'sine bağlanmak ve kurları kolayca işleyebilmek için oluşturulmuş bir araçtır. Resmi bir dağıtım değildir.**

**Kullanımda doğacak finansal hatalar ve kayıplar sizin sorumluluğunuzdadır.**

- Bu paket TCMB tarafından resmi olarak desteklenmemektedir
- Finansal işlemlerde kullanmadan önce mutlaka doğrulama yapın
- Kritik finansal kararlar için resmi TCMB kaynaklarını kullanın
- Paket kullanımından kaynaklanan herhangi bir zarardan paket geliştiricileri sorumlu değildir

## 🚀 Özellikler

- 🔗 **Fluent URL Builder** - TCMB EVDS2 endpoint'ini zincir fonksiyonlar ile kolayca oluşturun
- 📅 **Hazır Tarih Kısayolları** - `today()`, `yesterday()`, `lastDays(7)`, `thisMonth()` gibi fonksiyonlarla hızlı tarih aralıkları
- 💾 **Opsiyonel Database Kaydı** - API'den gelen döviz kurlarını model ile veritabanına kaydedebilirsiniz
- ⚙️ **Publish Edilebilir Model ve Migration** - İsterseniz kendi projenizde model/migration dosyalarını özelleştirin
- 🧩 **Config Tabanlı Esneklik** - Varsayılan dövizler, tarih ayarları ve API key config üzerinden yönetilir
- 🎯 **Alış/Satış ve Forex/Banknote Ayrımı** - Döviz alış, döviz satış, efektif alış, efektif satış kurlarını ayrı ayrı çekebilirsiniz
- 🔄 **Null Değer Yönetimi** - Hafta sonları ve tatil günleri için esnek null değer işleme stratejileri
- 🧪 **Unit, Feature ve Matrix Testleri** - Laravel 10/11 + PHP 8.2/8.3 kombinasyonları için GitHub Actions test workflow'u
- 📡 **Laravel Http Client ile API İletişimi** - Temiz, güvenli ve performanslı bir API client

## 📦 Kurulum

Paketi Composer ile yükleyin:

```bash
composer require denizgolbas/laravel-tcmb-evds
```

### Config Dosyasını Publish Etme

Config dosyasını publish etmek için:

```bash
php artisan vendor:publish --tag=evds-config
```

Bu işlem `config/evds.php` dosyasını oluşturur. `.env` dosyanıza API anahtarınızı ekleyin:

```env
TCMB_EVDS_API_KEY=your-api-key-here
TCMB_EVDS_BASE_ENDPOINT=https://evds2.tcmb.gov.tr/service/evds/
TCMB_EVDS_NULL_HANDLING=previous_day
```

**Önemli:** API anahtarınızı [TCMB EVDS2 Kullanıcı Dokümanları](https://evds2.tcmb.gov.tr/index.php?/evds/userDocs) sayfasından alabilirsiniz.

### Migration'ları Publish Etme

Migration dosyalarını publish etmek için:

```bash
php artisan vendor:publish --tag=evds-migrations
```

Ardından migration'ları çalıştırın:

```bash
php artisan migrate
```

### Model'i Publish Etme

Model dosyasını publish etmek için:

```bash
php artisan vendor:publish --tag=evds-model
```

Bu işlem `app/Models/EvdsCurrencyRate.php` dosyasını oluşturur. İsterseniz bu modeli extend ederek özelleştirebilirsiniz.

## 🛠️ Kullanım Örnekleri

### Temel Kullanım

```php
use Denizgolbas\LaravelTcmbEvds\Facades\Evds;

// Tek döviz kuru çekme (otomatik olarak hem alış hem satış, hem forex hem banknote)
$data = Evds::currency('USD')
    ->today()
    ->get();

// Çoklu döviz kuru çekme
$data = Evds::currency(['USD', 'EUR'])
    ->startDate('2024-01-01')
    ->endDate('2024-01-10')
    ->get();
```

### Alış/Satış Seçimi

```php
// Sadece satış kurları (Forex Selling - Döviz Satış)
$data = Evds::currency('USD')
    ->startDate('2024-01-01')
    ->endDate('2024-01-10')
    ->type('sell')
    ->get();

// Sadece alış kurları (Forex Buying - Döviz Alış)
$data = Evds::currency('USD')
    ->startDate('2024-01-01')
    ->endDate('2024-01-10')
    ->type('buy')
    ->get();

// Hem alış hem satış (varsayılan)
$data = Evds::currency('USD')
    ->startDate('2024-01-01')
    ->endDate('2024-01-10')
    ->get();
```

### Forex/Banknote (Döviz/Efektif) Seçimi

```php
// Sadece Forex kurları (Döviz)
$data = Evds::currency('USD')
    ->startDate('2024-01-01')
    ->endDate('2024-01-10')
    ->marketType('forex')
    ->get();

// Sadece Banknote kurları (Efektif)
$data = Evds::currency('USD')
    ->startDate('2024-01-01')
    ->endDate('2024-01-10')
    ->marketType('banknote')
    ->get();

// Hem forex hem banknote (varsayılan)
$data = Evds::currency('USD')
    ->startDate('2024-01-01')
    ->endDate('2024-01-10')
    ->get();
```

### Kombinasyon Örnekleri

```php
// Forex Satış (Döviz Satış)
$data = Evds::currency(['USD', 'EUR'])
    ->startDate('2024-01-01')
    ->endDate('2024-01-10')
    ->type('sell')
    ->marketType('forex')
    ->get();

// Banknote Alış (Efektif Alış)
$data = Evds::currency('USD')
    ->startDate('2024-01-01')
    ->endDate('2024-01-10')
    ->type('buy')
    ->marketType('banknote')
    ->get();

// Banknote Satış (Efektif Satış)
$data = Evds::currency('EUR')
    ->today()
    ->type('sell')
    ->marketType('banknote')
    ->get();
```

### Tarih Kısayolları

```php
// Bugün
$data = Evds::currency('USD')->today()->get();

// Dün
$data = Evds::currency('USD')->yesterday()->get();

// Son 7 gün
$data = Evds::currency('USD')->lastDays(7)->get();

// Bu hafta
$data = Evds::currency('USD')->thisWeek()->get();

// Geçen hafta
$data = Evds::currency('USD')->lastWeek()->get();

// Bu ay
$data = Evds::currency('USD')->thisMonth()->get();

// Geçen ay
$data = Evds::currency('USD')->lastMonth()->get();
```

### Null Değer Yönetimi (Hafta Sonları ve Tatil Günleri)

```php
// Bir önceki günün değerini kullan (varsayılan)
$data = Evds::currency('USD')
    ->startDate('2024-01-01')
    ->endDate('2024-01-10')
    ->nullValueHandling('previous_day')
    ->get();

// Geçen haftanın ortalamasını kullan
$data = Evds::currency('USD')
    ->startDate('2024-01-01')
    ->endDate('2024-01-10')
    ->nullValueHandling('last_week_avg')
    ->get();

// Null değerleri atla (kaydetme)
$data = Evds::currency('USD')
    ->startDate('2024-01-01')
    ->endDate('2024-01-10')
    ->nullValueHandling('skip')
    ->get();
```

### Veritabanına Kaydetme

```php
// Basit kaydetme
$saved = Evds::currency('USD')
    ->today()
    ->save();

// Kombinasyonlu kaydetme
$saved = Evds::currency(['USD', 'EUR'])
    ->startDate('2024-01-01')
    ->endDate('2024-01-10')
    ->type('sell')
    ->marketType('forex')
    ->nullValueHandling('previous_day')
    ->save();

// Kaydedilen verileri kullanma
foreach ($saved as $rate) {
    echo "{$rate->code}: {$rate->rate} ({$rate->date->format('Y-m-d')})\n";
    echo "Tip: {$rate->type}, Market: {$rate->marketType}\n";
}
```

### URL Oluşturma

```php
// URL'i görmek için
$url = Evds::currency(['USD', 'EUR'])
    ->startDate('2024-01-01')
    ->endDate('2024-01-10')
    ->type('sell')
    ->marketType('forex')
    ->build();

echo $url;
// Çıktı: https://evds2.tcmb.gov.tr/service/evds/?series=TP.DK.USD.S.YTL-TP.DK.EUR.S.YTL&startDate=01-01-2024&endDate=10-01-2024&type=json&key=...
```

### Model ile Veritabanından Veri Çekme

```php
use Denizgolbas\LaravelTcmbEvds\Models\EvdsCurrencyRate;
use Carbon\Carbon;

// Bugünün USD kuru
$rate = EvdsCurrencyRate::where('code', 'USD')
    ->where('date', Carbon::today())
    ->first();

// Belirli bir tarih aralığındaki kurlar
$rates = EvdsCurrencyRate::where('code', 'USD')
    ->whereBetween('date', ['2024-01-01', '2024-01-10'])
    ->get();

// Sadece satış kurları
$sellRates = EvdsCurrencyRate::where('code', 'USD')
    ->sell()
    ->get();

// Sadece forex kurları
$forexRates = EvdsCurrencyRate::where('code', 'USD')
    ->forex()
    ->get();

// Kombinasyon: USD Forex Satış
$usdForexSell = EvdsCurrencyRate::where('code', 'USD')
    ->sell()
    ->forex()
    ->whereBetween('date', ['2024-01-01', '2024-01-10'])
    ->orderBy('date', 'asc')
    ->get();

// En son kaydedilen USD kuru
$latestUsd = EvdsCurrencyRate::where('code', 'USD')
    ->latest('date')
    ->first();

// Her döviz için en son kurlar
$latestRates = EvdsCurrencyRate::whereIn('code', ['USD', 'EUR'])
    ->latest('date')
    ->get()
    ->groupBy('code');
```

### Gerçek Dünya Örnekleri

#### Örnek 1: Günlük Döviz Kurlarını Çekip Kaydetme

```php
use Denizgolbas\LaravelTcmbEvds\Facades\Evds;

// Her gün çalışacak bir scheduled task
public function handle()
{
    // USD, EUR, GBP için bugünün kurlarını çek ve kaydet
    $saved = Evds::currency(['USD', 'EUR', 'GBP'])
        ->today()
        ->type('sell')
        ->marketType('forex')
        ->nullValueHandling('previous_day')
        ->save();

    return "{$saved->count()} adet kur kaydedildi.";
}
```

#### Örnek 2: Belirli Tarih Aralığındaki Kurları Raporlama

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

#### Örnek 3: Model ile Veri Analizi

```php
use Denizgolbas\LaravelTcmbEvds\Models\EvdsCurrencyRate;

// Son 30 günün ortalama USD kuru
$avgRate = EvdsCurrencyRate::where('code', 'USD')
    ->sell()
    ->forex()
    ->where('date', '>=', Carbon::now()->subDays(30))
    ->avg('rate');

// En yüksek ve en düşük kurlar
$maxRate = EvdsCurrencyRate::where('code', 'USD')
    ->sell()
    ->forex()
    ->max('rate');

$minRate = EvdsCurrencyRate::where('code', 'USD')
    ->sell()
    ->forex()
    ->min('rate');
```

#### Örnek 4: Hata Yönetimi

```php
use Denizgolbas\LaravelTcmbEvds\Facades\Evds;
use Denizgolbas\LaravelTcmbEvds\Exceptions\InvalidConfigurationException;
use Denizgolbas\LaravelTcmbEvds\Exceptions\ApiException;

try {
    $data = Evds::currency('USD')
        ->today()
        ->get();
} catch (InvalidConfigurationException $e) {
    // API key eksik veya yanlış yapılandırılmış
    logger()->error('EVDS Config Error: ' . $e->getMessage());
    return response()->json(['error' => 'API yapılandırma hatası'], 500);
} catch (ApiException $e) {
    // API isteği başarısız
    logger()->error('EVDS API Error: ' . $e->getMessage());
    return response()->json(['error' => 'API isteği başarısız'], 500);
} catch (\Exception $e) {
    // Genel hata
    logger()->error('EVDS Error: ' . $e->getMessage());
    return response()->json(['error' => 'Beklenmeyen bir hata oluştu'], 500);
}
```

## 📚 Builder Metodları Referansı

### `currency(array|string $codes)`

Döviz kodlarını ayarlar. Tek veya çoklu döviz kodu kabul eder.

```php
Evds::currency('USD')->get();
Evds::currency(['USD', 'EUR', 'GBP'])->get();
```

### `startDate(Carbon|string $date)`

Başlangıç tarihini ayarlar. Carbon instance veya string formatında tarih kabul eder.

```php
Evds::currency('USD')->startDate('2024-01-01')->get();
Evds::currency('USD')->startDate(Carbon::parse('2024-01-01'))->get();
```

### `endDate(Carbon|string $date)`

Bitiş tarihini ayarlar. Carbon instance veya string formatında tarih kabul eder.

```php
Evds::currency('USD')->endDate('2024-01-10')->get();
```

### `type(array|string $types)`

Alış/Satış tipini ayarlar. `'buy'`, `'sell'` veya `['buy', 'sell']` kabul eder.

```php
Evds::currency('USD')->type('sell')->get();
Evds::currency('USD')->type('buy')->get();
Evds::currency('USD')->type(['buy', 'sell'])->get();
```

### `marketType(array|string $marketTypes)`

Market tipini ayarlar. `'forex'`, `'banknote'` veya `['forex', 'banknote']` kabul eder.

```php
Evds::currency('USD')->marketType('forex')->get();
Evds::currency('USD')->marketType('banknote')->get();
Evds::currency('USD')->marketType(['forex', 'banknote'])->get();
```

### `nullValueHandling(string $strategy)`

Null değer işleme stratejisini ayarlar. `'previous_day'`, `'last_week_avg'`, `'skip'` kabul eder.

```php
Evds::currency('USD')->nullValueHandling('previous_day')->get();
Evds::currency('USD')->nullValueHandling('last_week_avg')->get();
Evds::currency('USD')->nullValueHandling('skip')->get();
```

### `build()` veya `toUrl()`

URL'i oluşturur ve döndürür.

```php
$url = Evds::currency('USD')->today()->build();
```

### `get()`

API'den veri çeker ve Collection döndürür.

```php
$data = Evds::currency('USD')->today()->get();
```

### `save()`

API'den veri çeker ve veritabanına kaydeder. Collection döndürür.

```php
$saved = Evds::currency('USD')->today()->save();
```

## 🧪 Testler

Testleri çalıştırmak için:

```bash
composer test
```

veya

```bash
vendor/bin/phpunit --testdox
```

### Test Kapsamı

- ✅ Builder URL oluşturma testleri
- ✅ Tarih metodları testleri
- ✅ API client testleri (mock ile)
- ✅ Database kayıt testleri
- ✅ Facade testleri
- ✅ Null değer yönetimi testleri

### Matrix Testler

GitHub Actions üzerinde şu kombinasyonlar test edilir:

- PHP 8.2 + Laravel 10.x
- PHP 8.2 + Laravel 11.x
- PHP 8.3 + Laravel 10.x
- PHP 8.3 + Laravel 11.x

### GitHub Actions için API Key Kurulumu

GitHub Actions'da testlerin çalışması için (opsiyonel - testler mock kullanır):

1. GitHub repository'nize gidin
2. **Settings** → **Secrets and variables** → **Actions** seçeneğine gidin
3. **New repository secret** butonuna tıklayın
4. Şu bilgileri girin:
   - **Name**: `TCMB_EVDS_API_KEY`
   - **Secret**: API anahtarınızı yapıştırın
5. **Add secret** butonuna tıklayın

**Not:** Testler için gerçek API key'e ihtiyaç yoktur çünkü testler mock response kullanır. Ancak gerçek API testleri yapmak isterseniz secret'ı ekleyebilirsiniz.

## ⚙️ Konfigürasyon

Config dosyası (`config/evds.php`) aşağıdaki ayarları içerir:

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

### Null Değer Yönetimi

Hafta sonları ve tatil günleri için null değerlerin nasıl işleneceğini ayarlayabilirsiniz:

- `'previous_day'`: Bir önceki günün değerini kullan (varsayılan)
- `'last_week_avg'`: Geçen haftanın ortalamasını kullan
- `'skip'`: Null değerleri atla, kaydetme

### Model Özelleştirme

Kendi modelinizi kullanmak için config dosyasında `model` ayarını değiştirin:

```php
'model' => \App\Models\CustomCurrencyRate::class,
```

Kendi modeliniz `EvdsCurrencyRate` modelini extend etmelidir:

```php
namespace App\Models;

use Denizgolbas\LaravelTcmbEvds\Models\EvdsCurrencyRate as BaseModel;

class CustomCurrencyRate extends BaseModel
{
    // Özelleştirmeleriniz
}
```

## 🔧 Geliştirme

### Gereksinimler

- PHP >= 8.2
- Laravel >= 10.0
- Composer

### Geliştirme Ortamı Kurulumu

```bash
git clone https://github.com/denizgolbas/laravel-tcmb-evds.git
cd laravel-tcmb-evds
composer install
```

### Kod Standartları

Paket PSR-12 kod standartlarına uygundur. Kodları kontrol etmek için:

```bash
composer lint
```

## 📄 Lisans

Bu paket MIT lisansı altında lisanslanmıştır. Detaylar için [LICENSE](LICENSE) dosyasına bakın.

## 🤝 Katkıda Bulunma

Katkılarınızı bekliyoruz! Lütfen:

1. Fork edin
2. Feature branch oluşturun (`git checkout -b feature/amazing-feature`)
3. Değişikliklerinizi commit edin (`git commit -m 'Add some amazing feature'`)
4. Branch'inizi push edin (`git push origin feature/amazing-feature`)
5. Pull Request oluşturun

## 📞 Destek

Sorularınız veya önerileriniz için:

- Issue açın: [GitHub Issues](https://github.com/denizgolbas/laravel-tcmb-evds/issues)
- Email: denizgolbas@example.com

## 🙏 Teşekkürler

- TCMB EVDS2 API ekibine teşekkürler
- Laravel topluluğuna teşekkürler

---

⭐ Bu paketi beğendiyseniz yıldız vermeyi unutmayın!
