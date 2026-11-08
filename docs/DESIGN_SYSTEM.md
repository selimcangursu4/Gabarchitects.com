# DESIGN SYSTEM — GAB Architects

> Faz: PHASE 01 · Konsept: **"Plan & Işık"** (bkz. [DESIGN_CONCEPT.md](DESIGN_CONCEPT.md))
> Kaynak: [resources/css/app.css](../resources/css/app.css), [resources/views/components/site/](../resources/views/components/site/)

---

## 1. Renk Token'ları

Ham token'lar `--gab-*` CSS değişkenleridir ve `<html data-theme="dark|light">` ile değişir. Bileşenler bunları **yalnızca Tailwind sınıfları üzerinden** kullanır.

| Tailwind | Rol | Dark | Light |
|---|---|---|---|
| `bg-bg` | Ana arka plan | `#111111` | `#F5F4F0` |
| `bg-surface` | İkincil arka plan (kart, alternatif bölüm, footer) | `#191919` | `#FFFFFF` |
| `text-fg` | Metin | `#F5F3EF` | `#171717` |
| `text-muted` | İkincil metin (≥ 15px) | `#A6A39D` | `#77736D` |
| `text-subtle` | Küçük etiket ve meta metni (≤ 14px) | `#A6A39D` | `#6B6761` ¹ |
| `border-line` | Çizgiler | `#353535` | `#DEDBD4` |
| `text-accent` / `bg-accent` | Vurgu | `#B7A58A` | `#A58F70` |
| `text-on-image` | Görsel üzerindeki metin (temadan bağımsız) | `#F5F3EF` | `#F5F3EF` |

### Kontrast (WCAG 2.2)
| Kombinasyon | Oran | Sonuç |
|---|---|---|
| Dark: `fg` / `bg` | ~17:1 | AAA |
| Dark: `muted` / `bg` | ~7.5:1 | AAA |
| Dark: `accent` / `bg` | ~7.9:1 | AAA |
| Light: `fg` / `bg` | ~16:1 | AAA |
| Light: `muted` / `bg` | ~4.3:1 | **Yalnızca büyük metin** (AA-large) |
| Light: `subtle` / `bg` | ~5.1:1 | AA |
| Light: `accent` / `bg` | ~2.8:1 | **Metin için kullanılmaz**; yalnızca çizgi, nokta ve dekor |

¹ Brief'teki `#77736D`, 14px ve altındaki metinde AA eşiğinin (4.5:1) altında kalıyor. Bu yüzden küçük etiketler için bir ton koyusu (`#6B6761`) eklendi; brief rengi korunuyor.

**Kurallar**
- Vurgu rengi geniş alanlarda kullanılmaz. Yalnızca ince çizgi, aktif durum, numara ve tırnak işaretinde kullanılır.
- Light modda vurgu rengi metin rengi olarak yalnızca ≥ 40px boyutta kullanılır (ör. 404, süreç numaraları).

---

## 2. Tipografi

| Rol | Font | Not |
|---|---|---|
| Başlık | **Instrument Serif** 400 (+ italic) | OFL, self-host, latin-ext (ğ ş ı İ) |
| Gövde | **Manrope Variable** 200–800 | OFL, self-host |

Fontlar `@fontsource` paketlerinden derlenir ve sunucuda barındırılır. Google Fonts'a istek gitmez (KVKK). `font-display: swap` kullanılır.

| Sınıf | Boyut (akışkan) | Satır yüksekliği | Kullanım |
|---|---|---|---|
| `text-display` | 52 → 144px | 0.92 | Hero, büyük CTA |
| `text-h1` | 44 → 100px | 0.98 | Sayfa başlıkları |
| `text-h2` | 36 → 72px | 1.02 | Bölüm başlıkları |
| `text-h3` | 26 → 38px | 1.1 | Kart ve satır başlıkları |
| `text-statement` | 28 → 56px | 1.12 | Büyük alıntı/manifesto |
| `text-lead` | 17 → 21px | 1.6 | Giriş paragrafları |
| gövde | 16px | 1.7 | Metin |
| `.eyebrow` | 12px, uppercase, 0.18em | 1.2 | Bölüm etiketleri ve meta |

**Kurallar**
- Başlıklar her zaman serif ve 400 ağırlıktadır; kalın serif kullanılmaz.
- Vurgu kalınlıkla değil, boyutla yapılır. Gövdede yalnızca 400 ve 500 ağırlık kullanılır.
- **Türkçe büyük harf:** `lang="tr"` altında `uppercase`, "i" harfini "İ" yapar. İngilizce kelimeler (ör. "Architects") `lang="en"` ile işaretlenmelidir.

---

## 3. Grid ve Boşluk

| Token | Değer |
|---|---|
| `.container-gab` | max 1600px; yan boşluk `clamp(20px, 4vw, 64px)` |
| `.grid-gab` | Mobil 4 kolon, ≥1024px 12 kolon; aralık `clamp(16px, 2vw, 32px)` |
| `.section-y` | Bölüm dikey boşluğu `clamp(80px, …, 176px)` |
| `.plan-lines` | ≥1024px: içerik alanında 4 kolonlu, çok ince pafta çizgileri (imza unsuru) |

Kırılımlar Tailwind varsayılanlarıdır: `sm` 640 · `md` 768 · `lg` 1024 · `xl` 1280 · `2xl` 1536 · `3xl` 1920.

---

## 4. Bileşenler

| Bileşen | Dosya | Açıklama |
|---|---|---|
| Layout | `components/layouts/site` | `<head>`, tema script'i, header/footer, `over-hero` modu |
| Header | `components/site/header` | Sticky; hero üzerinde şeffaf; aşağı kaydırınca gizlenir; mobilde tam ekran menü (focus trap, Esc) |
| Footer | `components/site/footer` | Logo, menü, hizmetler, iletişim, yasal bağlantılar |
| Logo | `components/site/logo` | **Geçici** tipografik logotype (vektör logo bekleniyor) |
| Tema düğmesi | `components/site/theme-toggle` | `aria-pressed`, localStorage `gab-theme` |
| Buton | `components/site/button` | `solid` · `outline` · `link` (ölçü çizgisi) |
| Bölüm etiketi | `components/site/section-label` | `01 —— ETİKET` |
| Görsel | `components/site/picture` | WebP `srcset`, `sizes`, `width/height` (CLS yok), lazy; LCP için `eager` |
| Proje kartı | `components/site/project-card` | Görsel + künye şeridi (numara, ad, konum · kategori) |
| 404 | `errors/404` | Marka tasarımında hata sayfası |

### Etkileşim imzaları
- **Ölçü çizgisi** (`.measure-line`): hover/focus'ta 700ms içinde soldan sağa çizilen 1px vurgu çizgisi.
- **Yavaş yakınlaşma** (`.img-zoom`): hover'da 1.00 → 1.03, 1100ms.
- **Hizmet önizlemesi:** satır hover'ında görsel, `clip-path` ile sağdan açılır.
- **Scroll reveal** (`[data-reveal]`): opacity + 16px kayma, bir kez çalışır. `--reveal-delay` ile sıralanır.
- **Hero:** görsel açılışta 1.06 → 1.00 ölçeğe yerleşir (2.4 sn).

Easing: `--ease-architect: cubic-bezier(0.22, 1, 0.36, 1)`.

---

## 5. Tema Sistemi

1. `<head>` içindeki inline script, CSS yüklenmeden önce `localStorage['gab-theme']` değerini okur; yoksa **dark** uygular (TD-010). Bu sayede ilk yüklemede tema yanıp sönmez (FOUC yok).
2. Düğme, Alpine store'u (`$store.theme`) üzerinden temayı değiştirir ve tercihi kaydeder. Geçiş sırasında 280ms renk animasyonu için `html.theme-transition` sınıfı geçici olarak eklenir.
3. Görsel üzerindeki metinler (`text-on-image`) temadan bağımsız olarak açık renktir.

---

## 6. Erişilebilirlik ve Hareket

- "İçeriğe geç" bağlantısı, `:focus-visible` için 1px vurgu çerçevesi.
- Tüm görseller ya anlamlı bir alt metne sahiptir ya da `alt="" role="presentation"` ile dekoratif olarak işaretlenir.
- `prefers-reduced-motion`: bütün animasyon ve geçişler kapanır, reveal içerikleri doğrudan görünür.
- JavaScript kapalıyken bütün içerik görünür (reveal yalnızca `.js` sınıfı varken uygulanır).
- Header menüsü: `aria-current`, `aria-expanded`, `aria-controls`; mobil menü `role="dialog"` + focus trap.

---

## 7. Performans Bütçesi (PHASE 01 ölçümü)

| Varlık | Boyut (gzip) | Bütçe |
|---|---|---|
| CSS | 14.3 KB | < 30 KB |
| JS (Alpine + uygulama) | 19.7 KB | < 50 KB |
| Font (sayfa başı, latin) | ~46 KB woff2 (serif + sans) | < 80 KB |

Hero görseli `<link rel="preload" imagesrcset>` + `fetchpriority="high"` ile yüklenir; diğer görseller lazy yüklenir.
