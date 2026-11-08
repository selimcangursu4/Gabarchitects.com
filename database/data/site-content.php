<?php

/*
|--------------------------------------------------------------------------
| GAB Gayrimenkul Proje Geliştirme — başlangıç içeriği
|--------------------------------------------------------------------------
| Kaynak: https://gabarchitects.com (2026-11-08 itibarıyla canlı site).
| Metinler mevcut siteden alınmıştır; marka adı "GAB Etiler" → "GAB Gayrimenkul Proje Geliştirme"
| olarak güncellenmiştir (TD-014).
|
| Bu dosyayı yalnızca SiteContentSeeder okur ve veritabanına aktarır. Site veritabanından çalışır;
| içerik değişiklikleri CMS'ten (PHASE 04) yapılır, bu dosya yalnızca ilk kurulum içindir.
|
| null olan alanlar mevcut sitede bulunmayan bilgilerdir (bkz. docs/PROJECT_ANALYSIS.md §6).
| 'category_confirmed' => false: kategori, proje adından/görselden tahmin edilmiştir; müşteri onayı gerekir.
*/

return [

    'settings' => [
        'brand' => 'GAB Gayrimenkul Proje Geliştirme',
        'tagline' => 'Arsadan anahtar teslime',
        'address' => "Etiler Mahallesi, Mehmet Bıyıklı Sokak, Güliz Apartmanı No:4 D:10\n(All Sport Cafe karşısı) Beşiktaş / İstanbul",
        'address_short' => 'Etiler, Beşiktaş / İstanbul',
        'phone' => null,
        'email' => null,
        'map_url' => 'https://www.google.com/maps/search/?api=1&query='.rawurlencode('Mehmet Bıyıklı Sokak 4, Etiler, Beşiktaş, İstanbul'),
        'social' => [
            ['label' => 'Instagram', 'url' => 'https://www.instagram.com/gabproje/'],
            ['label' => 'Facebook', 'url' => 'https://www.facebook.com/Gabproje'],
        ],
    ],

    'home' => [
        'hero' => [
            'eyebrow' => null,   // kullanıcı talebiyle kaldırıldı (2026-11-08)
            'title' => 'Arsadan anahtar teslime.',
            'text' => 'Mimarlık | Gayrimenkul Proje Geliştirme | İnşaat',   // kullanıcı talebi (2026-11-08)
            // Kullanıcı talebi (2026-11-08): hero görseli Boğaziçi sahili (5120×3413, çözünürlük düşürülmez)
            'image' => 'bogazici-sahil',
            'image_caption' => 'Boğaziçi',
        ],
        'statement' => 'Her projeyi yalnızca bir yapı olarak değil; bulunduğu bölgeye, yatırımcısına ve geleceğe değer katan bir yaşam ve yatırım alanı olarak değerlendiriyoruz.',
        'intro' => [
            'title' => 'Güven, vizyon ve değer odaklı bir yaklaşım',
            'text' => 'GAB Gayrimenkul Proje Geliştirme; İstanbul merkezli olarak lüks konut ve ticari alanlarda mimari tasarım, kat karşılığı proje geliştirme ve anahtar teslim inşaat hizmetleri sunan bir kurumdur. Arsa ve yatırım sahiplerine imar durumundan kâr analizine kadar tüm finansal süreçlerde danışmanlık sağlar.',
            'values' => ['Şeffaflık', 'Güvenilirlik', 'Kalite', 'Sürdürülebilirlik'],
        ],
        'services_intro' => 'Arsa geliştirme, kentsel dönüşüm, lüks konut, villa, ticari yapılar ve karma kullanım projelerinde; fizibilite analizlerinden mimari tasarıma, yatırım planlamasından uygulama yönetimine kadar kapsamlı çözümler sunuyoruz.',
        'approach' => [
            'title' => 'Geleceğin yaşam alanları',
            'text' => 'Geleceğin yaşam alanlarını tasarlarken yalnızca bugünün ihtiyaçlarını değil, yarının beklentilerini de dikkate alıyor; her projede yüksek kalite standartlarını, estetik anlayışı ve yatırım değerini ön planda tutuyoruz.',
            'text_secondary' => 'Stratejik planlama, teknik uzmanlık ve estetik yaklaşımı bir araya getirerek fikir aşamasından anahtar teslim sürecine kadar tüm aşamaları profesyonel bir anlayışla yönetiyoruz.',
            'project' => 'bebek-ofis',
        ],
        'process' => [
            'title' => 'Proje geliştirme yaklaşımı',
            'text' => 'Her proje, yatırım hedefleri doğrultusunda özel olarak planlanır. Fizibilite analizinden proje geliştirmeye, yatırım danışmanlığından uygulama yönetimine kadar tüm süreçleri bütüncül bir anlayışla yönetiyoruz.',
            'steps' => [
                'Arsa ve bölge analizi',
                'Fizibilite çalışmaları',
                'Konsept geliştirme',
                'Mimari projelendirme',
                'Bütçe planlaması',
                'Uygulama yönetimi',
            ],
        ],
        'cta' => [
            'title' => 'Bir proje konuşalım.',
            'text' => 'İhtiyaçlarınızı değerlendirerek size özel çözüm ve teklif hazırlayalım.',
        ],
        // Zorlu Center görselinin altında kaynak dosyadan gelen beyaz/siyah bant var; düzgün görsel gelene kadar öne çıkarılmadı.
        'featured_projects' => ['sapanca-villa', 'bodrum-yalikavak-malikane', 'cesme-otel', 'arkas-railport-terminali'],
    ],

    // Mevcut /about/ sayfasından. Kuruluş hikâyesi, misyon ve ekip mevcut sitede yok (null).
    'about' => [
        'title' => 'GAB Gayrimenkul Proje Geliştirme kimdir?',
        'subtitle' => 'Güven, vizyon ve değer odaklı bir yaklaşım',
        'paragraphs' => [
            'GAB Gayrimenkul Proje Geliştirme; gayrimenkul proje geliştirme, yatırım danışmanlığı, mimarlık ve inşaat alanlarında faaliyet gösteren, yenilikçi ve sonuç odaklı bir proje geliştirme şirketidir.',
            'Her projeyi yalnızca bir yapı olarak değil, bulunduğu bölgeye, yatırımcısına ve geleceğe değer katan bir yaşam ve yatırım alanı olarak değerlendiriyoruz. Stratejik planlama, teknik uzmanlık ve estetik yaklaşımı bir araya getirerek fikir aşamasından anahtar teslim sürecine kadar tüm aşamaları profesyonel bir anlayışla yönetiyoruz.',
            'Arsa geliştirme, kentsel dönüşüm, lüks konut, villa, ticari yapılar ve karma kullanım projelerinde; fizibilite analizlerinden mimari tasarıma, yatırım planlamasından uygulama yönetimine kadar kapsamlı çözümler sunuyoruz.',
            'Şeffaflık, güvenilirlik, kalite ve sürdürülebilirlik ilkeleri doğrultusunda hareket eden GAB Gayrimenkul Proje Geliştirme, güçlü iş ortakları ve uzman kadrosuyla yatırımcıların ve arsa sahiplerinin güvenilir çözüm ortağı olmayı hedeflemektedir.',
            'Geleceğin yaşam alanlarını tasarlarken, yalnızca bugünün ihtiyaçlarını değil, yarının beklentilerini de dikkate alıyor; her projede yüksek kalite standartlarını, estetik anlayışı ve yatırım değerini ön planda tutuyoruz.',
        ],
        'vision' => 'Geliştirdiğimiz her projeyle bulunduğumuz bölgelere değer katmak, güven inşa etmek ve sürdürülebilir başarıya imza atmak.',
        'mission' => null,
        'story' => null,
        'values' => ['Şeffaflık', 'Güvenilirlik', 'Kalite', 'Sürdürülebilirlik'],
    ],

    'services' => [
        ['slug' => 'proje-gelistirme', 'title' => 'Proje Geliştirme', 'excerpt' => 'Projelerinizi fikir aşamasından uygulamaya kadar planlıyor, teknik ve ticari açıdan en verimli çözümleri geliştiriyoruz.', 'image' => 'etiler-kentsel-donusum'],
        ['slug' => 'mimarlik-ve-tasarim', 'title' => 'Mimarlık ve Tasarım', 'excerpt' => 'İhtiyaçlara ve çevresel koşullara uygun, estetik ve fonksiyonel projeler geliştiriyoruz.', 'image' => 'bodrum-gundogan'],
        ['slug' => 'ic-mimarlik', 'title' => 'İç Mimarlık', 'excerpt' => 'Yaşam ve çalışma alanlarını modern, ergonomik ve özgün tasarımlarla şekillendiriyoruz.', 'image' => 'bebek-ofis'],
        ['slug' => 'gayrimenkul-yatirim-danismanligi', 'title' => 'Gayrimenkul Yatırım Danışmanlığı', 'excerpt' => 'Yatırım hedeflerinize uygun analizler yaparak doğru lokasyon, doğru proje ve sürdürülebilir yatırım stratejileri sunuyoruz.', 'image' => 'cesme-otel'],
        ['slug' => 'insaat-ve-taahhut', 'title' => 'İnşaat ve Taahhüt', 'excerpt' => 'Konut, ticari ve endüstriyel yapılarda kalite, güven ve zamanında teslim anlayışıyla anahtar teslim projeler gerçekleştiriyoruz.', 'image' => 'sapanca-villa'],
        ['slug' => 'uygulama-projeleri', 'title' => 'Uygulama Projeleri', 'excerpt' => 'Tasarımdan uygulamaya kadar tüm teknik projeleri eksiksiz hazırlıyor, uygulanabilir çözümler sunuyoruz.', 'image' => 'zorlu-center-ofis'],
        ['slug' => 'kentsel-donusum-ve-arsa-gelistirme', 'title' => 'Kentsel Dönüşüm ve Arsa Geliştirme', 'excerpt' => 'Arsa analizinden hak sahipleri yönetimine kadar tüm süreçleri profesyonel olarak yönetiyor, gayrimenkullerinizin değerini en üst seviyeye çıkarıyoruz.', 'image' => 'etiler-kentsel-donusum'],
        ['slug' => 'konut-ve-villa-projeleri', 'title' => 'Konut ve Villa Projeleri', 'excerpt' => 'Müstakil villalardan toplu konut projelerine kadar estetik, kaliteli ve uzun ömürlü yaşam alanları hayata geçiriyoruz.', 'image' => 'levent-villa'],
        ['slug' => 'ticari-yapilar', 'title' => 'Ticari Yapılar', 'excerpt' => 'Ofis binaları, iş merkezleri, mağazalar ve karma kullanım projelerinde modern ve sürdürülebilir yapılar inşa ediyoruz.', 'image' => 'metro-turizm'],
        ['slug' => 'endustriyel-yapilar', 'title' => 'Fabrika ve Endüstriyel Yapılar', 'excerpt' => 'Üretim tesisleri, lojistik merkezleri, depolar ve sanayi yapılarında fonksiyonel, dayanıklı ve verimli çözümler geliştiriyoruz.', 'image' => 'arkas-railport-terminali'],
        ['slug' => 'havalimani-ve-kamu-yapilari', 'title' => 'Havalimanı ve Kamu Yapıları', 'excerpt' => 'Terminal binaları, kamu tesisleri ve altyapı projelerinde uluslararası standartlarda mühendislik ve uygulama hizmetleri sunuyoruz.', 'image' => null],
        // Mevcut sitede yalnızca başlık var; açıklama müşteriden bekleniyor.
        ['slug' => 'renovasyon-ve-restorasyon', 'title' => 'Renovasyon ve Restorasyon', 'excerpt' => null, 'image' => null],
    ],

    'project_categories' => [
        'konut' => 'Konut',
        'ticari' => 'Ticari',
        'endustriyel' => 'Endüstriyel',
        'turizm' => 'Turizm ve Konaklama',
        'ic-mimari' => 'İç Mimari',
        'kentsel-donusum' => 'Kentsel Dönüşüm',
    ],

    // Sıra mevcut sitedeki sıradır. year/area/description mevcut sitede yok.
    'projects' => [
        ['slug' => 'sapanca-villa', 'title' => 'Sapanca Villa', 'location' => 'Sapanca', 'subtitle' => 'Villa projesi', 'category' => 'konut', 'category_confirmed' => true, 'image' => 'sapanca-villa'],
        ['slug' => 'etiler-kentsel-donusum', 'title' => 'Etiler Kentsel Dönüşüm', 'location' => 'İstanbul, Etiler', 'subtitle' => 'Kentsel dönüşüm projesi', 'category' => 'kentsel-donusum', 'category_confirmed' => true, 'image' => 'etiler-kentsel-donusum'],
        ['slug' => 'arkas-railport-terminali', 'title' => 'Arkas Railport Terminali', 'location' => null, 'subtitle' => 'Railport terminali projesi — Arkas Holding', 'category' => 'endustriyel', 'category_confirmed' => false, 'image' => 'arkas-railport-terminali'],
        ['slug' => 'bodrum-yalikavak-malikane', 'title' => 'Yalıkavak Malikâne', 'location' => 'Bodrum, Yalıkavak', 'subtitle' => 'Malikâne projesi', 'category' => 'konut', 'category_confirmed' => true, 'image' => 'bodrum-yalikavak-malikane'],
        ['slug' => 'metro-turizm', 'title' => 'Metro Turizm', 'location' => null, 'subtitle' => 'Proje', 'category' => 'ticari', 'category_confirmed' => false, 'image' => 'metro-turizm'],
        ['slug' => 'sinpas-kis-bahcesi', 'title' => 'Sinpaş Kış Bahçesi', 'location' => 'İstanbul', 'subtitle' => 'Kış bahçesi projesi', 'category' => 'ic-mimari', 'category_confirmed' => false, 'image' => 'sinpas-kis-bahcesi'],
        ['slug' => 'bodrum-gundogan', 'title' => 'Gündoğan', 'location' => 'Bodrum, Gündoğan', 'subtitle' => 'Proje', 'category' => 'konut', 'category_confirmed' => false, 'image' => 'bodrum-gundogan'],
        ['slug' => 'bebek-ofis', 'title' => 'Bebek Ofis', 'location' => 'İstanbul, Bebek', 'subtitle' => 'Ofis projesi', 'category' => 'ic-mimari', 'category_confirmed' => false, 'image' => 'bebek-ofis'],
        ['slug' => 'levent-villa', 'title' => 'Levent Villa', 'location' => 'İstanbul, Levent', 'subtitle' => 'Ticari proje', 'category' => 'ticari', 'category_confirmed' => true, 'image' => 'levent-villa'],
        ['slug' => 'sapanca-villa-2', 'title' => 'Sapanca Villa II', 'location' => 'Sapanca', 'subtitle' => 'Villa projesi', 'category' => 'konut', 'category_confirmed' => true, 'image' => 'sapanca-villa-2'],
        ['slug' => 'zorlu-center-ofis', 'title' => 'Zorlu Center Ofis', 'location' => 'İstanbul, Zorlu Center', 'subtitle' => 'Ofis projesi', 'category' => 'ic-mimari', 'category_confirmed' => false, 'image' => 'zorlu-center-ofis'],
        ['slug' => 'zekeriyakoy-kat-karsiligi', 'title' => 'Zekeriyaköy', 'location' => 'İstanbul, Zekeriyaköy', 'subtitle' => 'Kat karşılığı projesi', 'category' => 'konut', 'category_confirmed' => false, 'image' => 'zekeriyakoy-kat-karsiligi'],
        ['slug' => 'acibadem-taahhut', 'title' => 'Acıbadem', 'location' => 'İstanbul, Acıbadem', 'subtitle' => 'Taahhüt projesi', 'category' => null, 'category_confirmed' => false, 'image' => 'acibadem-taahhut'],
        ['slug' => 'bodrum-yalikavak-malikane-2', 'title' => 'Yalıkavak Malikâne II', 'location' => 'Bodrum, Yalıkavak', 'subtitle' => 'Malikâne projesi', 'category' => 'konut', 'category_confirmed' => true, 'image' => 'bodrum-yalikavak-malikane-2'],
        ['slug' => 'cesme-otel', 'title' => 'Çeşme Otel', 'location' => 'İzmir, Çeşme', 'subtitle' => 'Otel projesi', 'category' => 'turizm', 'category_confirmed' => true, 'image' => 'cesme-otel'],
        ['slug' => 'levent-ticari-kurgu', 'title' => 'Levent Ticari Kurgu', 'location' => 'İstanbul, Levent', 'subtitle' => 'Ticari kurgu projesi', 'category' => 'ticari', 'category_confirmed' => true, 'image' => 'levent-ticari-kurgu'],
    ],

    // Mevcut sitede "MÜŞTERİ/PROJE – HİZMET" formatında. Logo kullanım izni bekleniyor.
    'references' => [
        ['name' => 'Arkas Lojistik', 'service' => 'İnce işler uygulama'],
        ['name' => 'Expo Fuar', 'service' => 'Danışmanlık hizmetleri'],
        ['name' => 'Nergis İnşaat', 'service' => 'Taşeronluk hizmetleri'],
        ['name' => 'Senegal', 'service' => 'Proje danışmanlığı'],
        ['name' => 'Nida Kule', 'service' => 'Danışmanlık ve uygulama'],
        ['name' => 'Avrupa Konutları', 'service' => 'Daire uygulamaları ve tasarımları'],
        ['name' => 'Kaymakamlık Projesi', 'service' => 'Mimari proje yönetimi'],
        ['name' => 'Acarkent Villa', 'service' => 'Mimari uygulama ve tasarım'],
        ['name' => 'Levent Akatlar Villa', 'service' => 'Tasarım ve uygulama'],
        ['name' => 'Sapanca Villa', 'service' => 'Tasarım ve uygulama'],
        ['name' => 'Bodrum Yalıkavak', 'service' => 'İç mimari uygulama'],
        ['name' => 'Ömerli Kasaba Evleri', 'service' => 'Mimari ve iç mimari tasarım'],
        ['name' => 'İzmir Alaçatı', 'service' => 'Tasarım ve uygulama'],
        ['name' => 'Nişantaşı Anaokulu', 'service' => 'Yangın sistemleri ve uygulama'],
        ['name' => 'Kozyatağı', 'service' => 'Kaba inşaat ve ince işler'],
        ['name' => 'Tuzla Ticari Bina', 'service' => 'Danışmanlık'],
    ],

    'faqs' => [
        ['q' => 'GAB Gayrimenkul Proje Geliştirme hangi alanlarda hizmet vermektedir?', 'a' => 'GAB Gayrimenkul Proje Geliştirme; gayrimenkul proje geliştirme, yatırım danışmanlığı, mimarlık, iç mimarlık, inşaat ve taahhüt hizmetleri ile kentsel dönüşüm projelerinde uçtan uca profesyonel çözümler sunmaktadır.'],
        ['q' => 'Proje geliştirme süreciniz nasıl ilerliyor?', 'a' => 'Süreç; arsa ve bölge analizi, fizibilite çalışmaları, konsept geliştirme, mimari projelendirme, bütçe planlaması ve uygulama yönetimi aşamalarından oluşur. Her proje, yatırım hedefleri doğrultusunda özel olarak planlanır.'],
        ['q' => 'Anahtar teslim inşaat hizmeti sunuyor musunuz?', 'a' => 'Evet. Konut, villa, ticari yapı ve özel projelerde kaba yapıdan ince işlere kadar tüm süreçleri tek çatı altında yönetiyor, anahtar teslim çözümler sunuyoruz.'],
        ['q' => 'Kentsel dönüşüm projelerinde destek sağlıyor musunuz?', 'a' => 'Evet. Kentsel dönüşüm süreçlerinde hak sahipleriyle koordinasyon, proje geliştirme, sözleşme danışmanlığı ve uygulama yönetimi hizmetleri sunarak sürecin güvenle ilerlemesini sağlıyoruz.'],
        ['q' => 'Projem için nasıl teklif alabilirim?', 'a' => 'İletişim formunu doldurarak veya telefon ve e-posta yoluyla bizimle iletişime geçebilirsiniz. Uzman ekibimiz, ihtiyaçlarınızı değerlendirerek size özel çözüm ve teklif hazırlayacaktır.'],
        ['q' => 'Hangi bölgelerde hizmet veriyorsunuz?', 'a' => 'Merkez ofisimiz İstanbul Etiler’de bulunmaktadır. Başta İstanbul olmak üzere Türkiye genelinde gayrimenkul proje geliştirme, mimarlık, inşaat ve yatırım danışmanlığı hizmetleri sunuyoruz.'],
        ['q' => 'Yatırım danışmanlığı hizmetiniz kimlere yöneliktir?', 'a' => 'Bireysel ve kurumsal yatırımcılar, arsa sahipleri, müteahhitler ve proje geliştiricilere yönelik yatırım analizi, fizibilite çalışmaları ve proje geliştirme danışmanlığı hizmeti veriyoruz.'],
        ['q' => 'Referans projelerinizi inceleyebilir miyim?', 'a' => 'Evet. Tamamlanan ve devam eden seçili projelerimizi Referanslar sayfamızdan inceleyebilir, farklı ölçeklerde gerçekleştirdiğimiz çalışmalar hakkında detaylı bilgi edinebilirsiniz.'],
    ],

    // Görsel anahtarı → kaynak dosya (mevcut sitedeki /wp-content/uploads yolu).
    // Hepsi 3D görselleştirme (render). Profesyonel fotoğraf çekimi önerilir.
    'image_sources' => [
        'bogazici-sahil' => 'Kullanıcı tarafından sağlandı (2026-11-08) — ana sayfa hero görseli, 5120×3413',
        'sapanca-villa' => '2026/07/dc531417-042b-4ed4-b5ac-9cb2eb538f4f.jpg',
        'etiler-kentsel-donusum' => '2026/07/PHOTO-2022-05-28-01-48-33-2.jpg',
        'arkas-railport-terminali' => '2026/07/slider1__ResizedImageWzE5MjAsMTA4MF0.jpg',
        'bodrum-yalikavak-malikane' => '2026/07/PHOTO-2025-02-08-23-21-34-5.jpg',
        'metro-turizm' => '2026/07/METROPARK-4-2.jpg',
        'sinpas-kis-bahcesi' => '2026/07/PHOTO-2026-04-25-14-33-13.jpg',
        'bodrum-gundogan' => '2026/07/4.jpg',
        'bebek-ofis' => '2026/07/PHOTO-2025-02-08-23-31-00-7.jpg',
        'levent-villa' => '2026/07/5.jpg',
        'sapanca-villa-2' => '2026/07/PHOTO-2024-07-01-00-44-29-2.jpg',
        'zorlu-center-ofis' => '2026/07/kat1_toplanti1.jpg',
        'zekeriyakoy-kat-karsiligi' => '2026/07/780E0DBF-97D2-4AD4-AD4E-EE4951CADB57.png',
        'acibadem-taahhut' => '2026/08/IMG_2684.jpg',
        'bodrum-yalikavak-malikane-2' => '2026/07/PHOTO-2025-02-08-23-21-34-2.jpg',
        'cesme-otel' => '2026/07/PHOTO-2025-10-07-00-00-43-8.jpg',
        'levent-ticari-kurgu' => '2026/07/bodrum_audition2.jpg',
    ],
];
