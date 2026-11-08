<?php

namespace App\Models\Concerns;

use App\Support\Slug;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Boş bırakılan slug'ı başlıktan Türkçe kurallarla üretir (ç→c, ğ→g, ı→i, İ→i, ö→o, ş→s, ü→u)
 * ve tabloda (silinmiş kayıtlar dahil) benzersiz olmasını sağlar.
 */
trait HasSlug
{
    protected static function bootHasSlug(): void
    {
        static::saving(function ($model) {
            $source = $model->slug ?: $model->{$model->slugSource()};
            $model->slug = $model->uniqueSlug(static::turkishSlug((string) $source));
        });
    }

    protected function slugSource(): string
    {
        return property_exists($this, 'slugFrom') ? $this->slugFrom : 'title';
    }

    public static function turkishSlug(string $value): string
    {
        return Slug::make($value);
    }

    protected function uniqueSlug(string $base): string
    {
        $base = $base !== '' ? $base : Str::lower(Str::random(8));
        $slug = $base;
        $i = 2;

        while ($this->slugExists($slug)) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    private function slugExists(string $slug): bool
    {
        $query = static::query()->where('slug', $slug);

        if (in_array(SoftDeletes::class, class_uses_recursive(static::class), true)) {
            $query->withTrashed();
        }

        if ($this->exists) {
            $query->whereKeyNot($this->getKey());
        }

        return $query->exists();
    }
}
