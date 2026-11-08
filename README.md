# GAB Architects — Kurumsal Web Sitesi & CMS

Laravel 13 · Blade · Tailwind CSS v4 · Alpine.js · Filament (planlanan) · MySQL/MariaDB (production) / SQLite (lokal)

Proje faz bazlı geliştiriliyor. Güncel durum için [docs/PROJECT_STATUS.md](docs/PROJECT_STATUS.md) ve [docs/PHASE_TRACKER.md](docs/PHASE_TRACKER.md) dosyalarına bakın.

## Lokal kurulum
```bash
composer install
npm install && npm run build
cp .env.example .env && php artisan key:generate
# .env → ADMIN_EMAIL (ilk Super Admin; ADMIN_PASSWORD boşsa rastgele üretilir)
touch database/database.sqlite && php artisan migrate:fresh --seed
php artisan serve --host=127.0.0.1 --port=8010
```

## Test
```bash
php artisan test
```

## Dokümantasyon
- [Proje analizi](docs/PROJECT_ANALYSIS.md) · [Teknik mimari](docs/TECHNICAL_ARCHITECTURE.md) · [Sayfa yapısı](docs/PAGE_STRUCTURE.md)
- [Tasarım sistemi](docs/DESIGN_SYSTEM.md) · [Hosting gereksinimleri](docs/HOSTING_REQUIREMENTS.md) · [Teknik kararlar](docs/TECHNICAL_DECISIONS.md)
- [Veritabanı şeması](docs/DATABASE_SCHEMA.md) · [Proje yapısı](docs/PROJECT_STRUCTURE.md)
