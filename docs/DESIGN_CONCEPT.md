# DESIGN CONCEPT — GAB Architects

> Faz: PHASE 00 (ön konsept). Detaylı sistem Phase 01'de DESIGN_SYSTEM.md olarak hazırlanacak.

## Konsept: "Plan & Işık" (Plan & Light)

Mimarın iki temel aracından yola çıkar: **teknik çizim** (disiplin, ölçü, çizgi) ve **ışık** (malzeme, atmosfer, fotoğraf).

- **Plan:** Sayfa, görünür ve çok ince bir 12 kolonlu grid üzerine kurulur. Bölümleri ayıran 1 px'lik çizgiler, mimari paftalardaki gibi küçük teknik etiketler (`01 — Projeler`, `Konum / Yıl / Alan`) ve antet benzeri künye blokları kullanılır.
- **Işık:** Fotoğraflar büyük, kenardan kenara ve kesintisiz kullanılır. Metin görsel üzerinde minimum tutulur. Dark mode bir "gece çekimi", light mode ise bir "aydınger/kâğıt" atmosferi verir.
- **Editoryal ritim:** Dergi düzeni mantığıyla tam genişlik görsel → asimetrik iki kolon → büyük tipografik alıntı → negatif alan.

Space İstanbul'dan farkı: o site ilan/arama odaklıdır. GAB ise **portföy ve süreç anlatısı** odaklı olacak; arama paneli, ağır mega-menü ve çoklu CTA kullanılmayacak.

## İmza unsurları (özgün)
1. **Künye şeridi:** Her proje kartında ve detayında mimari antetten esinlenen ince çerçeveli bilgi satırı.
2. **Numaralı bölümler:** `01 / 09` gibi bölüm sayaçları, ince çizgi ve küçük sans-serif etiket.
3. **Ölçü çizgisi hover'ı:** Proje kartlarının üzerine gelindiğinde altta bir ölçü çizgisi soldan sağa çizilir (CSS transform, GPU dostu).
4. **Yavaş yakınlaştırma:** Görsellerde hover'da 1.00 → 1.03 ölçek, 900 ms ease-out.
5. **Büyük serif kelimeler:** Bölüm girişlerinde tek kelimelik dev başlıklar ("Yaklaşım", "Süreç").

## Tipografi (aday — Phase 01'de Türkçe karakter testi ile kesinleşecek)
| Rol | Aday 1 | Aday 2 | Aday 3 |
|---|---|---|---|
| Başlık (serif) | Instrument Serif | Newsreader (display) | Bodoni Moda |
| Gövde (sans) | Manrope | Hanken Grotesk | Geist |

Hepsi açık lisanslıdır (OFL). Fontlar **self-host** edilecek; `ğ ş ı İ ç ö ü` glifleri kontrol edilecek.

## Renk
Brief'teki paletler birebir kullanılacak (DESIGN_SYSTEM.md'de token adlarıyla tanımlanacak). Vurgu rengi (`#B7A58A` / `#A58F70`) yalnızca ince detaylarda (çizgi, aktif durum, küçük etiket) kullanılacak; geniş alanlarda kullanılmayacak. Phase 01'de her metin/zemin kombinasyonu için WCAG AA kontrast testi yapılacak. İkincil metin renginin küçük puntoda yeterli kontrastı sağlayıp sağlamadığı özellikle kontrol edilecek.

## Hareket
Scroll reveal (opacity + 16 px translate, bir kez çalışır), sayfa geçişlerinde kısa fade, hover efektleri. `prefers-reduced-motion` açıkken hepsi devre dışı kalır. Parallax ve scroll-jacking kullanılmayacak.
