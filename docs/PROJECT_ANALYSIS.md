# PROJECT ANALYSIS — GAB Architects

> Faz: PHASE 00 – Proje Analizi
> Tarih: 2026-11-08
> Kaynak: https://gabarchitects.com/ (canlı site incelemesi), https://www.spaceistanbul.com/tr (UX referansı)
> Not: Bu belgedeki bütün firma bilgileri mevcut siteden alınmıştır. Sitede bulunmayan hiçbir bilgi eklenmemiştir. Eksik bilgiler **[EKSİK]** olarak işaretlenmiştir.

---

## 1. Mevcut Sitenin Durumu (Özet)

| Konu | Bulgu |
|---|---|
| Altyapı | WordPress (`/wp-content/uploads/`). Bir emlak teması/eklentisi kullanılıyor (ilan karşılaştırma, üye girişi widget'ları). Medya yüklemeleri 2026/07–08 tarihli; site yakın zamanda kurulmuş. |
| Gerçek sayfa sayısı | 3 — Ana sayfa, `/about/`, `/contact-2/` |
| Dil | Yalnızca Türkçe. Bazı arayüz metinleri İngilizce kalmış ("Contact", "Compare listings", "Forgot Password"). |
| Marka adı | Sitede **"GAB Etiler"** ve **"GAB Gayrimenkul Proje Geliştirme"**; domain **gabarchitects.com**; sosyal medya **"gabproje"**. Marka adı tutarsız. |
| Logo | `cropped-Mavi-Siyah-Minimalist-...-Logo-7.png`. Dosya adına bakılırsa bir şablon aracından üretilmiş PNG. Vektörel logo görünmüyor. |
| Projeler | 16 proje kartı var, hepsinin bağlantısı `#`. Detay sayfası, yıl, alan, açıklama veya galeri yok. |
| Referanslar | 16 metin referansı var ("MÜŞTERİ – HİZMET" formatında). Logo yok. |
| Gayrimenkul portföyü | Tanıtım metni var, gerçek ilan yok. |
| Blog | Yok. |
| İletişim | Adres var. **Telefon, e-posta ve harita yok.** |
| Sosyal medya | Facebook: facebook.com/Gabproje · Instagram: instagram.com/gabproje |

### Kritik kalite sorunları

1. **Tema demo verisi yayında.** Gayrimenkul arama formunda Chicago/Miami/Beirut semt adları ("Albany Park", "Brooklyn", "Wynwood"...) ve "teste", "Uupo" gibi test değerleri var. "Oda Sayısı" alanında semt adları listeleniyor.
2. "User registration is disabled for demo purpose." metni bütün sayfalarda görünüyor.
3. Menüde iki öğe ("GAB Etiler ?" ve "Gayrimenkul Proje Geliştirme") aynı sayfaya gidiyor.
4. Butonların, kartların ve footer bağlantılarının çoğu `#`. Arama motorlarının tarayabileceği proje veya hizmet URL'si yok.
5. Görsellerin alt metinleri boş ya da hepsi aynı ("GAB Etiler Kimdir").
6. SSS'de "iletişim formu, telefon veya e-posta ile ulaşabilirsiniz" yazıyor, ama sitede telefon ve e-posta yok.

---

## 2. Mevcut İçerik Envanteri (korunacak içerik)

### 2.1 Firma tanımı (mevcut siteden, özet)
- İstanbul merkezli. Mimari tasarım, kat karşılığı proje geliştirme ve anahtar teslim inşaat hizmetleri veriyor.
- Arsa sahiplerine ve yatırımcılara imar durumundan kâr analizine kadar danışmanlık sağlıyor.
- Değerleri: şeffaflık, güvenilirlik, kalite, sürdürülebilirlik.
- **Vizyon (mevcut):** Faaliyet gösterdiği bölgelere değer katmak, güven inşa etmek ve her projeyle kalıcı başarı elde etmek.
- Merkez: İstanbul, Etiler. Hizmet alanı: "Türkiye geneli, ağırlıklı İstanbul".
- **[EKSİK]** Kuruluş yılı, kurucular, ekip, misyon metni, sertifikalar, ticari unvan.

### 2.2 Hizmetler — mevcut site ile brief karşılaştırması

| Brief'teki hizmet | Mevcut sitede karşılığı | Not |
|---|---|---|
| Mimarlık ve Tasarım | Mimarlık / Mimari Tasarım | ✔ |
| İç Mimarlık | İç Mimarlık | ✔ |
| Proje Geliştirme | Gayrimenkul Proje Geliştirme | ✔ |
| Gayrimenkul Yatırım Danışmanlığı | Gayrimenkul Yatırım Danışmanlığı | ✔ |
| Anahtar Teslim Projeler | Anahtar Teslim projeler | ✔ |
| İnşaat ve Uygulama Yönetimi | İnşaat & Taahhüt / Uygulama Projeleri | ✔ (isim birleştirilecek) |
| Endüstriyel Yapılar | Fabrika & Endüstriyel Yapılar | ✔ |
| Ticari Yapılar | Ticari Yapılar | ✔ |
| Konut ve Villa Projeleri | Konut & Villa Projeleri | ✔ |
| — | **Havalimanı Kamu Yapıları** | Mevcut sitede var, brief'te yok |
| — | **Kentsel Dönüşüm ve Arsa Geliştirme** | Mevcut sitede var, brief'te yok |
| — | **Renovasyon & Restorasyon** | Mevcut sitede var, brief'te yok |

**Öneri:** 12 hizmetin hepsi CMS'e girilsin. Hangilerinin yayınlanacağına müşteri karar versin (onay gerekiyor).

### 2.3 Projeler (mevcut sitedeki 16 kart)

| # | Başlık | Alt başlık | Önerilen kategori (taslak) |
|---|---|---|---|
| 1 | Sapanca | Villa projemiz | Residential |
| 2 | İstanbul-Etiler | Kentsel dönüşüm projemiz | Residential |
| 3 | Arkas Holding | Railport Terminali Projemiz | Industrial / Infrastructure |
| 4 | Bodrum Yalıkavak | Malikane Projemiz | Residential |
| 5 | Metro Turizm | Projemiz | **[EKSİK – belirsiz]** |
| 6 | İstanbul-Sinpaş | Kış Bahçesi Projemiz | **[EKSİK – belirsiz]** |
| 7 | Bodrum Gündoğan | Projemiz | **[EKSİK – belirsiz]** |
| 8 | İstanbul-Bebek | Ofis Projemiz | Commercial / Interior |
| 9 | İstanbul-Levent Villa | Ticari Projemiz | Commercial |
| 10 | Sapanca | Villa Projemiz (2. kayıt) | Residential |
| 11 | İstanbul-Zorlu Center | Ofis Projemiz | Commercial / Interior |
| 12 | İstanbul-Zekeriyaköy | Kat Karşılığı Projemiz | Residential |
| 13 | İstanbul-Acıbadem | Taahhüt Projemiz | **[EKSİK – belirsiz]** |
| 14 | Bodrum Yalıkavak | Malikane Projemiz (2. kayıt) | Residential |
| 15 | İzmir Çeşme | Otel Projemiz | Hospitality |
| 16 | İstanbul-Levent | Ticari Kurgu Projemiz | Commercial |

- "Sapanca" ve "Bodrum Yalıkavak" ikişer kez geçiyor. Bunların aynı proje mi yoksa farklı projeler mi olduğu netleşmeli.
- Hiçbir proje için **yıl, alan (m²), açıklama, galeri veya rol** (tasarım mı, uygulama mı, danışmanlık mı) bilgisi yok. Bu bilgilerin hepsi **[EKSİK]**.
- Kategori eşleştirmeleri tahmindir; müşteri onaylamalı.

### 2.4 Referanslar (mevcut sitedeki 16 kayıt)
Arkas Lojistik (ince işler uygulama), Expo Fuar (danışmanlık), Nergis İnşaat (taşeronluk), Senegal (proje danışmanlığı), Nida Kule (danışmanlık ve uygulama), Avrupa Konutları (daire uygulama ve tasarım), Kaymakamlık Projesi (mimari proje yönetimi), Acarkent Villa, Levent Akatlar Villa, Sapanca Villa (tasarım ve uygulama), Bodrum Yalıkavak (iç mimari uygulama), Ömerli Kasaba Evleri (mimari ve iç mimari tasarım), İzmir Alaçatı (tasarım ve uygulama), Nişantaşı Anaokulu (yangın sistemleri), Kozyatağı (kaba inşaat ve ince işler), Tuzla Ticari Bina (danışmanlık).

- Kurumsal isimlerin (Arkas, Expo, Nergis, Nida Kule, Avrupa Konutları) **logolarını kullanmak için izin** gerekebilir. Logo dosyaları da **[EKSİK]**.
- Referansların bir kısmı müşteri değil proje adı (Sapanca Villa, Kozyatağı). Bu yüzden yeni yapıda "Referans" (kurum/iş ortağı) ile "Proje" ayrı tutulacak, ama aralarında ilişki kurulabilecek.

### 2.5 SSS (8 soru-cevap)
Mevcut 8 SSS korunacak ve CMS'e taşınacak. Ayrı bir `faqs` modeli önerilir (brief'te yok, ek öneri).

### 2.6 İletişim
- Adres: Etiler Mah. Mehmet Bıyıklı Sk. Güliz Apt. No:4 D:10 (All Sport Cafe karşısı), Beşiktaş / İstanbul
- **[EKSİK]** Telefon, e-posta, WhatsApp, Google Maps bağlantısı, çalışma saatleri, LinkedIn.

---

## 3. Görsel İhtiyaç Analizi

Premium bir tasarımın başarısı **%70 oranında fotoğraf kalitesine bağlıdır**. Mevcut sitede yaklaşık 26 görsel var ve kaynak çözünürlükleri doğrulanamadı.

| İhtiyaç | Minimum | İdeal |
|---|---|---|
| Hero görseli/videosu | 1 yatay görsel, ≥ 2560 px genişlik | 3–5 görsel veya 10–20 sn sessiz döngü video (≤ 6 MB, H.264/WebM) |
| Proje kapakları | Her proje için 1 adet, ≥ 2400 px | Profesyonel mimari fotoğraf |
| Proje galerisi | Her proje için 6 görsel | 12–20 görsel (dış, iç, detay, gece çekimi, plan/çizim) |
| Hizmet kapakları | 12 görsel | Hizmete özel gerçek proje fotoğrafları |
| Ekip fotoğrafları | **[EKSİK]** | Aynı ışık ve fonda çekilmiş portreler |
| Ofis fotoğrafı | **[EKSİK]** | 2–4 görsel |
| Logo | **[EKSİK – vektör]** | SVG; açık ve koyu zemin için ayrı versiyonlar |
| Referans logoları | **[EKSİK]** | SVG/PNG, şeffaf zemin |

**Kural:** Stok fotoğrafların GAB projesi gibi sunulması yasaktır. Geliştirme sırasında kullanılacak yer tutucu görseller CMS'te açıkça "ÖRNEK" olarak etiketlenecek ve yayına çıkmadan önce değiştirilecek.

---

## 4. Referans Site İncelemesi — Space İstanbul (yalnızca ilham)

Space İstanbul bir **emlak danışmanlığı** platformudur, yani ilan/arama odaklıdır. GAB ise mimarlık ve proje geliştirme firmasıdır, yani **portföy/hikâye** odaklıdır. Bu yüzden yapıyı kopyalamak doğru olmaz; alınacak olan yaklaşımdır.

### Öğrenilecek ilkeler
1. Hero'dan hemen sonra kullanıcıyı bir eyleme yönlendirmek. GAB için bu eylem arama değil, "Projeleri keşfet".
2. Her kartta aynı sırada, tutarlı bir bilgi şeridi göstermek (konum · kategori · yıl).
3. Detay sayfasında özellikleri gruplara ayırmak (GAB için proje künyesi: konum, yıl, alan, hizmet, durum).
4. Dile göre ayrı URL ön ekleri (`/tr`, `/en`).
5. KVKK ve çerez metinlerini footer'da tek bir grupta toplamak.
6. Detay sayfasında doğrudan iletişim aksiyonu sunmak ("Bu proje hakkında görüşelim").
7. Blog'u marka uzmanlığını ve SEO'yu güçlendirmek için kullanmak.

### Kaçınılacak zayıflıklar
- Ana sayfada birbiriyle yarışan çok sayıda CTA. GAB'da tek bir ana CTA olacak.
- Bilgisi zayıf proje kartları (sadece görsel ve isim).
- EN sayfalarında Türkçe içerik görünmesi. GAB'da eksik çeviri asla gösterilmeyecek.
- Tutarsız URL yerelleştirmesi (`for_sale-apartment` gibi alt çizgili slug'lar).
- 80 bağlantılı ağır mega-menü.
- Video olmadan çalışmayan hero. GAB'da her zaman bir poster görseli olacak.

---

## 5. Riskler

| # | Risk | Etki | Olasılık | Önlem |
|---|---|---|---|---|
| R1 | Turhost paketinin Laravel gereksinimlerini (PHP ≥ 8.2, SSH, document root) karşılayıp karşılamadığı **doğrulanamadı** | Yüksek | Orta | HOSTING_REQUIREMENTS.md'deki kontrol listesi Turhost'a sorulacak ve SSH olmayan senaryo için alternatif dağıtım planı hazırlanacak |
| R2 | Yeterli sayıda ve kalitede proje fotoğrafı yok | Yüksek | Yüksek | Fotoğraf listesi müşteriye iletilecek; gerekirse profesyonel çekim yapılacak |
| R3 | Marka adı belirsiz (GAB Architects / GAB Etiler / GAB Proje Geliştirme) | Yüksek | Kesin | Phase 01'den önce müşteri karar verecek |
| R4 | Vektörel logo yok | Orta | Yüksek | SVG logo talep edilecek ya da logo yenileme ayrı iş olarak planlanacak |
| R5 | Proje detayları (yıl, alan, açıklama) yok | Yüksek | Kesin | Proje bilgi formu (şablon) müşteriye gönderilecek |
| R6 | İngilizce içerikleri kimin yazacağı belli değil | Orta | Kesin | Profesyonel çeviri önerilir; eksik çeviriler EN'de yayınlanmayacak |
| R7 | Referans logolarının kullanım izni | Orta | Orta | Müşteri onay verecek; onay gelene kadar metin olarak gösterilecek |
| R8 | KVKK, gizlilik ve çerez metinleri yok | Yüksek (hukuki) | Kesin | Metinler CMS'te düzenlenebilir alan olarak bırakılacak; müşterinin hukuk danışmanı yazacak |
| R9 | SEO geçişi: eski URL'ler | Düşük | Kesin | `/about/` ve `/contact-2/` için 301 yönlendirme (eski sitenin trafiği az) |
| R10 | E-posta teslim edilebilirliği (form bildirimleri) | Orta | Orta | Turhost SMTP hesabı ile SPF/DKIM/DMARC kayıtları |
| R11 | Domain, DNS ve hosting erişim bilgileri kimde, belli değil | Yüksek | Bilinmiyor | Phase 08'den önce erişim doğrulanacak |
| R12 | Mevcut WordPress sitesinin yedeği | Yüksek | — | Yayına geçişten önce tam yedek (dosya + veritabanı) alınacak |
| R13 | Gayrimenkul modülü için gerçek ilan yok | Düşük | Kesin | Modül panelden kapatılabilir olacak ve varsayılan olarak **kapalı** gelecek |

---

## 6. Müşteriden Beklenen Bilgi ve Kararlar

1. **Resmi marka adı:** Sitede hangisi kullanılacak: "GAB Architects", "GAB Etiler" mi, "GAB Proje Geliştirme" mi? Ticari unvan ne?
2. **Turhost paket bilgisi:** Paket adı ve cPanel erişimi (detaylar HOSTING_REQUIREMENTS.md'de).
3. **İletişim:** Telefon, e-posta, WhatsApp, Google Maps konumu, LinkedIn.
4. **Logo:** SVG/AI/PDF formatında vektör dosya.
5. **Projeler:** Her proje için yıl, alan, lokasyon, açıklama, GAB'ın rolü ve fotoğraflar. Tekrar eden iki kayıt aynı proje mi, farklı mı?
6. **Hizmetler:** 12 hizmetten hangileri yayınlanacak?
7. **Ekip:** Yönetici ve ekip bilgileri gösterilecek mi?
8. **Referans logoları:** Kullanım izni var mı?
9. **İngilizce içerik:** Çeviriyi kim yapacak?
10. **Hukuki metinler:** KVKK aydınlatma metni, gizlilik politikası ve çerez politikasını kim hazırlayacak?
11. **Gayrimenkul portföyü:** Bu bölüm ilk yayında aktif olacak mı?
12. **CMS altyapısı kararı:** TECHNICAL_ARCHITECTURE.md §4'e bakınız.
