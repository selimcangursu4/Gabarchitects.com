<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * Zengin metin alanlarını kaydederken temizler (XSS savunması, TD-038).
 * Filament'in symfony/html-sanitizer yapılandırması kullanılır: script, on* olayları,
 * javascript: bağlantıları vb. kaldırılır; başlık, liste, bağlantı, görsel gibi güvenli etiketler kalır.
 * Modelde: protected static array $sanitizedHtml = ['body'];
 */
trait SanitizesHtml
{
    protected static function bootSanitizesHtml(): void
    {
        static::saving(function ($model) {
            foreach (static::$sanitizedHtml as $attribute) {
                $value = $model->getAttribute($attribute);

                if (is_string($value) && $value !== '' && $model->isDirty($attribute)) {
                    $model->setAttribute($attribute, Str::sanitizeHtml($value));
                }
            }
        });
    }
}
