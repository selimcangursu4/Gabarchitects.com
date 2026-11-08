# PROJECT STATUS

**Son güncelleme:** 2026-11-08
**Aktif faz:** PHASE 07 – Güvenlik ve Test → **Kontrol Bekliyor (kullanıcı onayı)**
**Sonraki faz:** PHASE 08 – Turhost Deployment

## Kararlar
- Marka **GAB Architects** · Admin **Filament 5** (`/admin`) · **Yalnızca Türkçe** · Varsayılan tema **Dark**
- Gayrimenkul portföyü sitenin merkezinde · Turhost analizi ertelendi
- İlan detayında form yok: WhatsApp + Ara butonları (TD-047)

## Test / önizleme sitesi (Railway, TD-060)
- https://gabarchitectscom-production.up.railway.app — panel: `/admin` (giriş bilgileri kullanıcının bilgisayarında `~/gab-railway-yonetici.txt`)
- Production değildir; arama motorlarına kapalı; ÖRNEK ilanlar var. Kurulum: DEPLOYMENT_GUIDE → "Test / önizleme ortamı".
- Not: GitHub'a gönderim otomatik dağıtımı tetiklemedi; dağıtımlar API/panelden "Deploy" ile başlatıldı.

## Lokal çalıştırma
```bash
composer install && npm install && npm run build
cp .env.example .env && php artisan key:generate        # .env → ADMIN_EMAIL
touch database/database.sqlite
php artisan migrate:fresh --seed     # ~40 sn; local'de 12 ÖRNEK ilan
php artisan serve --host=127.0.0.1 --port=8010
```
- Site: http://127.0.0.1:8010 · Panel: http://127.0.0.1:8010/admin
- Lokal yönetici: `admin@gabarchitects.test` (şifre kullanıcıya iletildi; panelde Profil'den değiştirilebilir)
- Örnek ilanları sil: `php artisan gab:purge-samples`
- Lokal site arama motorlarına kapalıdır (noindex + `Disallow: /`); production'da otomatik açılır (TD-050).

## Mevcut durum
- Public site: 22 route (+ `/sitemap.xml`, `/robots.txt`), tüm sayfalar veritabanından.
- Panel: 16 resource + Site Ayarları + Dashboard; policy tabanlı yetki; tüm değişiklikler işlem kaydına yazılıyor.
- SEO: meta, Open Graph, Twitter Card, canonical, hreflang, sitemap, robots, 7 tip JSON-LD, breadcrumb.
- Performans (Lighthouse mobil, lokal production benzeri): 90–99; erişilebilirlik 100; en iyi uygulamalar 100; SEO 100.
- Güvenlik: güvenlik başlıkları + CSP (nonce), marka hata sayfaları (403/419/429/500/503), KVKK çerez bildirimi, giriş deneme sınırı, yükleme korumaları; `composer audit` / `npm audit` temiz.
- Testler: **124/124 PHPUnit** (966 doğrulama) + mobil tarayıcı testi 16/16 + 14 sayfa × 7 ekran genişliğinde taşma taraması temiz.

## Açık konular
1. **PHASE 07 onayı.**
2. **Telefon ve WhatsApp numarası** (ilan butonları ve Organization şeması bunlara bağlı), e-posta, web formu bildirim adresi → Site Ayarları.
3. Gerçek ilanlar panelden girilmeli; sonra `gab:purge-samples`.
4. 16 projenin "devam eden" olanları panelden işaretlenmeli.
5. 3 proje görseli, vektör logo (şemaya logo eklenecek), hukuki metinler bekleniyor. Hukuki metin girilene kadar o sayfalar noindex ve sitemap dışı.
6. Production'da SMTP ayarı (şu an lokal mailer = log).
7. TD-026 (AVIF türevlerini kuyruğa alma) hâlâ **Önerildi**: panelde büyük görsel yüklemesi birkaç saniye sürebilir. Sunucuda cron/kuyruk imkânı PHASE 08'de netleşince karar verilecek.
8. Core Web Vitals'ın gerçek değerleri Turhost'ta ölçülecek (PHASE 08); `.htaccess` gzip/önbellek kurallarının sunucuda çalıştığı doğrulanacak.
9. Çerez bandı metni ve Çerez Politikası / KVKK / Gizlilik metinleri müşteri hukuk onayıyla panelden girilmeli. Şu an bant yalnızca teknik gerçeği söylüyor ("yalnızca zorunlu çerez"). Analiz/reklam aracı eklenirse bant kabul/ret bandına dönüştürülmeli (TD-055).
10. Production'da SSL, panel girişinden **önce** aktif olmalı (oturum çerezi `secure`, TD-057).

## Yeni oturum için
Sırasıyla okuyun: PROJECT_STATUS.md → PHASE_TRACKER.md → CHANGELOG.md → TECHNICAL_DECISIONS.md.
