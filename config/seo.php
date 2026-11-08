<?php

return [
    /*
     * Arama motorları siteyi dizine alabilir mi?
     * Varsayılan: yalnızca production. Lokal / test / staging ortamları
     * "noindex" ve "Disallow: /" ile kapalıdır (yarım içerik Google'a gitmez).
     */
    // Boş bırakılan "SEO_INDEXABLE=" de varsayılana düşer (aksi halde production'da yanlışlıkla kapanırdı)
    'indexable' => (static function (): bool {
        $value = env('SEO_INDEXABLE');

        return $value === null || $value === ''
            ? env('APP_ENV') === 'production'
            : filter_var($value, FILTER_VALIDATE_BOOLEAN);
    })(),

    // Paylaşım görseli için hedef genişlik (Open Graph önerisi 1200 px)
    'share_image_width' => 1200,

    // İsteğe bağlı X (Twitter) hesabı, ör. "@gabarchitects" — [EKSİK]: müşteriden teyit edilecek
    'twitter_site' => env('SEO_TWITTER_SITE'),
];
