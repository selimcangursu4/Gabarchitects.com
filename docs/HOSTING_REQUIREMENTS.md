# HOSTING REQUIREMENTS — Turhost

> Faz: PHASE 00 · Durum: **ERTELENDİ (TD-015)**. Site önce lokalde geliştirilip onaylanacak; Turhost analizi PHASE 08'den önce yapılacak.
> Not: Proje **Laravel 13.x** ile kuruldu, bu nedenle sunucuda **PHP ≥ 8.3** gerekiyor.

---

## 1. Araştırma Sonucu (dürüst durum)

Turhost'un paket özellikleri **resmi kaynaktan doğrulanamadı**:

- `turhost.com/web-hosting/`, `/linux-hosting/` ve `/destek/` sayfaları 404 döndü.
- Ana sayfanın içeriği JavaScript ile oluşturulduğu için okunamadı.
- Üçüncü taraf listeleme siteleri (whtop.com) Linux paylaşımlı paketlerde **cPanel** ve bir **PHP sürüm seçici** olduğunu belirtiyor (Giriş, Giriş Plus, Uzman, Limitsiz Pro paketleri). **SSH, Composer, Cron ve document root** konularında ise bilgi yok.

Sonuç olarak, aşağıdaki kontrol listesi müşterinin mevcut paketi üzerinde (cPanel'e girilerek) veya Turhost destek ekibine sorularak doğrulanmalıdır. **Doğrulama yapılmadan Phase 08 planı kesinleşmez.**

---

## 2. Gereksinim Kontrol Listesi

| # | Gereksinim | Minimum | Önerilen | Zorunlu mu? | Nasıl kontrol edilir |
|---|---|---|---|---|---|
| 1 | PHP sürümü | **8.3** (Laravel 13 gereksinimi; güncellendi 2026-11-08) | 8.4 | **Evet** | cPanel → "Select PHP Version" / "MultiPHP Manager" |
| 2 | PHP eklentileri | bcmath, ctype, curl, dom, fileinfo, mbstring, openssl, pdo_mysql, tokenizer, xml, zip, intl | + **gd (WebP destekli)** veya imagick, exif | **Evet** | PHP Selector → Extensions |
| 3 | AVIF desteği | — | GD libavif veya Imagick AVIF | Hayır | Test betiği ile (`imageavif` fonksiyonu) |
| 4 | MySQL / MariaDB | MySQL 5.7 / MariaDB 10.3 | MySQL 8 / MariaDB 10.6+ | **Evet** | cPanel → MySQL Databases / phpMyAdmin |
| 5 | Document root değiştirme | — | Domain kökünü `/laravel/public` dizinine yönlendirme | Önemli | cPanel → Domains → Document Root |
| 6 | SSH erişimi | — | Var | Önemli (yoksa B senaryosu) | cPanel → "SSH Access" / "Terminal" |
| 7 | Composer | — | SSH'te `composer` | Hayır (lokalde çalıştırılabilir) | `composer -V` |
| 8 | Git | — | cPanel Git Version Control | Hayır | cPanel → Git™ Version Control |
| 9 | Cron Job | Dakikada 1 | Dakikada 1 | **Evet** (queue, sitemap, temizlik) | cPanel → Cron Jobs |
| 10 | SSL | Let's Encrypt / AutoSSL | Ücretsiz otomatik SSL | **Evet** | cPanel → SSL/TLS Status |
| 11 | `upload_max_filesize` / `post_max_size` | 20M / 25M | 64M / 64M | **Evet** | PHP Selector → Options |
| 12 | `memory_limit` | 256M | 512M | **Evet** (görsel işleme) | PHP Selector → Options |
| 13 | `max_execution_time` | 60 | 120 | Önemli | PHP Selector → Options |
| 14 | Symlink izni | — | `storage:link` çalışmalı | Hayır (alternatif mevcut) | Test |
| 15 | `proc_open`, `symlink`, `exec` | — | Açık | Hayır | `disable_functions` listesi |
| 16 | E-posta / SMTP | 1 hesap | SPF + DKIM + DMARC | **Evet** (form bildirimi) | cPanel → Email Accounts / Email Deliverability |
| 17 | Disk / inode | 2 GB / 50k | 10 GB+ / 200k+ | Önemli (görsel türevleri) | Paket detayı |
| 18 | Yedekleme | Haftalık | Günlük + elle yedek (JetBackup vb.) | Önemli | cPanel → Backup |
| 19 | Sunucu konumu | — | Türkiye (KVKK açısından avantajlı) | Hayır | Destek |
| 20 | Node.js | **Gerekmez** | — | Hayır | — |

### Turhost destek ekibine gönderilecek soru metni (hazır)
> Merhaba, `gabarchitects.com` için kullandığımız paket üzerinde Laravel uygulaması çalıştırmak istiyoruz. Aşağıdaki konularda bilgi verebilir misiniz?
> 1) PHP 8.3 seçilebiliyor mu, CLI PHP sürümü de aynı mı oluyor? 2) SSH/Terminal erişimi var mı? 3) Composer kurulu mu? 4) Ana domainin document root'u `public_html` dışında bir klasöre yönlendirilebilir mi? 5) Cron Job'lar dakikalık çalıştırılabiliyor mu? 6) `upload_max_filesize` ve `memory_limit` değerleri nedir, artırılabilir mi? 7) GD/Imagick WebP ve AVIF destekliyor mu? 8) MySQL/MariaDB sürümü nedir? 9) `symlink` ve `proc_open` fonksiyonları açık mı? 10) Otomatik yedekleme sıklığı nedir?

---

## 3. Dağıtım Senaryoları

### Senaryo A — SSH + document root değiştirilebilir (ideal)
```
/home/kullanici/
├── gab/                  ← Laravel uygulaması (public değil)
│   ├── .env              ← web'den erişilemez
│   └── public/           ← document root buraya
└── public_html/          ← kullanılmaz / eski site yedeği
```
Deploy: `git pull` veya zip yükleme → `composer install --no-dev -o` → `php artisan migrate --force` → `optimize`.

### Senaryo B — SSH yok veya document root değiştirilemiyor (paylaşımlı hosting için en yaygın durum)
```
/home/kullanici/
├── gab/                  ← Laravel (vendor dahil, lokalde hazırlanmış)
└── public_html/          ← Laravel'in public/ içeriği kopyalanır
    └── index.php         ← ../gab/vendor ve ../gab/bootstrap yollarına uyarlanır
```
- `vendor/` lokalde `composer install --no-dev -o` ile hazırlanır ve zip olarak yüklenir.
- Migration ve cache komutları **tek seferlik cPanel cron** ile çalıştırılır (`php /home/.../gab/artisan migrate --force`). Web üzerinden çalışan bir "deploy endpoint" **kullanılmayacak** (güvenlik).
- Symlink yoksa `storage/app/public` yerine doğrudan `public_html/media` diski tanımlanır.
- `.env` daima `public_html` **dışında** kalır.

### Senaryo C — PHP < 8.2 seçilebiliyor veya cron yok
**Bu paket uygun değildir.** Yükseltme gerekir: aynı firmada daha üst bir Linux paket ya da Laravel/Bulut hosting. Brief §3 gereği, **onayınız olmadan farklı bir mimariye geçilmeyecek**.

---

## 4. Cron Görevleri (planlanan)

```
* * * * * /usr/local/bin/php /home/KULLANICI/gab/artisan schedule:run >> /dev/null 2>&1
```
Scheduler içeriği:
- `queue:work --stop-when-empty --max-time=50` (her dakika): e-posta bildirimleri ve görsel türevleri
- Sitemap yenileme (saatlik)
- Eski oturum, log ve geçici dosya temizliği (günlük)
- Veritabanı yedeği → `storage/backups` (günlük, 14 gün saklanır)

Zamanlanmış blog yayınları **cron'a bağlı değildir** (sorgu bazlı `published_at <= now()`).

---

## 5. Yayına Geçiş Ön Koşulları
1. Mevcut WordPress sitesinin tam yedeği (dosyalar + veritabanı) alınmış ve lokalde saklanıyor olmalı.
2. Domain, DNS ve cPanel erişim bilgileri doğrulanmış olmalı.
3. Yeni site önce staging alt domaininde test edilmiş olmalı.
4. Geçiş, düşük trafik saatinde yapılmalı. Geri dönüş planı: eski `public_html` arşivini geri yüklemek.
