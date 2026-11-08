# PHASE TRACKER

Durumlar: Bekliyor · Devam Ediyor · Kontrol Bekliyor · Tamamlandı · Hata Var

| Faz | Başlık | Durum | İlerleme | Not |
|---|---|---|---|---|
| PHASE 00 | Proje Analizi | Tamamlandı | %100 | |
| PHASE 01 | Tasarım Sistemi | Tamamlandı | %100 | Açık: vektör logo, 3 eksik görsel |
| PHASE 02 | Proje Altyapısı | Tamamlandı | %100 | MySQL doğrulaması PHASE 08'de |
| PHASE 03 | Frontend Geliştirme | Tamamlandı | %100 | Kullanıcı "devam et" ile onayladı (2026-11-08) |
| PHASE 04 | Yönetim Paneli (Filament) | Tamamlandı | %100 | Kullanıcı "sıradaki fazı tamamlayalım" ile onayladı (2026-11-08) |
| PHASE 05 | Çoklu Dil | **İptal** | — | Yalnızca Türkçe |
| PHASE 06 | SEO ve Performans | Tamamlandı | %100 | Kullanıcı "devam et" ile onayladı (2026-11-08). Lighthouse: performans 90–99, erişilebilirlik 100, en iyi uygulamalar 100, SEO 100 (production modu) |
| PHASE 07 | Güvenlik ve Test | **Kontrol Bekliyor** | %100 | 124/124 test; mobil tarayıcı testi 16/16; bağımlılık denetimi temiz |
| PHASE 08 | Turhost Deployment | Bekliyor | %0 | |
| PHASE 09 | Son Kontrol | Bekliyor | %0 | |

## PHASE 07 — brief §10 ve PHASE 07 listesi karşılığı
| Madde | Karşılık | Test |
|---|---|---|
| CSRF | Laravel `ValidateCsrfToken` (web grubu) | Token'sız POST → 419, kayıt yok |
| XSS | Blade kaçışı, zengin metin `SanitizesHtml`, CSP (nonce), JSON-LD `HEX_TAG` | Panel + site kaçış, AdminCrudTest temizlik, SeoTest JSON-LD |
| SQL Injection | Eloquent parametreli sorgular; filtre değerleri doğrulanır | Enjeksiyon dizeleri → 200, tablo sağlam |
| Şifre hash | bcrypt (`hashed` cast) | Ham değer hash, `Hash::check` |
| Login rate limiting | Filament: giriş 5/dk, şifre sıfırlama 2/dk | 6. deneme doğru şifreyle de reddedilir |
| Oturum güvenliği | httpOnly, SameSite=Lax, production'da secure | Çerez bayrakları + config varsayılanları |
| Yetki kontrolü | Policy'ler (PHASE 04) | AdminPanelTest, AdminCrudTest, InfrastructureTest + 403 sayfası |
| Dosya yükleme / MIME / güvenli ad | İçerikten MIME, izinli tip listesi, boyut + piksel sınırı, ULID ad, orijinal özel diskte, türevler yeniden kodlanır | SVG, GIF, PHP metni, poliglot, piksel bombası, panel hata mesajı |
| Admin rotaları | Filament auth middleware, rol + aktiflik, noindex | Misafir → giriş, rolsüz → 403 |
| .env / hassas dosyalar | Document root `public/`; `.htaccess` engeli | `/.env` 404, `.htaccess` kuralları |
| Production hata mesajları | `APP_DEBUG=false` + marka hata sayfaları | 500'de ayrıntı yok, 0 DB sorgusu |
| Spam koruması | PHASE 03: honeypot, süre damgası, rate limit | ContactFormTest + 429 sayfası |
| KVKK / çerez | Bilgilendirme bandı (panelden metin), hukuki sayfalar düzenlenebilir | Bant + metin sürümü + dış kaynak yok |
| Mobil | 390 px dokunmatik tarayıcı testi + 7 genişlikte taşma taraması | 16/16 |
| Dil | Yalnızca Türkçe | `lang="tr"`, `/en` 404, Türkçe tarih |
| Tema | Dark varsayılan, FOUC yok, tercih kalıcı | HomePageTest + tarayıcı |
| Bağımlılıklar | `composer audit`, `npm audit` | 0 açık |

## PHASE 06 — brief §9 karşılığı
| Brief | Karşılık | Durum |
|---|---|---|
| Meta title / description | Layout + kayıt bazlı `meta_title`/`meta_description` (panel SEO sekmesi), yoksa başlık/özet, yoksa site açıklaması | Tamamlandı |
| Open Graph / Twitter Cards | og:* + twitter:card; görsel: kayıt kapağı → ana sayfa kapağı | Tamamlandı |
| Canonical / Hreflang | Sorgu parametresiz kanonik (`?page` hariç); tek dil hreflang tr + x-default | Tamamlandı |
| XML sitemap / robots.txt | `/sitemap.xml`, ortama göre `/robots.txt` | Tamamlandı |
| Schema.org / Breadcrumb | JSON-LD 7 tip; görünür breadcrumb ile aynı veri | Tamamlandı |
| SEO dostu slug, alt metin, 404, 301 | PHASE 02–04'te (Türkçe slug, alt metin testi, marka 404, Yönlendirmeler) | Tamamlandı |
| Lazy loading, responsive images, WebP/AVIF, görsel boyutu | PHASE 02'de (`x-site.picture`, srcset, AVIF+WebP türevleri, width/height) | Tamamlandı |
| CSS/JS optimizasyonu, gereksiz JS | Vite derlemesi (CSS 16 KB, JS 20 KB gzip), Alpine tek kütüphane | Tamamlandı |
| Cache | Menü/ayar/paylaşım görseli cache, `.htaccess` tarayıcı önbelleği + gzip, OPcache + `optimize` (TD-048, TD-049) | Tamamlandı |
| Core Web Vitals | LCP 1,7–2,6 sn, CLS ≤ 0,06, TBT 0–40 ms (çoğu ölçüm) — lokal, gzip'li, Lighthouse mobil simülasyonu | Tamamlandı (gerçek değerler PHASE 08'de sunucuda ölçülecek) |

### Lighthouse (mobil, lokal: OPcache + optimize + gzip proxy, `SEO_INDEXABLE=true`)
| Sayfa | Performans | Erişilebilirlik | En iyi uyg. | SEO |
|---|---|---|---|---|
| Ana sayfa | 97 (3 ölçüm: 97/97/90) | 100 | 100 | 100 |
| Hakkımızda | 99 | 100 | 100 | 100 |
| Proje detayı | 94–96 | 100 | 100 | 100 |
| Projeler | 98 | 100 | 100 | 66–69* |
| Satılık | 99 | 100 | 100 | 66–69* |
| İlan detayı | 99 | 100 | 100 | 66–69* |
| Hizmetler / İletişim | 98 / 99 | 100 | 100 | 66–69* |

\* Lokal (noindex) modda ölçüldü: SEO'daki **tek** başarısız denetim `is-crawlable` (bilinçli noindex). Production modunda ölçülen 3 sayfanın üçü de 100. ÖRNEK ilan production'da da noindex'tir.

## PHASE 04 — brief §7 karşılığı
| Brief | Panel | Durum |
|---|---|---|
| 7.1 Dashboard | Panel: sayılar (proje toplam/yayında/taslak, ilan, blog, web formu/okunmamış), son talepler, son projeler, son işlemler | Tamamlandı |
| 7.2 Proje yönetimi | Projeler: CRUD, taslak/yayın/zamanlanmış, kategori, kapak, çoklu görsel + sıralama, devam eden/tamamlanan, öne çıkan, SEO | Tamamlandı |
| 7.3 Sayfa yönetimi | Sayfalar: ana sayfa (hero, tanıtım, yaklaşım, süreç, CTA), hakkımızda, iletişim, hukuki metinler | Tamamlandı |
| 7.4 Blog yönetimi | Blog Yazıları + Kategoriler; yazar, zamanlanmış yayın, SEO | Tamamlandı |
| 7.5 Medya kütüphanesi | Yükleme (toplu), önizleme, arama, alt metin, boyut, kullanım sayısı, WebP/AVIF, kullanımdaki görsel silinemez | Tamamlandı |
| 7.6 Menü yönetimi | Menüler: ekleme/silme/sıralama, alt menü, yeni sekme | Tamamlandı |
| 7.7 İletişim talepleri | **Web Formları** (talep 9): yeni/inceleniyor/yanıtlandı/arşiv, okunmamış rozeti, iç not, e-postayla yanıtla | Tamamlandı |
| 7.8 Kullanıcı ve yetki | Kullanıcılar (Super Admin/Admin/Editor), policy tabanlı yetki, işlem kayıtları | Tamamlandı |
| 4.4 / 4.6 | Proje Kategorileri, İlanlar (Portföy) | Tamamlandı |
| Talep 7 | Hizmetler: ekleme, silme, düzenleme, sürükle-bırak sıralama | Tamamlandı |
| Ek | SSS, Ekip, 301 Yönlendirmeler, Site Ayarları (iletişim, sosyal medya, modül aç/kapa, bildirim e-postası) | Tamamlandı |
