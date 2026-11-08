# TECHNICAL DECISIONS

Durumlar: **Önerildi** (onay bekliyor) · **Kabul** · **Reddedildi** · **İptal**

| ID | Karar | Durum | Gerekçe / Not |
|---|---|---|---|
| TD-001 | Laravel + Blade + Tailwind + Alpine + MySQL; Node yalnızca lokalde | Kabul (2026-11-08) | Brief §3 |
| TD-002 | Admin paneli için Filament (public site özel Blade) | **Kabul (2026-11-08)** | Kullanıcı onayı |
| TD-003 | Çeviriler `*_translations` tabloları | **İptal (2026-11-08)** | Kullanıcı kararı: site yalnızca Türkçe |
| TD-004 | Eksik çeviri → 404 | İptal | TD-003 ile birlikte |
| TD-005 | URL'ler Türkçe ve dil ön eki olmadan (`/projeler/{slug}`) | **Kabul (2026-11-08)** | Tek dil |
| TD-006 | Zamanlanmış yayın, sorgu bazlı (`published_at <= now()`) | Kabul | Cron bağımlılığını azaltır |
| TD-007 | Queue: `database` driver + cron | Kabul | Paylaşımlı hosting uyumu |
| TD-008 | Görsel türevleri WebP (+ destek varsa AVIF); orijinaller private diskte | Kabul | Brief §7.5, §9 |
| TD-009 | Fontlar self-host | Kabul | KVKK + performans |
| TD-010 | Varsayılan tema: Dark | **Kabul (2026-11-08)** | Kullanıcı onayı |
| TD-011 | Roller: `spatie/laravel-permission` | Kabul | Brief §7.8 |
| TD-012 | Ek tablolar: `redirects`, `faqs`, `team_members`, `mediables`, `project_service` | Kabul | 301 yönetimi, SSS, ekip, medya takibi |
| TD-013 | Gayrimenkul modülü varsayılan olarak kapalı | Kabul | Gerçek ilan yok |
| TD-014 | Marka adı: **GAB Architects** | **İptal (2026-11-08)** → TD-061 | Kullanıcı kararı |
| TD-061 | Marka adı: **GAB Gayrimenkul Proje Geliştirme**. Kaynak: Site Ayarları `site.brand` (panelden değişir), varsayılan `config('gab.brand')`. Logo: "GAB" + iki satır "Gayrimenkul / Proje Geliştirme". Var olan veritabanları migration ile güncellenir (`'in` → `'nin` dahil); iletişim mesajları ve işlem kayıtları değişmez. Alan adı, e-posta adresleri, depo adı (`gabarchitects.com`) değişmez | Kabul (2026-11-08, kullanıcı kararı) | "Sitede GAB Architects yazan her yeri GAB Gayrimenkul Proje Geliştirme olarak değiştir" |
| TD-015 | Turhost analizi ertelendi; geliştirme ve onay lokalde yapılacak | **Kabul (2026-11-08)** | Kullanıcı kararı. Yine de paylaşımlı hosting uyumu korunacak |
| TD-016 | Lokal veritabanı SQLite; migration'lar MySQL/MariaDB uyumlu yazılır | Kabul | Lokal makinede MySQL kurulu değil |
| TD-017 | Laravel iskeleti PHASE 01'de kurulur (tasarım prototipi gerçek Blade layout'u üzerinde yapılır); veritabanı/model işleri PHASE 02'de | Kabul | Prototipin sonradan yeniden yazılmasını önler |
| TD-018 | Lokal PHP 8.4. Production için PHP sürümü kullanılan Laravel sürümüne göre belirlenecek (bkz. HOSTING_REQUIREMENTS) | Kabul | |
| TD-019 | Medya türevleri `public/media` diskine yazılır (storage:link/symlink gerekmez); orijinaller `storage/app/private` | Kabul (2026-11-08) | Turhost'ta symlink izni belirsiz; paylaşımlı hosting uyumu |
| TD-020 | Referans tablosu adı `client_references` (model `Reference`) | Kabul | `references` MySQL ayrılmış kelimesi |
| TD-021 | `project_images` / `property_images` yerine polimorfik `mediables` | Kabul | Tek galeri yapısı + medya kullanım takibi |
| TD-022 | Cache'e yalnızca düz dizi/scalar konur | Kabul | Laravel 13 varsayılanı `cache.serializable_classes=false` (güvenlik); nesneler geri yüklenmez |
| TD-023 | Görsel işleme kütüphanesiz (GD) — intervention/image eklenmedi | Kabul | Gereksiz paket kuralı; GD WebP/AVIF destekliyor |
| TD-024 | 301 yönlendirmeleri yalnızca 404 yanıtında kontrol edilir (global middleware, cache'li harita) | Kabul | Normal sayfalara ek sorgu maliyeti yok |
| TD-025 | `Model::shouldBeStrict()` geliştirme ortamında açık | Kabul | N+1 (lazy loading) ve hatalı alan erişimini erken yakalar |
| TD-026 | Panelden yüklemede AVIF türevleri kuyruğa alınmalı (PHASE 04) | Önerildi | Seeder ölçümü: 13 görsel AVIF ile ~36 sn |
| TD-027 | Gayrimenkul modülü **varsayılan açık** (TD-013 değişti) | Kabul (2026-11-08) | Kullanıcı talebi 1–3: hero filtresi ve portföy sitenin merkezinde |
| TD-028 | Hero filtresi Space İstanbul'daki **işlevi** alır, görünüm GAB tasarım dilinde | Kabul | Brief §2: tasarım kopyalanmaz |
| TD-029 | İlan alanları: listing_type (sale/rent), property_type (config listesi), city, district, room_count (rooms'tan türetilir), is_featured, is_sample | Kabul | Filtreleme ve indeksler |
| TD-030 | Filtre parametreleri Türkçe, GET, paylaşılabilir; geçersiz değer → yok sayılır (hata sayfası yok) | Kabul | UX + SEO |
| TD-031 | Satılık/Kiralık aramaları kanonik `/satilik`, `/kiralik` adreslerine gider (JS); JS kapalıyken `/portfoy?islem=` | Kabul | Yinelenen içerik riskini azaltır |
| TD-032 | "Öne çıkan" listeler eklenme tarihine göre: son 9 ilan, son 4 proje, son 16 referans | Kabul | Kullanıcı talepleri 2, 6, 8 |
| TD-033 | Projelerde `progress` (ongoing/completed); devam edenler ayrı bölüm | Kabul | Talep 3. Mevcut 16 projenin durumu bilinmiyor → boş |
| TD-034 | Tek form bileşeni (ana sayfa, iletişim, ilan detayı); `source` alanı ile ayrılır; spam: honeypot + şifreli süre damgası (≥3 sn) + rate limit 5/10 dk | Kabul | Talep 9 |
| TD-035 | E-posta gönderilemese de form kaydı korunur (hata loglanır) | Kabul | Veri kaybı olmaz |
| TD-036 | Örnek ilanlar yalnızca `local` ortamda, `is_sample=true`, "ÖRNEK" etiketli, üretilmiş nötr görsellerle; `php artisan gab:purge-samples` ile silinir | Kabul | Gerçek ilan yok; gerçek proje görselleri yanıltıcı olurdu |
| TD-037 | Menü öğesinde `url` boş olabilir → açılır menü başlığı (buton) | Kabul | Talep 4 |
| TD-038 | Zengin metin (`body`) kaydederken temizlenir: `SanitizesHtml` → Filament'in symfony/html-sanitizer yapılandırması | **Kabul (PHASE 04)** | XSS savunması; ek paket yok |
| TD-039 | Panel yetkisi Laravel Policy'leri ile (Filament 5 policy yoksa reddeder); izin başına tek policy tabanı | Kabul | Brief §7.8; Editor kullanıcı/ayar/menü/form/log göremez |
| TD-040 | `Gate::before` Super Admin için yalnızca izin adlarında (`*.manage`) true döner | Kabul | Sabit yasaklar (web formu oluşturma, sayfa silme, kendini silme, son Super Admin) korunur |
| TD-041 | Panel kayıt anahtarı ID (`$recordRouteKeyName = 'id'` + adres üretimi için `UsesIdRecordUrls` trait'i) | Kabul | Başlık/slug değişse de düzenleme adresi sabit. İkisi birlikte gerekir (BUG-017) |
| TD-042 | Görsel alanlarının durumu medya ID'sidir; yükleme MediaService'e gider; alandan kaldırma dosyayı silmez | Kabul | Tek medya kütüphanesi; kullanımdaki görsel silinemez |
| TD-043 | Galeri alanında Filament dosya yolu kontrolü kapalı, yerine kayıtta "yalnızca var olan medya ID'leri" kontrolü | Kabul | Filament kontrolü alanı model sütunu sanıyor (strict mode hatası) |
| TD-044 | İşlem kaydı yalnızca oturum açmış kullanıcı işlemlerinde; HTML gövdeler 500 karakterle kısaltılır | Kabul | Seeder/ziyaretçi gürültüsü yok; log şişmez |
| TD-046 | Sayfalar, Medya Kütüphanesi, Menüler, Yönlendirmeler **sidebar'da gizli** (`$shouldRegisterNavigation = false`); işlevler ve adresler (`/admin/pages`, `/admin/media`, `/admin/menus`, `/admin/redirects`) korunuyor | Kabul (2026-11-08, kullanıcı kararı) | Brief §7.3/7.5/7.6 işlevleri silinmedi; tek satırla geri açılabilir |
| TD-047 | İlan detayında web formu yok; **WhatsApp** (ilan başlığı + no + bağlantı önceden doldurulmuş mesaj) ve **Ara** butonları; mobilde ekran altında sabit çubuk. Numaralar Site Ayarları: `contact.whatsapp`, `contact.phone` (ayrı alanlar: sabit hat WhatsApp kullanmayabilir). **Butonlar her zaman görünür** (kullanıcı kararı); numarası girilmemiş buton pasif gösterilir (iletişim sayfası yönlendirmesi kaldırıldı), panel ana ekranında uyarı çıkar | Kabul (2026-11-08, kullanıcı kararı) | Numara uydurulmaz; `App\Support\Phone` biçimlendirir. Ek (kullanıcı kararı): WhatsApp numarasını arayan ikinci "Hemen arayın" (sıra: WhatsApp → cep arama → sabit arama; numaralar aynıysa tek buton) |
| TD-048 | Sıkıştırma (`mod_deflate`) ve uzun tarayıcı önbelleği `public/.htaccess` ile; `max-age=1 yıl, immutable` yalnızca sürümlü dosyalarda (Vite hash'li `build/assets`, uuid klasörlü `media`, `?v=`'li Filament). HTML önbelleğe alınmaz | Kabul (PHASE 06) | Turhost Apache/LiteSpeed; Node/sunucu ayarı gerekmez. Ölçüm: HTML 146 → 17 KB, CSS 74 → 16 KB |
| TD-049 | Tam sayfa (HTML) önbelleği **yok** | Kabul (PHASE 06) | OPcache + `php artisan optimize` ile sayfalar 40–100 ms (lokal ölçüm). Formlarda CSRF token'ı var; sayfa önbelleği yanlış token/eski içerik riski taşır. Menü, ayarlar, footer hizmetleri, paylaşım görseli zaten cache'li |
| TD-050 | Dizine alma ortama bağlı: `SEO_INDEXABLE` (varsayılan: yalnızca `APP_ENV=production`). Kapalıyken `<meta name="robots" content="noindex">` + `robots.txt: Disallow: /` | Kabul (PHASE 06) | Lokal/staging içeriği Google'a gitmez. `public/robots.txt` dosyası yok; route üretir. Boş `SEO_INDEXABLE=` varsayılana düşer (production'da yanlışlıkla kapanmaz; testli) |
| TD-051 | `noindex, follow`: filtrelenmiş ilan listeleri, ÖRNEK ilanlar, metni girilmemiş hukuki sayfalar. Kanonik adreste yalnızca `?page=` korunur | Kabul (PHASE 06) | Yinelenen/ince içerik önlenir. Sitemap'e de girmezler |
| TD-052 | Schema.org JSON-LD: Organization + WebSite (ana sayfa, iletişim), BreadcrumbList (görünür breadcrumb ile aynı veriden, `x-site.breadcrumbs`), BlogPosting, CreativeWork (proje), Service, RealEstateListing (fiyat gizliyse Offer yok). Boş alan yazılmaz | Kabul (PHASE 06) | Telefon/logo gibi eksik bilgiler uydurulmaz; girildikçe şemaya otomatik eklenir |
| TD-053 | Paylaşım görseli: sayfanın kendi kapağı, yoksa ana sayfa kapağı; 1200 px'e en yakın **var olan** WebP türevi (mutlak adres) | Kabul (PHASE 06) | WebP; Facebook, X, LinkedIn, WhatsApp destekliyor. Ayrı JPEG türevi üretilmedi |
| TD-054 | Güvenlik başlıkları global middleware (`SecurityHeaders`, en dışta): nosniff, `X-Frame-Options: SAMEORIGIN`, Referrer-Policy, Permissions-Policy, COOP; HTTPS+production'da HSTS (1 yıl, alt alan adı hariç). Genel sitede CSP: script yalnızca `'self'` + nonce (+ Alpine için `'unsafe-eval'`), style `'unsafe-inline'` (style öznitelikleri), `object-src 'none'`, `frame-src 'none'`, `form-action 'self'`. Panelde CSP yok + `X-Robots-Tag: noindex` | Kabul (PHASE 07) | Filament/Livewire satır içi script kullanır. Alpine CSP derlemesi `x-data` ifadelerini yeniden yazmayı gerektirirdi; risk/kazanç dengesi için `'unsafe-eval'` kabul edildi. **Kural:** yeni satır içi `<script>` → `nonce="{{ Vite::cspNonce() }}"`; dış kaynak eklenecekse CSP + çerez bildirimi güncellenir |
| TD-055 | Çerez bildirimi **bilgilendirme** (tek "Anladım" butonu), onay bandı değil | Kabul (PHASE 07) | Sitede yalnızca zorunlu çerez var (oturum, XSRF) + tema için localStorage; dış kaynak yok (testli). Hiçbir şeyi değiştirmeyen "Reddet" butonu brief'teki sahte arayüz yasağına girerdi. Metin panelden; değişince yeniden gösterilir. **Analiz/reklam aracı eklenirse kabul/ret bandına dönüştürülmeli ve script'ler onaya bağlanmalı** |
| TD-056 | 403/419/429/500/503 sayfaları bağımsız `x-layouts.error` ile: veritabanı/menü/ayar okumaz, sistem ayrıntısı göstermez, noindex | Kabul (PHASE 07) | Veritabanı çökmüşken de açılır (testli: 0 sorgu). 404 sitenin tam düzenini kullanır (menü faydalı) |
| TD-057 | Oturum çerezi production'da varsayılan `secure` (boş `SESSION_SECURE_COOKIE=` de varsayılana düşer); httpOnly, SameSite=Lax | Kabul (PHASE 07) | SSL'siz production'da panel girişi çalışmaz — bilinçli; DEPLOYMENT_GUIDE'da SSL önce |
| TD-058 | `local` diskte `'serve' => false` | Kabul (PHASE 07) | Orijinal görseller hiçbir yoldan web'e açılmaz; kullanılmayan PUT yükleme route'u kalkar |
| TD-059 | Görsel yüklemede piksel sınırı `media.max_pixels` = 50 MP (dosya başlığından, görsel açılmadan) | Kabul (PHASE 07) | Dekompresyon bombası GD belleğini tüketip sunucuyu düşürmesin |
| TD-060 | **Test/önizleme ortamı: Railway** (Docker: PHP 8.4 + Apache + `.htaccess`, Railway MySQL, `/data` kalıcı disk). Production hedefi **Turhost olarak kalır** | Kabul (2026-11-08, kullanıcı: "Vercel gibi, Laravel/PHP ile çalışan bir yer seç; şimdilik test amaçlı") | Vercel PHP'yi resmi desteklemiyor ve dosya sistemi kalıcı değil → form/panel kayıtları ve görseller kaybolurdu (sahte arayüz). Railway: GitHub'dan otomatik derleme, kalıcı MySQL + disk; ayrıca Turhost öncesi **MySQL ve Apache/.htaccess provası**. Node yalnızca imaj derleme aşamasında. `TRUSTED_PROXIES=*` yalnızca burada; `SEO_INDEXABLE=false`; `GAB_SEED_SAMPLES=true` (ÖRNEK ilanlar) |
| TD-045 | Filament varlıkları (`public/css/filament`, `public/js/filament`, `public/fonts/filament`) depoya dahil | Kabul | Sunucuda Node/derleme gerekmez; Inter fontu yerel (dış istek yok) |
