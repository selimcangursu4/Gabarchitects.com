# PROJECT STRUCTURE — GAB Architects

> Faz: PHASE 02 · Laravel 13 · PHP ≥ 8.3

```
app/
├── Enums/                      PublishStatus, PublicationState, ContactStatus, ListingStatus, Role
├── Http/
│   ├── Controllers/Site/       Public sayfa controller'ları (ince; iş mantığı yok)
│   └── Middleware/
│       └── HandleRedirects     404 → CMS yönlendirmeleri (global middleware)
├── Models/
│   ├── Concerns/
│   │   ├── HasSlug             Türkçe slug + benzersizlik (soft delete dahil)
│   │   ├── HasPublishing       draft/published/zamanlanmış, published() scope
│   │   ├── HasMediaGallery     cover() + sıralı gallery() (mediables)
│   │   └── FlushesCache        kayıt değişince cache anahtarlarını temizler
│   └── *.php                   Media, Service, Project, ProjectCategory, Page, BlogPost, BlogCategory,
│                               Reference, Property, Faq, TeamMember, Menu, MenuItem, ContactMessage,
│                               SiteSetting, ActivityLog, Redirect, User
├── Providers/AppServiceProvider  strict mode (dev), Gate::before (Super Admin), header/footer view composer
├── Services/
│   ├── MediaService            görsel doğrulama, orijinal saklama, WebP/AVIF türevleri, silme
│   └── SettingService          önbellekli site ayarları → settings('anahtar')
└── Support/helpers.php         settings()

config/
├── gab.php                     ilk admin hesabı, seed_media
├── media.php                   genişlikler, kalite, AVIF, izinli MIME'lar
└── filesystems.php             + 'media' diski (public/media)

database/
├── data/site-content.php       mevcut sitedeki içerik (seeder kaynağı)
├── migrations/                 2026_11_08_2000xx_*  (media, portfolio, content, system)
└── seeders/
    ├── assets/legacy/          mevcut siteden alınan orijinal görseller (13 adet)
    ├── RolesAndPermissionsSeeder, AdminUserSeeder, SiteContentSeeder

resources/
├── css/app.css                 tasarım sistemi (DESIGN_SYSTEM.md)
├── js/app.js                   Alpine: tema, header, scroll reveal
└── views/
    ├── components/layouts/site.blade.php
    ├── components/site/        header, footer, logo, theme-toggle, button, section-label, picture, project-card
    ├── pages/home.blade.php
    └── errors/404.blade.php

public/
├── build/                      Vite çıktısı (gitignore; deploy paketinde var)
└── media/                      görsel türevleri (gitignore; seeder/CMS üretir)

storage/app/private/media/originals/   orijinal görseller (web'den erişilemez)

tests/Feature/
├── HomePageTest                ana sayfa, içerik DB'den, taslak gizleme, menü cache, alt metin, 404
├── PublishingTest              taslak/yayın/zamanlanmış, cron'suz yayın, Türkçe slug, önceki/sonraki
├── MediaServiceTest            türevler, büyütme yok, MIME reddi, galeri sırası, kullanım sayısı, silme
└── InfrastructureTest          roller/izinler, 301 yönlendirme, ayar cache'i, boş hukuki sayfalar

docs/                           proje dokümantasyonu ve takip dosyaları
```

## Katman kuralları
- **Controller:** yalnızca sorgu ve view. İş mantığı `Services/` altında.
- **Doğrulama:** Form Request (PHASE 03 iletişim formu, PHASE 04 Filament formları).
- **Yetki:** Spatie izinleri + Policy (PHASE 04).
- **Cache:** yalnızca düz dizi/scalar (Laravel 13 `serializable_classes=false`; TD-022).
- **Blade:** aynı şablonda `@php(...)` kısa yazım ile `@php … @endphp` karıştırılmaz (BUG-002).

## Komutlar
| Komut | Açıklama |
|---|---|
| `php artisan migrate:fresh --seed` | Veritabanını sıfırdan kur + mevcut site içeriğini aktar |
| `php artisan db:seed --class=SiteContentSeeder` | Yalnızca içeriği tekrar aktar (idempotent) |
| `php artisan test` | Testler (SQLite bellek içi, görsel seed kapalı) |
| `npm run build` | CSS/JS derle (production sunucusunda Node gerekmez) |
| `php artisan serve --host=127.0.0.1 --port=8010` | Lokal sunucu |
