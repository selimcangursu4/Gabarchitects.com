<?php

return [
    // Türevlerin yazıldığı disk: public/media (symlink gerektirmez, paylaşımlı hosting uyumlu — TD-019)
    'disk' => env('MEDIA_DISK', 'media'),

    // Orijinallerin saklandığı disk: storage/app/private (web'den erişilemez)
    'original_disk' => env('MEDIA_ORIGINAL_DISK', 'local'),

    // Kaynaktan büyük türev üretilmez (büyütme yok). Kaynak bu listedeki en büyük değerden küçükse
    // kendi genişliği de türev olarak eklenir → yüksek çözünürlüklü görsel tam çözünürlüğünü korur
    // (kullanıcı talebi 2026-11-08: hero görselinde çözünürlük düşürülmez; 5120 px'e kadar).
    // Tarayıcı srcset ile ekrana uygun olanı seçer: telefon küçük, 4K/Retina ekran büyük türevi indirir.
    'widths' => [480, 960, 1600, 2400, 3200, 3840, 5120],

    'quality' => [
        'webp' => 80,
        'avif' => 60,
    ],

    // Sunucu GD'si AVIF destekliyorsa üretilir (imageavif). Kapatmak için MEDIA_AVIF=false.
    'avif' => env('MEDIA_AVIF', true),

    'max_upload_kb' => 20480,

    // Piksel sınırı (genişlik × yükseklik). Küçük dosyalı ama devasa boyutlu görseller ("dekompresyon bombası")
    // GD belleğini tüketip sunucuyu düşürmesin. 50 MP ≈ 8660×5770; profesyonel kamera görselleri sığar.
    'max_pixels' => 50_000_000,

    'allowed_mimes' => ['image/jpeg', 'image/png', 'image/webp'],
];
