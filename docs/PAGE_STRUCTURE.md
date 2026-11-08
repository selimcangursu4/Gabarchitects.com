# PAGE STRUCTURE — GAB Architects

> Güncelleme 2026-11-08 (PHASE 03): Site **yalnızca Türkçe**. Gayrimenkul portföyü artık sitenin merkezinde (kullanıcı talepleri 1–9).

---

## 1. Site Haritası ve URL'ler

| Sayfa | URL | Route adı | Not |
|---|---|---|---|
| Anasayfa | `/` | home | Hero + portföy filtresi, Portföyümüz (öne çıkan son 9), projeler, hizmetler, referanslar, form |
| Hakkımızda | `/hakkimizda` | about | Kurumsal ▾ |
| Referanslar | `/referanslar` | references | Kurumsal ▾ — aktif referansların tamamı |
| Hizmetler | `/hizmetler` | services.index | |
| Hizmet detay | `/hizmetler/{slug}` | services.show | Taslak → 404 |
| Projeler | `/projeler` | projects.index | Devam eden projeler ayrı bölüm + tamamlanan/tümü |
| Proje kategorisi | `/projeler/kategori/{slug}` | projects.category | |
| Proje detay | `/projeler/{slug}` | projects.show | Künye, galeri, önceki/sonraki, ilgili |
| Portföy (tümü) | `/portfoy` | properties.index | Filtreli liste |
| Satılık | `/satilik` | properties.sale | Portföyümüz ▾ |
| Kiralık | `/kiralik` | properties.rent | Portföyümüz ▾ |
| İlan detay | `/portfoy/{slug}` | properties.show | Galeri, ilan bilgileri, **WhatsApp + Ara butonları** (form yok; mobilde sabit alt çubuk), benzer ilanlar |
| Blog | `/blog` | blog.index | |
| Blog kategorisi | `/blog/kategori/{slug}` | blog.category | |
| Blog detay | `/blog/{slug}` | blog.show | |
| İletişim | `/iletisim` (GET/POST) | contact / contact.store | POST: rate limit 5/10 dk |
| KVKK / Gizlilik / Çerez | `/kvkk`, `/gizlilik-politikasi`, `/cerez-politikasi` | legal.* | Metin yoksa "hazırlanıyor" notu |
| Yönetim | `/admin` | — | PHASE 04 (Filament) |

Portföy route'ları `module:real_estate` middleware'i ile korunur. Modül panelden kapatılırsa 404 döner ve hero filtresi ile Portföyümüz bölümü gizlenir.

### Portföy filtresi (URL parametreleri)
`islem` (satilik|kiralik) · `tip` (daire, rezidans, villa, mustakil-ev, yali, ofis, dukkan, bina, depo, otel, arsa, tarla) · `il` · `ilce` · `fiyat_min` / `fiyat_max` · `m2_min` / `m2_max` · `oda[]` (1–5; 5 = 5+1 ve üzeri) · `siralama` (yeni, fiyat-artan, fiyat-azalan, m2-azalan)

Örnek: `/satilik?tip=villa&il=Muğla&ilce=Bodrum&oda[]=4&oda[]=5`. Geçersiz değerler hata vermeden yok sayılır.

### Eski URL yönlendirmeleri (301)
`/about/` → `/hakkimizda` · `/contact-2/` → `/iletisim`

---

## 2. Header menüsü (talep 4–5)

| Öğe | Bağlantı | Alt menü |
|---|---|---|
| Anasayfa | `/` | — |
| Kurumsal ▾ | — | Hakkımızda, Referanslar |
| Portföyümüz ▾ | — | Satılık, Kiralık |
| Projeler | `/projeler` | — |
| Hizmetler | `/hizmetler` | — |
| Blog | `/blog` | — |
| İletişim | `/iletisim` | — |

Bağlantısız üst öğeler açılır menü düğmesidir: hover, tıklama ve klavye (Enter/Esc) ile çalışır, `aria-expanded` günceller. Menü yönetimi PHASE 04'te panelden yapılacak.

---

## 3. Ana sayfa bölümleri

| # | Bölüm | Veri kuralı |
|---|---|---|
| 1 | Hero: solda slogan, sağda portföy filtresi | pages.home.content.hero |
| 2 | **Portföyümüz** — e-ticaret kart listesi | `is_featured = true` olan **son eklenen 9 ilan** |
| 3 | Marka cümlesi | pages.home.content.statement |
| 4 | Hakkımızda özeti | pages.home.content.intro |
| 5 | Öne çıkan projeler (asimetrik) | `is_featured = true` olan **son eklenen 4 proje** |
| 6 | Hizmetler | yayındaki hizmetler (sıralı) |
| 7 | Mimari yaklaşım | pages.home.content.approach |
| 8 | Referanslar | `is_featured = true` olan **son eklenen 16 referans** |
| 9 | Proje geliştirme süreci | pages.home.content.process |
| 10 | **Bir proje konuşalım** + form | kayıt → contact_messages (source = home_cta) → panel "Web Formları" |
