# TECHNICAL ARCHITECTURE — GAB Architects

> Faz: PHASE 00 · Durum: **Öneri — onay bekliyor**
> Bu belge mimari önerisidir. Sürüm numaraları Phase 02'de, Turhost'un PHP sürümü doğrulandıktan sonra kesinleşecek.

---

## 1. Temel İlke

**Sunucuda yalnızca PHP + MySQL/MariaDB çalışacak.** Node.js yalnızca geliştirme makinesinde, CSS/JS derlemek için (Vite) kullanılacak. Derlenmiş `public/build/` klasörü sunucuya hazır olarak yüklenecek. Böylece standart cPanel paylaşımlı hosting yeterli olacak.

---

## 2. Teknoloji Yığını

| Katman | Seçim | Gerekçe |
|---|---|---|
| Dil | PHP 8.3 (min. 8.2) | Laravel'in güncel sürümleri için gerekli |
| Framework | Laravel (güncel kararlı sürüm: 12.x ya da 13.x) | 13.x, PHP 8.3+ gerektirir. Sunucunun PHP sürümüne göre seçilecek |
| Veritabanı | MySQL 8 / MariaDB 10.6+ | Turhost cPanel standardı. `utf8mb4_unicode_ci` |
| Şablon | Blade + Blade Components | Sunucu tarafında render; SEO dostu, JS'e bağımlı değil |
| CSS | Tailwind CSS v4 (geliştirmede derlenir) | Tasarım token'ları CSS değişkenleri üzerinden yönetilir |
| JS | Alpine.js + az miktarda vanilla JS | Hafif, hydration gerektirmez |
| Build | Vite (yalnızca lokal) | `npm run build` → `public/build` |
| Görsel işleme | `intervention/image` v3 (GD; Imagick varsa o) | WebP her zaman; AVIF sunucu destekliyorsa |
| Cache | `file` driver (Redis yoksa) | Paylaşımlı hosting uyumlu |
| Queue | `database` driver, cron ile işlenir | Daemon gerekmez |
| Mail | SMTP (Turhost e-posta hesabı) | `.env` üzerinden |
| Test | Pest veya PHPUnit + Laravel HTTP testleri | Feature testleri öncelikli |

### Önerilen paketler (minimum)
| Paket | Amaç | Alternatif |
|---|---|---|
| `astrotomic/laravel-translatable` | `*_translations` tablo yapısı (brief §8'e birebir uygun) | Kendi trait'imiz |
| `spatie/laravel-permission` | Roller + izinler (`roles`, `permissions` tabloları) | Basit `role` kolonu + Policy |
| `intervention/image` | Görsel küçültme ve WebP/AVIF dönüştürme | — |
| `spatie/laravel-sitemap` (opsiyonel) | XML sitemap | Kendi Blade şablonumuz (tercih: kendimiz yazalım) |

Activity log, 301 redirect yönetimi, medya kütüphanesi ve ayarlar **kendi kodumuzla** yazılacak; hepsi küçük modüller ve dış bağımlılık gerektirmiyor.

---

## 3. Uygulama Katmanları

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Site/            # Public controller'lar (ince)
│   │   └── Admin/           # CMS controller'ları
│   ├── Requests/            # Form Request doğrulama (Admin/*, Site/ContactRequest)
│   └── Middleware/          # SetLocale, AdminAuth, RedirectManager, SecurityHeaders
├── Models/                  # Eloquent modelleri + *Translation modelleri
├── Services/                # İş mantığı: MediaService, SeoService, ContactService,
│                            #   MenuService, SettingService, ActivityLogger, SitemapService
├── Policies/                # Model bazlı yetki
├── Enums/                   # PublishStatus, ContactStatus, Role
├── View/Components/         # Blade bileşenleri (site + admin)
└── Support/                 # LocalizedRoute helper, Seo DTO
```

- Controller'lar yalnızca HTTP işini yapar; iş mantığı Service katmanındadır.
- Doğrulama tamamen Form Request ile yapılır.
- Yetki kontrolü Policy + `can:` middleware ile yapılır.
- Yayın durumu için ortak scope kullanılır: `published()` = `status = published AND published_at <= now()`. **Zamanlanmış yayın cron gerektirmez**; sorgu zamanı kendisi kontrol eder.

---

## 4. CMS Altyapısı — **KARAR GEREKİYOR**

Brief, Blade + Tailwind + Alpine ile özel bir panel tanımlıyor. Değerlendirilecek iki seçenek var:

| | **A) Özel Blade Admin (brief'teki yaklaşım)** | **B) Filament (Laravel admin paneli)** |
|---|---|---|
| Sunucu gereksinimi | Yalnızca PHP | Yalnızca PHP (Livewire; Node gerekmez) |
| Geliştirme süresi | Uzun: 12 modül sıfırdan yazılır | Belirgin şekilde kısa |
| Galeri sürükle-bırak sıralama, çok dilli sekmeler, dosya yükleme | Elle yazılır | Hazır bileşenler |
| Tasarım kontrolü | Tam | Tema ile özelleştirilebilir; GAB renklerine uyarlanabilir |
| Hata riski | Daha yüksek (daha fazla özel kod) | Daha düşük (olgun, test edilmiş bileşenler) |
| Ek bağımlılık | Yok | Filament + Livewire |
| Public site | Her iki seçenekte de özel Blade + Tailwind | Aynı |

**Öneri: B (Filament).** Public site her iki durumda da tamamen özel tasarım olacak. Filament yalnızca `/admin` için kullanılır. Bu seçimle CRUD, medya, çok dilli form ve rol modüllerinin daha hızlı ve daha az hatayla tamamlanması bekleniyor. Brief'te belirtilen yığından bir sapma olduğu için **onayınıza sunulmuştur**. Onay verilmezse A ile devam edilecek.

---

## 5. Dil

**Güncelleme 2026-11-08:** Kullanıcı kararıyla site **yalnızca Türkçe**. Çoklu dil sistemi (çeviri tabloları, `/tr`–`/en` ön ekleri, hreflang) kapsamdan çıkarıldı (TD-003 iptal).
- İçerik alanları doğrudan ana tablolarda tutulur (`projects.title`, `projects.slug`...).
- `APP_LOCALE=tr`; tarih ve sayı formatları Türkçe. Arayüz metinleri `lang/tr/*.php` altında.
- `<html lang="tr">`, Türkçe slug üretimi (`ç→c`, `ğ→g`, `ı→i`, `ö→o`, `ş→s`, `ü→u`).

---

## 6. Tema Sistemi (Dark/Light)

- Token'lar `:root` ve `[data-theme="dark"]` üzerinde CSS değişkenleri olarak tanımlanır. Tailwind bu değişkenleri kullanır (`bg-surface`, `text-muted`...).
- `<head>` içindeki küçük bir inline script, sayfa render edilmeden önce `localStorage` → `prefers-color-scheme` sırasıyla temayı uygular (FOUC önlenir).
- **Varsayılan tema: Dark** (marka algısı için önerilir; onay bekliyor).
- Geçişte 200–300 ms renk animasyonu uygulanır. `prefers-reduced-motion` aktifse animasyon kapanır.
- Görseller her iki temada da aynı kalır. Logo için iki varyant (açık/koyu) kullanılır.

---

## 7. Medya Mimarisi

- Orijinaller `storage/app/media/originals/` altında saklanır (public değil).
- Türevler `storage/app/public/media/{id}/` altında: `w480`, `w960`, `w1600`, `w2400` × `webp` (+ `avif` destek varsa), ayrıca tarayıcı uyumluluğu için bir `jpg` fallback.
- Blade `<x-picture>` bileşeni `<picture>` + `srcset` + `sizes` + `loading="lazy"` + `width/height` (CLS önleme) üretir.
- Türev üretimi yükleme anında senkron yapılır. Büyük dosyalarda zaman aşımı riski varsa iş `database` queue'ya atılır ve cron ile işlenir.
- Yükleme güvenliği: MIME + uzantı + `getimagesize` doğrulaması, rastgele dosya adı (ULID), SVG yüklemesi yalnızca Super Admin'e açık ve sanitize edilerek, en fazla 20 MB.
- `media` tablosu kullanım alanlarını polimorfik `mediables` pivotu ile izler. Kullanılan bir görsel silinmeye çalışıldığında uyarı verilir.

---

## 8. SEO Altyapısı

- `SeoService` her sayfa için şunları üretir: title, description, canonical, hreflang (yalnızca mevcut çeviriler için + `x-default` → tr), OG ve Twitter kartları.
- Schema.org: `Organization` / `LocalBusiness` (yalnızca doğrulanmış bilgilerle), `BreadcrumbList`, `CreativeWork` (proje), `Article` (blog), `Service`.
- `/sitemap.xml`: dil bazlı ve `xhtml:link` alternatifleri ile. İçerik değiştiğinde cache temizlenir.
- `robots.txt` dinamik üretilir. Production dışı ortamlarda `Disallow: /` olur.
- `redirects` tablosu (brief'e ek): 301/302 yönetimi, middleware ile uygulanır. Eski WordPress URL'leri buraya girilir.

---

## 9. Güvenlik

| Konu | Uygulama |
|---|---|
| CSRF | Laravel varsayılanı (tüm formlarda) |
| XSS | Blade `{{ }}` escape. Zengin metin (blog, proje içeriği) kaydedilirken HTML Purifier benzeri bir allowlist ile temizlenir |
| SQL Injection | Eloquent / Query Builder; ham sorgu yok |
| Şifre | `bcrypt`/`argon2id` (Laravel `Hash`) |
| Login | 5 deneme / dakika rate limit + IP bazlı kilit, oturum yeniden üretimi, "beni hatırla" opsiyonel |
| 2FA | Opsiyonel (Phase 07'de değerlendirilir) |
| Admin | `/admin` → `auth` + `role` middleware. `noindex` |
| Yetki | Policy bazlı; Editor rolü kullanıcı/ayar ekranlarına erişemez |
| Dosya | §7'deki kurallar; `storage` dışında yazılabilir dizin yok |
| Header'lar | CSP, `X-Frame-Options`, `Referrer-Policy`, `Permissions-Policy`, HSTS (SSL aktifleştikten sonra) |
| Spam | Honeypot + zaman tuzağı + rate limit. Gerekirse Cloudflare Turnstile (çerez gerektirmeyen seçenek) |
| Hata | `APP_DEBUG=false`, özel 404/500/503 sayfaları |
| Çerez | Yalnızca zorunlu çerezler + izin bazlı analitik. İzin alınmadan Google Analytics/Maps yüklenmez (harita için tıklayınca yükleme) |
| Loglar | `activity_logs`: kullanıcı, işlem, model, önceki ve sonraki değer, IP, zaman |

---

## 10. Performans Hedefleri

| Metrik | Hedef (mobil) |
|---|---|
| LCP | < 2.5 sn |
| CLS | < 0.05 |
| INP | < 200 ms |
| Toplam JS (sayfa başı) | < 50 KB gzip |
| Lighthouse Perf / SEO / A11y / BP | ≥ 90 / 100 / ≥ 95 / ≥ 95 |

Teknikler: hero görseline `fetchpriority="high"` + preload, fontların **self-host** edilmesi (Google Fonts CDN yerine; hem KVKK hem performans açısından), `font-display: swap` + subset, sayfa/fragment cache (`file`), `config:cache`, `route:cache`, `view:cache`, `.htaccess` üzerinden gzip/brotli ve uzun süreli cache header'ları.

---

## 11. Ortamlar

| Ortam | Yer | Not |
|---|---|---|
| local | Geliştirici makinesi (Windows) | Laravel Herd veya XAMPP/Laragon + Node |
| staging | Turhost alt domaini (ör. `yeni.gabarchitects.com`) — **önerilir** | `noindex`, HTTP basic auth |
| production | `gabarchitects.com` | Yalnızca test edilmiş sürüm |

Git deposu yalnızca kaynak kodu içerir. `.env`, `vendor/`, `node_modules/`, `storage/*` ve `public/build` commit edilmez (dağıtım paketi ayrıca üretilir).
