<?php

return [
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
