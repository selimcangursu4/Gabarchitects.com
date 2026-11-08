<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Cache;

/**
 * Kayıt değişince ilgili cache anahtarlarını temizler.
 * Modelde: protected static array $flushesCacheKeys = ['menus.tree'];
 */
trait FlushesCache
{
    protected static function bootFlushesCache(): void
    {
        $flush = fn () => array_map(fn (string $key) => Cache::forget($key), static::$flushesCacheKeys);

        static::saved($flush);
        static::deleted($flush);

        if (method_exists(static::class, 'restored')) {
            static::restored($flush);
        }
    }
}
