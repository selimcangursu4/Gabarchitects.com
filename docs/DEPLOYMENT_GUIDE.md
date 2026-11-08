# DEPLOYMENT GUIDE

> Durum: **PHASE 08 — Turhost adımları hazır** (alan adı: https://gabarchitects.com). Sunucuda doğrulandıkça güncellenecek.
> Senaryo ayrıntıları: [HOSTING_REQUIREMENTS.md](HOSTING_REQUIREMENTS.md) §3.

## Temel kurallar
1. Mevcut WordPress sitesinin dosyaları ve veritabanı **yedeklenmeden** hiçbir değişiklik yapılmaz.
2. `.env` hiçbir zaman `public_html` veya document root altında olmaz.
3. Production'da `APP_ENV=production`, `APP_DEBUG=false`.
4. Önce staging, sonra production.
5. Her deploy öncesinde veritabanı yedeği alınır.

## Turhost kurulumu — https://gabarchitects.com (Senaryo B, TD-063)

**Sunucudaki yerleşim**
```
/home/KULLANICI/
├── gab/            ← gab.zip (uygulama, vendor dahil) + .env   — web'den erişilemez
└── public_html/    ← public_html.zip (index.php, .htaccess, build, filament, media)
```

**0. Ön koşullar**
1. cPanel → **Yedekleme**: mevcut WordPress sitesinin tam yedeği (dosyalar + veritabanı) indirilir ve bilgisayarda saklanır. Yedek alınmadan 4. adıma geçilmez.
2. cPanel → **Select PHP Version / MultiPHP Manager**: `gabarchitects.com` için **PHP 8.4**. Eklentiler: `pdo_mysql`, `mbstring`, `intl`, `gd`, `zip`, `fileinfo`, `bcmath`, `exif`, `opcache`. `memory_limit` ≥ 256M, `upload_max_filesize`/`post_max_size` ≥ 32M, `max_execution_time` ≥ 300.
3. cPanel → **SSL/TLS Status**: `gabarchitects.com` ve `www.gabarchitects.com` için AutoSSL / Let's Encrypt **aktif** (TD-057/TD-062: SSL olmadan panel girişi çalışmaz).
4. cPanel → **MySQL Databases**: yeni veritabanı + kullanıcı, kullanıcıya **ALL PRIVILEGES**.
5. cPanel → **Email Accounts**: `info@gabarchitects.com` (iletişim formu bildirimleri ve şifre sıfırlama bu hesaptan gider).

**1. Paket (lokal)**
```
powershell -ExecutionPolicy Bypass -File deploy\turhost\build.ps1
```
Çıktı `dist\turhost\`: `gab.zip`, `public_html.zip`, `gab\.env` (APP_KEY dolu). `.env` içindeki `<...>` alanları doldurulur (DB, e-posta şifresi, `ADMIN_PASSWORD`).

**2. Yükleme (cPanel → File Manager)**
1. `public_html` içindeki eski WordPress dosyaları `public_html_wp_yedek` klasörüne taşınır (silinmez).
2. Ev dizininde `gab` klasörü → `gab.zip` yüklenir → **Extract**.
3. `public_html` → `public_html.zip` yüklenir → **Extract**. (`.htaccess` gizli dosyadır: Settings → "Show Hidden Files".)
4. `gab/.env` yüklenir (izin 600 önerilir). `gab/storage` ve `gab/bootstrap/cache` ile `public_html/media` yazılabilir (755).

**3. Komutlar** — cPanel **Terminal** (varsa) ya da **Cron Jobs** ile bir kez (her komut çalışınca cron silinir):
```
cd ~/gab && php artisan migrate --force
cd ~/gab && php artisan db:seed --force          # yalnızca boş veritabanında (veri aktarılmıyorsa)
cd ~/gab && php artisan gab:sync-admin           # yönetici: .env → ADMIN_EMAIL / ADMIN_PASSWORD
cd ~/gab && php artisan optimize && php artisan filament:optimize
```
Cron'da `php` yerine cPanel'in gösterdiği tam yol kullanılır (ör. `/opt/cpanel/ea-php84/root/usr/bin/php`). Kalıcı cron (scheduler): `* * * * * cd ~/gab && php artisan schedule:run >> /dev/null 2>&1`.

**4. Veri (Railway → Turhost)** — Railway'deki gerçek içerik (Arkas Railport projesi, Konya ilanı, iletişim bilgileri, sayfalar) taşınır: Railway MySQL dökümü → phpMyAdmin **Import**; görsel türevleri → `public_html/media`, orijinaller → `gab/storage/app/private/media`. Ardından 3. adımdaki `migrate`, `gab:sync-admin`, `optimize` (seed **çalıştırılmaz**).

**5. Kontrol**: aşağıdaki SEO ve Güvenlik listeleri + `http://www.gabarchitects.com` → `https://gabarchitects.com` (301), `/admin` girişi `info@gabarchitects.com`, iletişim formu e-postası.

**Güncelleme (sonraki sürümler):** paket yeniden üretilir; `gab.zip` (`.env` ve `storage` korunur) ve `public_html.zip` (`media` korunur) üzerine açılır → `migrate --force` + `optimize`. Öncesinde veritabanı yedeği.

## Genel adımlar (Senaryo A — SSH var)
1. Lokal: `npm run build`, testler yeşil.
2. Sunucu: kod güncellenir (git pull veya zip).
3. `composer install --no-dev --optimize-autoloader`
4. `php artisan migrate --force`
5. `php artisan optimize` (config/route/view cache) ve `php artisan filament:optimize` (panel bileşen/ikon cache)
6. `storage:link` **gerekmez**: medya türevleri doğrudan `public/media` altına yazılır (TD-019). `public/media` ve `storage/app/private/media` yazılabilir olmalı.
7. İlk kurulumda: `php artisan db:seed --force` (`.env` → `ADMIN_EMAIL`, `ADMIN_PASSWORD`). Örnek ilanlar production'da oluşturulmaz.
8. `.env`: `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://alan-adi` (sitemap, kanonik ve paylaşım adresleri buna göre), `MAIL_*` (SMTP), `MEDIA_AVIF` (sunucu GD'si desteklemiyorsa `false`).
   - **Staging / geçici adres:** `SEO_INDEXABLE=false` (yarım site Google'a girmesin). Gerçek alan adında bu satır silinir (production'da varsayılan açık).
9. Smoke test: ana sayfa, hero filtresi → /satilik, bir proje ve ilan detayı, iletişim formu (panelde Web Formları'na düşüyor mu, e-posta geliyor mu), `/admin` girişi.
10. SEO / performans kontrolü (PHASE 06):
    - `/robots.txt` → `Disallow: /admin` + `Sitemap:` satırı (staging'de `Disallow: /`).
    - `/sitemap.xml` açılıyor, adresler `https://` ve doğru alan adıyla.
    - Sayfa kaynağında `<meta name="robots" content="index, follow…">`.
    - `curl -I -H "Accept-Encoding: gzip" https://alan-adi/` → `Content-Encoding: gzip`; `/build/assets/*.css` → `Cache-Control: public, max-age=31536000, immutable`. Yoksa Turhost'ta `mod_deflate` / `mod_headers` açık mı sorulur.
    - PHP OPcache açık mı (cPanel → PHP seçenekleri).
    - Google Search Console: alan adı doğrulanır, `sitemap.xml` gönderilir. Eski WordPress adresleri için Yönlendirmeler (panel → `/admin/redirects`) kontrol edilir.
    - Lighthouse (mobil) ana sayfa + bir ilan + bir proje; sonuç PHASE_TRACKER'a yazılır.

## Test / önizleme ortamı — Railway (TD-060)
> Production değildir. Müşteri önizlemesi ve Turhost öncesi MySQL/Apache provası içindir. Arama motorlarına kapalıdır.

Depodaki dosyalar: `Dockerfile`, `docker/entrypoint.sh`, `railway.json`, `.dockerignore`.

1. railway.com → GitHub ile giriş → **New Project → Deploy from GitHub repo** → `Gabarchitects.com`.
2. Aynı projede **+ Create → Database → MySQL**.
3. Web servisi → **Settings → Volumes → Add Volume**, mount path: `/data` (görseller, orijinaller, APP_KEY).
4. Web servisi → **Variables** (Raw Editor):
   ```
   APP_NAME="GAB Gayrimenkul Proje Geliştirme"
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://${{RAILWAY_PUBLIC_DOMAIN}}
   APP_LOCALE=tr
   DB_CONNECTION=mysql
   DB_URL=${{MySQL.MYSQL_URL}}
   SESSION_DRIVER=database
   CACHE_STORE=database
   QUEUE_CONNECTION=sync
   LOG_CHANNEL=stderr
   MAIL_MAILER=log
   TRUSTED_PROXIES=*
   SEO_INDEXABLE=false
   GAB_SEED_SAMPLES=true
   ADMIN_NAME="GAB Yönetici"
   ADMIN_EMAIL=<yönetici e-postası>
   ADMIN_PASSWORD=<güçlü şifre>
   ```
   `APP_KEY` verilmezse ilk açılışta üretilip `/data/app.key` içinde saklanır.
5. **Settings → Networking → Generate Domain** → `https://…up.railway.app`.
6. İlk açılış: migration + ilk içerik (13 görsel, AVIF dahil 1–2 dk). Sonraki dağıtımlarda içerik korunur (`gab:seed-if-empty`).
7. Kontrol: ana sayfa, `/admin` girişi, iletişim formu → Web Formları, panelden görsel yükleme, yeniden dağıtım sonrası görsel/kayıt duruyor mu.

## Güvenlik kontrolü (PHASE 07)
1. **SSL önce:** production'da oturum çerezi yalnızca HTTPS'te gönderilir (TD-057). SSL yoksa panele giriş yapılamaz. Geçici olarak gerekirse `SESSION_SECURE_COOKIE=false` (yalnızca kurulum sırasında, sonra silinir).
2. `APP_DEBUG=false` doğrula: olmayan bir hata tetiklenince marka 500 sayfası görünmeli, dosya yolu/sürüm görünmemeli.
3. `curl -I https://alan-adi/` → `Content-Security-Policy`, `X-Frame-Options`, `Strict-Transport-Security`, **`X-Powered-By` yok**. `curl -I https://alan-adi/admin/login` → `X-Robots-Tag: noindex`.
4. `https://alan-adi/.env` ve `/composer.json` → 403/404.
5. Bakım modu: `php artisan down --render="errors.503" --secret="<uzun-rastgele-değer>"` → ziyaretçi bakım sayfasını görür; yönetici `https://alan-adi/<secret>` ile geçer. Bitince `php artisan up`.
6. Dizin izinleri: `storage/` ve `bootstrap/cache/` yazılabilir; diğerleri salt okunur. `public/media` yazılabilir.
7. Yayından önce: `composer audit` ve `npm audit` temiz olmalı.

## Yedekleme prosedürü (taslak)
- Veritabanı: günlük otomatik dump (scheduler), 14 gün saklanır + haftalık cPanel yedeği indirilir.
- Medya: `storage/app/media` haftalık arşivlenir ve sunucu dışında saklanır.
- Geri yükleme adımları PHASE 08'de test edilerek yazılacak.
