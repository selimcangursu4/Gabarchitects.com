<?php

namespace App\Models;

use App\Http\Middleware\HandleRedirects;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

#[Fillable(['from_path', 'to_url', 'status_code', 'is_active', 'hits', 'last_hit_at'])]
class Redirect extends Model
{
    use LogsActivity;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_hit_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        // from_path her zaman "/yol" biçiminde, sondaki / olmadan saklanır.
        static::saving(function (self $redirect) {
            $redirect->from_path = static::normalize($redirect->from_path);
        });

        $flush = fn () => Cache::forget(HandleRedirects::CACHE_KEY);
        static::saved($flush);
        static::deleted($flush);
    }

    public static function normalize(string $path): string
    {
        $path = '/'.trim(parse_url($path, PHP_URL_PATH) ?? '', '/');

        return mb_strtolower($path);
    }
}
