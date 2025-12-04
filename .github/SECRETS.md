# GitHub Secrets Kurulumu

GitHub Actions'da testlerin çalışması için API key'i GitHub Secrets'a eklemeniz gerekmektedir.

## Adımlar

1. GitHub repository'nize gidin
2. **Settings** sekmesine tıklayın
3. Sol menüden **Secrets and variables** → **Actions** seçeneğine tıklayın
4. **New repository secret** butonuna tıklayın
5. Şu bilgileri girin:
   - **Name**: `TCMB_EVDS_API_KEY`
   - **Secret**: API anahtarınızı yapıştırın
6. **Add secret** butonuna tıklayın

## Not

Testler için gerçek API key'e ihtiyaç yoktur çünkü testler mock response kullanır. Ancak gerçek API testleri yapmak isterseniz secret'ı ekleyebilirsiniz.

## Secret Kullanımı

Workflow dosyasında secret şu şekilde kullanılır:

```yaml
env:
  TCMB_EVDS_API_KEY: ${{ secrets.TCMB_EVDS_API_KEY }}
```

Eğer secret yoksa, testler `phpunit.xml` dosyasındaki `test-api-key` değerini kullanır.

