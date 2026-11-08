# BUG TRACKER

| ID | Tarih | Faz | Açıklama | Önem | Durum |
|---|---|---|---|---|---|
| BUG-001 | 2026-11-08 | 01 | `lang="tr"` altında CSS `uppercase` "Architects" → "ARCHİTECTS" (noktalı İ) | Orta | Düzeltildi: İngilizce kelimeler `lang="en"` |
| BUG-002 | 2026-11-08 | 01 | Aynı Blade şablonunda `@php(...)` ve `@php … @endphp` birlikte kullanılınca derleme bozuluyor (500) | Yüksek | Düzeltildi: blok yazım kullanılıyor. **Kural:** bir şablonda kısa `@php()` ile blok `@php` karıştırılmayacak |
| BUG-003 | 2026-11-08 | 01 | Kurulum yarıda kaldığı için `APP_KEY` üretilmemişti (500) | Yüksek | Düzeltildi: `key:generate` |
| BUG-004 | 2026-11-08 | 01 | Header'da Alpine `:class` ile statik renk sınıfları çakışıyordu | Düşük | Düzeltildi: data attribute + CSS |
| BUG-005 | 2026-11-08 | 01 | Proje görselleri eski sunucudan eksik indi (yarım dosya) | Orta | **Kısmen çözüldü:** 16'dan 13'ü tamamlandı. Eksik: Sinpaş, Zekeriyaköy, Acıbadem. Orijinaller müşteriden istenecek |
| BUG-006 | 2026-11-08 | 01 | Zorlu Center görselinin kaynağında alt bant var | Düşük | **Açık:** öne çıkanlardan çıkarıldı |
| BUG-007 | 2026-11-08 | 02 | Menü cache'i model nesnesi tuttuğu için 500 (Laravel 13 `serializable_classes=false`) | Yüksek | Düzeltildi: cache'e düz dizi (TD-022) |
| BUG-008 | 2026-11-08 | 02 | `RefreshDatabase` + `$seed=true` seed verisini sonraki test sınıflarına sızdırıyordu (yanlış başarısız test) | Orta | Düzeltildi: seed `setUp` içinde, test transaction'ında |
| BUG-009 | 2026-11-08 | 02 | Alt metin testi görsel olmadığı için hiçbir şey doğrulamıyordu (risky) | Düşük | Düzeltildi: testte sahte medya üretiliyor, en az 6 görsel doğrulanıyor |
| BUG-010 | 2026-11-08 | 03 | Açılır menü: hover ile açılan menü fare tıklamasıyla kapanıyordu | Orta | Düzeltildi: fare tıklaması yalnızca açar, klavye aç/kapa, dışarı tıklama kapatır; tarayıcı testi eklendi |
| BUG-011 | 2026-11-08 | 03 | Filtre boşken arama `/kiralik?` (boş sorgu dizesi) üretiyordu | Düşük | Düzeltildi |
| BUG-012 | 2026-11-08 | 03 | `<option x-show>` ile ilçe süzme Safari'de çalışmaz | Orta | Düzeltildi (yazarken): ilçe listesi JS ile yeniden kuruluyor |
| BUG-013 | 2026-11-08 | 04 | Galeri kaydı görsel kaybediyordu: `collect()->map('intval')` anahtarı sayı tabanı olarak geçiriyor (`intval("1", 1) = 0`) | **Yüksek** | Düzeltildi; Livewire testi galeri sırasını doğruluyor. Kural CLAUDE.md'ye eklendi |
| BUG-014 | 2026-11-08 | 04 | İşlem kaydı etiketi olmayan alanı (`title`) okuyunca strict mode hatası (Web Formu detayı 500) | Yüksek | Düzeltildi: yalnızca var olan alanlar okunuyor |
| BUG-015 | 2026-11-08 | 04 | Galeri alanında Filament dosya yolu kontrolü strict mode hatası veriyordu | Orta | Düzeltildi (TD-043) |
| BUG-016 | 2026-11-08 | 04 | Panelde slug tabanlı kayıt adresi (başlık değişince adres değişir; test 404) | Düşük | Düzeltildi (TD-041) |
| BUG-018 | 2026-11-08 | 04 | **Kullanıcı bildirdi:** Panelde kullanıcı adı "GAB Y?netici". `.env` ve `.env.example`'a PowerShell `Add-Content` ile ANSI kodlamayla yazılmıştı; seeder bozuk adı veritabanına aktardı | Orta | Düzeltildi: iki dosya UTF-8 olarak yeniden yazıldı, kullanıcı adı "GAB Yönetici" yapıldı; tüm dosya ve veritabanı metinleri tarandı (başka bozulma yok). Önlem: `EncodingTest` + CLAUDE.md kuralı |
| BUG-017 | 2026-11-08 | 04 | **Kullanıcı bildirdi:** İlan/Proje/Hizmet/Blog listesinde "Düzenle" → `/admin/properties/ornek-ilan-12/edit` 404. Filament kaydı ID ile arıyor (TD-041) ama adresi Laravel `getRouteKey()` = slug ile üretiyordu. Testler adresi elle ID ile kurduğu için yakalamadı | **Yüksek** | Düzeltildi: `UsesIdRecordUrls` trait'i adres üretiminde ID geçirir. Regresyon testi: liste sayfalarındaki tüm panel bağlantıları açılıyor (düzeltme kaldırılınca test başarısız olduğu doğrulandı) |
| BUG-019 | 2026-11-08 | 06 | Hakkımızda "Değerlerimiz": 4 sütunda `text-h2` boyutunda "Sürdürülebilirlik" sütuna sığmıyor, 1440 px'te sayfa 20 px yatay kayıyor | Orta | Düzeltildi: lg'de `text-h3` + `min-w-0 break-words`. 14 sayfa × 7 genişlikte (360–1920) taşma taraması temiz |
| BUG-020 | 2026-11-08 | 06 | Ana sayfa LCP öğesi (hero başlığı) `data-reveal` ile JS çalışana kadar görünmez → LCP gecikiyor | Orta | Düzeltildi: hero başlığında reveal yok. Test: `SeoTest::test_hero_heading_is_not_hidden_until_javascript_runs` |
| BUG-021 | 2026-11-08 | 06 | Header `init()` içinde `window.scrollY` okuması zorunlu yeniden yerleşim (forced reflow) → TBT ~140 ms | Düşük | Düzeltildi: ilk okuma `requestAnimationFrame` içinde (ana sayfa TBT 280 → 40 ms) |
| BUG-023 | 2026-11-08 | 07 | Yanıtlarda `X-Powered-By: PHP/8.4.27` (sürüm sızıntısı). Başlığı PHP SAPI eklediği için Symfony'den silinemiyordu | Düşük | Düzeltildi: `header_remove()` + `.htaccess` `Header unset` |
| BUG-024 | 2026-11-08 | 07 | Bakım (503) yanıtı güvenlik başlıklarını almıyordu, nonce boştu: bakım kontrolü güvenlik middleware'inden önce çalışıyordu | Düşük | Düzeltildi: `SecurityHeaders` en dışta (`prepend`). Test: `SecurityTest::test_maintenance_page` |
| BUG-025 | 2026-11-08 | 07 | Panelde tekil görsel alanı, MediaService'in reddettiği dosyada (bozuk / piksel sınırı) sunucu hatası veriyordu | Orta | Düzeltildi: alan altında doğrulama mesajı. Test: `test_panel_upload_errors_are_shown_on_the_field_not_as_server_error` |
| BUG-026 | 2026-11-08 | 07 | Laravel'in `local` disk `storage/{path}` GET/PUT route'ları açıktı (imzalı URL ile; kullanılmıyordu) | Düşük | Düzeltildi: `'serve' => false`; route'lar kaldırıldı, test eklendi |
| BUG-027 | 2026-11-08 | Railway | Konteyner "AH00534: More than one MPM loaded" ile açılmıyordu | Yüksek (test ortamı) | Düzeltildi: imajda ve başlangıçta yalnızca `mpm_prefork` (mod_php) |
| BUG-028 | 2026-11-08 | Railway | `SamplePropertySeeder` kendi production kontrolü yüzünden `GAB_SEED_SAMPLES`'ı yok sayıyordu (0 ilan) | Orta | Düzeltildi + `gab:seed-samples-if-missing`; test: `test_sample_listings_only_with_flag_and_only_once` |
| BUG-029 | 2026-11-08 | 07 | `.gitignore` Filament varlıklarını dışlıyordu (TD-045 ile çelişki) → başka sunucuda panel stilsiz açılırdı | Yüksek | Düzeltildi: `public/css|js|fonts/filament` depoda |
| BUG-022 | 2026-11-08 | 06 | Erişilebilirlik: sayfalamadaki pasif "Önceki/Sonraki" kontrastı 2.17:1 (`opacity-40`); ilan listesinde h1 → h3 başlık atlaması | Düşük | Düzeltildi: `text-muted` + `aria-disabled`; listeye gizli `h2` |

## Mevcut (eski) sitede tespit edilen sorunlar — referans amaçlı
- Tema demo verileri yayında (Chicago/Miami semt adları, "teste", "Uupo", "User registration is disabled for demo purpose").
- Proje kartları ve butonlar `#` bağlantılı.
- Telefon ve e-posta yok.
- Menüde aynı sayfaya giden iki öğe.
- Alt metinler boş veya tekrar eden metinler.
- Bazı görseller Houzez tema demo görselleri (`2016/10/19.jpg`, `44.jpg`) ve bir duvar kâğıdı sitesinden (`peakpx-3.jpg`); yeni siteye **alınmadı**.
