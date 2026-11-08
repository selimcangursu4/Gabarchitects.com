<?php

return [
    // Türevlerin yazıldığı disk: public/media (symlink gerektirmez, paylaşımlı hosting uyumlu — TD-019)
    'disk' => env('MEDIA_DISK', 'media'),

    // Orijinallerin saklandığı disk: storage/app/private (web'den erişilemez)
    'original_disk' => env('MEDIA_ORIGINAL_DISK', 'local'),

    'widths' => [480, 960, 1600, 2400],

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
