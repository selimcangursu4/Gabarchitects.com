<?php

namespace App\Support;

use Illuminate\Support\Str;

class Slug
{
    /** Türkçe kurallarla slug: "İstanbul Çeşme Işıklı" → "istanbul-cesme-isikli" */
    public static function make(?string $value): string
    {
        return Str::slug(strtr((string) $value, ['İ' => 'i', 'I' => 'i', 'ı' => 'i']), '-', 'tr');
    }
}
