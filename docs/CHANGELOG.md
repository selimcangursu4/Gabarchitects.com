# CHANGELOG

## [Railway test ortamı] — 2026-11-08 (TD-060)
- Yayında: https://gabarchitectscom-production.up.railway.app (arama motorlarına kapalı, ÖRNEK ilanlı).
- Docker (PHP 8.4 + Apache + .htaccess), Railway MySQL 8, `/data` kalıcı disk; APP_KEY diskte üretilir.
- **MySQL provası:** 8 migration + tüm seed'ler MySQL 8'de sorunsuz (TD-016 doğrulandı).
- Canlı doğrulama: 15 adres, gzip, HSTS, CSP, AVIF + 1 yıl önbellek, panel girişi, iletişim formu → Web Formları (test mesajı silindi) — tarayıcı testi 13/13.
- `TRUSTED_PROXIES` (yalnızca proxy arkasında), `GAB_SEED_SAMPLES`, `gab:seed-if-empty`, `gab:seed-samples-if-missing`; dil varsayılanı `tr`.
- Düzeltmeler: BUG-027 (Apache MPM), BUG-028 (örnek ilan seed), BUG-029 (Filament varlıkları depoda). Toplam 127 test.

## [PHASE 07] — 2026-11-08
### Eklendi
- `App\Http\Middleware\SecurityHeaders` (global, en dışta): güvenlik başlıkları, nonce tabanlı CSP (genel site), HSTS (HTTPS + production), panelde `X-Robots-Tag: noindex`, `X-Powered-By` kaldırma (TD-054).
- Hata sayfaları 403, 419, 429, 500, 503 — bağımsız `x-layouts.error` (veritabanı okumaz, sistem ayrıntısı yok) (TD-056).
- KVKK çerez bildirimi `x-site.cookie-notice`: metin Site Ayarları'ndan, metin değişince yeniden gösterilir, ilan sayfasında mobil WhatsApp/Ara çubuğunu örtmez (TD-055).
- `media.max_pixels` (50 MP) dekompresyon bombası koruması (TD-059).
- `.htaccess`: gizli/yapılandırma dosyalarına (`.env`, `.git`, `composer.*`, `*.log`, `*.sqlite`…) erişim engeli, `X-Powered-By` kaldırma.
- `SecurityTest` (21 test): başlıklar + CSP nonce eşleşmesi, panel başlıkları, HSTS, oturum çerezi bayrakları ve production varsayılanı, panel giriş deneme sınırı, şifre hash'i, CSRF (419), korumalı alan ataması, XSS (panel + site), SQL enjeksiyonu, SVG ve piksel bombası reddi, PHP gömülü görsel (yeniden kodlama, özel disk), panel yükleme hata mesajı, gizli dosyalar, production 500 (ayrıntı yok, 0 sorgu), bakım 503, 429/403, çerez bildirimi, dış kaynak yok, dil. Toplam 124 test.
- Tarayıcı testi (mobil 390 px, dokunmatik): çerez bildirimi, hamburger menü (kaydırma kilidi, odak, Escape), tema kalıcılığı, ilan alt çubuğu, iletişim formu gönderimi — 16/16; CSP altında 12 sayfada konsol hatası yok.
### Değişti
- Oturum çerezi production'da varsayılan `secure` (TD-057).
- `local` disk `'serve' => false`: `storage/{path}` GET/PUT route'ları kaldırıldı (TD-058).
- Panel görsel alanında reddedilen dosya artık alan altında mesaj (BUG-025).
- Layout'taki satır içi tema script'i CSP nonce'u taşır.
### Kaldırıldı
- Kullanılmayan `resources/views/welcome.blade.php`.
### Denetim
- `composer audit`: bilinen açık yok. `npm audit` (tüm bağımlılıklar): 0 açık.

## [PHASE 06] — 2026-11-08
### Eklendi
- Layout'ta tüm SEO etiketleri: dinamik title/description, `robots`, canonical (yalnızca `?page=` korunur), hreflang (tr + x-default), Open Graph (site_name, locale, type, title, description, url, image + boyut/alt), Twitter Card.
- `config/seo.php`: `SEO_INDEXABLE` (varsayılan yalnızca production), paylaşım görseli genişliği, isteğe bağlı `SEO_TWITTER_SITE`.
- `/sitemap.xml` (`SeoController`): yalnızca yayındaki sayfa, hizmet, proje, proje kategorisi, ilan, blog yazısı/kategorisi; ÖRNEK ilan ve metni olmayan hukuki sayfa hariç; portföy modülü kapalıysa ilanlar yok; `lastmod`.
- `/robots.txt` (route): production'da `/admin`, `/livewire` kapalı + Sitemap satırı; diğer ortamlarda `Disallow: /`. `public/robots.txt` kaldırıldı.
- `App\Support\StructuredData`: Organization, WebSite, BreadcrumbList, BlogPosting, CreativeWork, Service, RealEstateListing (JSON-LD, `</script>` kaçışı korumalı).
- `x-site.breadcrumbs` bileşeni: görünür breadcrumb + aynı veriden BreadcrumbList; page-header, proje, ilan ve blog detayında ortak.
- `Media::shareImage()`: hedef genişliğe en yakın var olan türev.
- `public/.htaccess`: gzip (`mod_deflate`), sürümlü dosyalara 1 yıl `immutable` önbellek, `nosniff`, AVIF/WebP/WOFF2 MIME.
- `SeoTest` (16 test): meta/OG her sayfada, kanonik, robots (ortam), `SEO_INDEXABLE` boş/tanımsız/false durumları, noindex kuralları, sitemap (yayın durumu, örnek ilan, modül, her adres açılıyor), paylaşım görseli + varsayılan görsel cache temizliği, şemalar, JSON-LD XSS, statik robots dosyası yok, hero LCP. Toplam 103 test.
### Değişti
- noindex: filtrelenmiş ilan listesi, ÖRNEK ilan, metni girilmemiş hukuki sayfa.
- Ana sayfa hero başlığında reveal animasyonu kaldırıldı (LCP).
- Header ilk `scrollY` okuması `requestAnimationFrame` içinde (forced reflow / TBT).
- Sayfalama pasif metin kontrastı, ilan listesine gizli h2 (erişilebilirlik).
- Hakkımızda değerler listesi taşması (BUG-019).
- Site Ayarları yardım metinleri: numara boşsa buton "soluk ve tıklanamaz" (TD-047 ile uyumlu).
- `Page` modeli kaydedilince varsayılan paylaşım görseli cache'i temizlenir.

## [PHASE 04 düzeltmeleri] — 2026-11-08
- BUG-017: Slug'lı modellerde panel "Düzenle" bağlantısı 404 → `UsesIdRecordUrls`; panel bağlantılarını tarayan regresyon testi.
- Kullanıcı kararı: Sayfalar, Medya Kütüphanesi, Menüler, Yönlendirmeler sidebar'dan kaldırıldı (TD-046); adresle erişim sürüyor, test eklendi.
- BUG-018: "GAB Y?netici" → .env UTF-8 düzeltmesi, DB taraması, `EncodingTest`.
- Kullanıcı kararı (TD-047): İlan detayında form kaldırıldı; WhatsApp (hazır mesajlı) + Ara butonları, mobil sabit alt çubuk. Site Ayarları'na "WhatsApp numarası" alanı. `App\Support\Phone`, `x-site.property-contact`. Testler: PhoneTest, PropertyContactTest (toplam 85).
- Kullanıcı kararı: "Bizimle iletişime geçin" yedeği kaldırıldı; WhatsApp + Ara her zaman görünür, numara yoksa pasif. Dashboard'da "İlan butonları: Pasif" uyarısı (Site Ayarları'na bağlantı). Toplam 87 test.

## [PHASE 04] — 2026-11-08
### Eklendi
- Filament 5.11 (Livewire 4) yönetim paneli `/admin`: GAB marka rengi, varsayılan koyu tema, Türkçe arayüz, şifre sıfırlama, profil.
- Resource'lar: İlanlar, Projeler, Proje Kategorileri, Hizmetler, Referanslar, Blog Yazıları, Blog Kategorileri, SSS, Ekip, Web Formları, Sayfalar, Medya Kütüphanesi, Menüler, Yönlendirmeler, Kullanıcılar, İşlem Kayıtları; Site Ayarları sayfası.
- Dashboard widget'ları: StatsOverview, LatestContactMessages, LatestProjects, RecentActivity (izne göre görünür).
- `MediaUpload` form alanı: yüklemeler doğrudan MediaService'e (orijinal + WebP/AVIF), galeri sürükle-bırak sıralama.
- `CommonFields` (başlık+slug, yayın, SEO, zengin metin) ve `CommonColumns` (küçük görsel, yayın durumu rozeti/filtresi).
- Policy'ler: PermissionPolicy tabanı + Page, ContactMessage, Media, User, ActivityLog özel kuralları.
- `LogsActivity` (oluştur/güncelle/sil/geri yükle + eski/yeni değerler), `SanitizesHtml` (symfony/html-sanitizer), `RecordSuccessfulLogin` listener.
- `App\Support\Slug`.
- Testler: AdminPanelTest (tüm sayfalar, yetki), AdminCrudTest (Livewire ile gerçek CRUD: ilan+görsel, galeri sırası, sayfa içeriği, menü, ayarlar, web formu, kullanıcı/şifre, medya silme koruması, toplu yükleme, giriş kaydı). Toplam 70 test.
### Değişti
- `Gate::before` yalnızca izin adları için (Super Admin policy'deki sabit yasakları aşmaz).
- Panelde kayıt anahtarı ID (sitede slug).
- phpunit `memory_limit` 512M.

## [PHASE 03] — 2026-11-08
### Eklendi (kullanıcı talepleri 1–9)
- Hero portföy filtresi (solda metin, sağda filtre): işlem tipi, emlak tipi, il → ilçe, fiyat, m², oda; kanonik `/satilik` `/kiralik` adresleri.
- Ana sayfa "Portföyümüz": öne çıkan son 9 ilan, e-ticaret tarzı `x-site.property-card`.
- `/portfoy`, `/satilik`, `/kiralik` (yapışkan filtre paneli, sıralama, sayfalama), `/portfoy/{slug}` (galeri, ilan bilgileri, bilgi talebi formu, benzer ilanlar).
- Header: Anasayfa, Kurumsal ▾ (Hakkımızda, Referanslar), Portföyümüz ▾ (Satılık, Kiralık) — hover/tık/klavye uyumlu açılır menü.
- Sayfalar: Hakkımızda (+SSS), Referanslar (tüm aktifler), Hizmetler + detay, Projeler (devam eden/tamamlanan, kategori) + detay (künye, galeri, önceki/sonraki, ilgili), Blog + kategori + detay, İletişim, hukuki sayfalar.
- İletişim formu (`x-site.contact-form`): ana sayfa "Bir proje konuşalım", iletişim sayfası, ilan detayı → `contact_messages` + e-posta bildirimi; honeypot, süre damgası, rate limit.
- `PropertySearch` servisi, `PropertySearchRequest`, `ContactRequest`, `ContactService`, `ContactMessageReceived` mail, `EnsureModuleEnabled` middleware.
- Enum'lar: `ListingType`, `ProjectProgress`. `config/real_estate.php`.
- `SamplePropertySeeder` (yalnızca local, "ÖRNEK" etiketli) + `php artisan gab:purge-samples`.
- Testler: PropertySearchTest, ContactFormTest, SitePagesTest (toplam 53).
### Değişti
- Şema: properties (listing_type, property_type, city, district, net_area_m2, room_count, bathrooms, is_featured, is_sample + indeksler), projects.progress, menu_items.url nullable, contact_messages.source.
- Öne çıkan projeler: son eklenen 4 (önceden sort_order); referanslar ana sayfada son 16.
- Gayrimenkul modülü varsayılan açık.

## [PHASE 02] — 2026-11-08
### Eklendi
- `spatie/laravel-permission` 8.5; roller: super_admin, admin, editor; 15 izin.
- Migration'lar: media/mediables, services, project_categories, projects, project_service, pages, blog_categories, blog_posts, client_references, properties, faqs, team_members, menus, menu_items, contact_messages, site_settings, activity_logs, redirects. `users` tablosuna is_active, last_login_*, soft delete.
- 18 model; trait'ler: HasSlug (Türkçe slug), HasPublishing (taslak/yayın/zamanlanmış), HasMediaGallery, FlushesCache; enum'lar: PublishStatus, PublicationState, ContactStatus, ListingStatus, Role.
- `MediaService` (orijinal private diskte; WebP + AVIF türevleri `public/media`), `SettingService` + `settings()` helper.
- `HandleRedirects` global middleware (404 → CMS yönlendirmesi; `/about`, `/contact-2` → 301).
- Seeder'lar: RolesAndPermissions, AdminUser (.env), SiteContent (mevcut site içeriği + 13 orijinal görsel `database/seeders/assets/legacy`).
- `config/gab.php`, `config/media.php`, `media` diski.
- Testler: PublishingTest, MediaServiceTest, InfrastructureTest (toplam 27 test).
- `docs/DATABASE_SCHEMA.md`, `docs/PROJECT_STRUCTURE.md`.
### Değişti
- Ana sayfa, header ve footer artık veritabanından (Page, Project, Service, Reference, Menu, SiteSetting).
- `x-site.picture` Media modeli alır; AVIF `<source>` + WebP.
- Header'a alt menü desteği (masaüstü açılır, mobil iç içe).
### Kaldırıldı
- PHASE 01 prototip katmanı: `App\Support\SiteContent`, `gab:import-legacy-images` komutu, `config/navigation.php`, `public/images/content`.

## [PHASE 01] — 2026-11-08
### Eklendi
- Laravel 13.40 iskeleti (depo kökünde), SQLite, `APP_LOCALE=tr`, `Europe/Istanbul`.
- Tasarım sistemi: `resources/css/app.css` (token'lar, dark/light, tipografi ölçeği, grid, plan çizgileri, ölçü çizgisi, reveal, reduced-motion).
- Self-host fontlar: Instrument Serif + Manrope Variable (`@fontsource`). Alpine.js.
- Blade bileşenleri: layout, header (mobil menü + focus trap), footer, logo, tema düğmesi, buton, bölüm etiketi, picture, proje kartı.
- Ana sayfa (10 bölüm), mevcut sitedeki gerçek içerikle: `database/data/site-content.php`.
- Marka tasarımında 404 sayfası.
- `php artisan gab:import-legacy-images`: eski site görsellerini WebP türevlerine (640/1024/1600/2400) dönüştürür, yarım dosyaları reddeder.
- `tests/Feature/HomePageTest.php` (7 test).
- `docs/DESIGN_SYSTEM.md`.
### Değişti
- Kararlar: GAB Architects, Filament, yalnızca TR, varsayılan Dark, Turhost ertelendi (TECHNICAL_DECISIONS TD-002 … TD-018).
- PAGE_STRUCTURE: dil ön ekleri kaldırıldı. TECHNICAL_ARCHITECTURE §5 güncellendi. HOSTING: PHP ≥ 8.3.
### Kaldırıldı
- Laravel iskeletinin örnek testleri ve `welcome` route'u.

## [PHASE 00] — 2026-11-08
### Eklendi
- `docs/PROJECT_ANALYSIS.md`: mevcut site analizi, içerik envanteri (12 hizmet, 16 proje, 16 referans, 8 SSS), görsel ihtiyaçları, referans site incelemesi, riskler, müşteriye sorular.
- `docs/TECHNICAL_ARCHITECTURE.md`: teknoloji yığını, katmanlar, çoklu dil, tema, medya, SEO, güvenlik ve performans mimarisi.
- `docs/PAGE_STRUCTURE.md`: TR/EN URL haritası, sayfa bölümleri, menü, 301 yönlendirmeleri.
- `docs/HOSTING_REQUIREMENTS.md`: Turhost kontrol listesi, destek soru metni, A/B/C dağıtım senaryoları, cron planı.
- `docs/DESIGN_CONCEPT.md`: "Plan & Işık" ön tasarım konsepti.
- Takip dosyaları: PROJECT_STATUS, PHASE_TRACKER, BUG_TRACKER, TECHNICAL_DECISIONS, DEPLOYMENT_GUIDE.
- `CLAUDE.md`: oturum başlangıç kuralları.
