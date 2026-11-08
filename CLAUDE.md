# GAB Architects — Kurumsal Web Sitesi & CMS

Faz bazlı yürütülen bir proje. Her oturumun başında sırasıyla şunları oku:
1. `docs/PROJECT_STATUS.md`
2. `docs/PHASE_TRACKER.md`
3. `docs/CHANGELOG.md`
4. `docs/TECHNICAL_DECISIONS.md`

Kurallar:
- Aktif faz kullanıcı tarafından onaylanmadan bir sonraki faza geçme. Tamamlanmış fazları yeniden oluşturma.
- Gerçek olmayan proje, referans, müşteri veya firma bilgisi üretme. Eksik içeriği **[EKSİK]** / "ÖRNEK" olarak işaretle.
- Hedef sunucu Turhost paylaşımlı hosting: production'da Node.js çalışmaz. Vite çıktısı lokalde derlenir.
- "Önerildi" durumundaki teknik kararları kullanıcı onayı olmadan uygulama.
- Her fazın sonunda PROJECT_STATUS, PHASE_TRACKER ve CHANGELOG dosyalarını güncelle. Başarısız testleri başarılı olarak işaretleme.
- Dokümantasyon dili Türkçe; kod, tablo ve değişken adları İngilizce.
- **Kodlama:** Tüm dosyalar UTF-8. Windows'ta PowerShell `Add-Content`/`Set-Content`/`Out-File` ile kodlama belirtmeden dosyaya YAZMA (ANSI yazar, "ö" → "?"). Write/Edit araçlarını ya da `[IO.File]::WriteAllText(..., UTF8Encoding($false))` kullan. `tests/Unit/EncodingTest` bunu denetler.
- Marka: **GAB Architects**. Site yalnızca Türkçe (çoklu dil yok). Varsayılan tema Dark. Admin paneli Filament.
- Laravel uygulaması depo kökündedir. Lokal veritabanı SQLite; migration'lar MySQL uyumlu olmalı.
- Lokal sunucu: `php artisan serve --host=127.0.0.1 --port=8010` (8000 başka projede kullanılıyor).
- Cache'e yalnızca düz dizi/scalar koy (Laravel 13 cache nesne geri yüklemez).
- Blade'de aynı şablonda `@php(...)` kısa yazım ile `@php … @endphp` karıştırma.
- Testlerde `$seed = true` kullanma; `setUp()` içinde `$this->seed()` çağır.
- Yapı ve kurallar: `docs/PROJECT_STRUCTURE.md`, şema: `docs/DATABASE_SCHEMA.md`, sayfalar/menü: `docs/PAGE_STRUCTURE.md`.
- Tailwind sınıflarını dinamik üretme (`lg:grid-cols-{{ $n }}` gibi); açık eşleme kullan.
- View içinde veritabanı sorgusu yapma; veriyi controller'da hazırla.
- Gerçek ilan yok: lokal örnek ilanlar `is_sample=true` ve "ÖRNEK" etiketli; production'a konmaz.
- Collection'da `->map('intval')` gibi yerleşik fonksiyon adı geçirme (anahtar 2. parametre olur); closure kullan.
- Panel (Filament 5): `app/Filament`. Yetki = `app/Policies` (policy yoksa Filament reddeder). Görsel alanı = `App\Filament\Forms\MediaUpload`.
- Panel değişikliklerinde `tests/Feature/Admin` testlerini çalıştır (Livewire ile gerçek kaydetme).
- Sitede slug kullanan bir model için yeni panel resource'u: `$recordRouteKeyName = 'id'` **ve** `use UsesIdRecordUrls;` birlikte (BUG-017).
- Testlerde adresleri elle kurma; uygulamanın ürettiği bağlantıları test et.
- **CSP (TD-054):** genel sitede satır içi `<script>` yalnızca `nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}"` ile çalışır; `onclick=` gibi satır içi olay öznitelikleri çalışmaz (Alpine kullan). Dış kaynak (CDN, harita, analiz) eklenecekse önce `SecurityHeaders` CSP'si **ve** çerez bildirimi (TD-055) güncellenir; `SecurityTest::test_pages_load_no_third_party_resources` bunu yakalar.
- Hata sayfaları (`errors/*`, `x-layouts.error`) veritabanına dokunmaz; `settings()`/menü kullanma.
