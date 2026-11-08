<?php

return [
    // Marka adı (TD-061, kullanıcı kararı 2026-11-08). Sitede görünen ad panelden (Site Ayarları → Marka adı)
    // değiştirilebilir; bu değer ayar yoksa ve veritabanına erişilemeyen yerlerde (hata sayfaları) kullanılır.
    'brand' => 'GAB Gayrimenkul Proje Geliştirme',

    // SiteContentSeeder eski site görsellerini içe aktarsın mı? (Testlerde kapalı: hız için)
    'seed_media' => env('SEED_MEDIA', true),

    // ÖRNEK ilanlar local dışında da üretilsin mi? Yalnızca test/önizleme sunucusu için (TD-060).
    // Gerçek production'da AÇILMAZ; ilanlar "ÖRNEK" etiketli, noindex ve sitemap dışıdır.
    'seed_samples' => (bool) env('GAB_SEED_SAMPLES', false),
    // İlk kurulumdaki Super Admin hesabı (AdminUserSeeder). Şifre boşsa rastgele üretilir.
    'admin' => [
        'name' => env('ADMIN_NAME', 'Yönetici'),
        'email' => env('ADMIN_EMAIL'),
        'password' => env('ADMIN_PASSWORD'),
    ],
];
