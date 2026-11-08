<?php

namespace App\Models;

use App\Enums\PublishStatus;
use App\Models\Concerns\FlushesCache;
use App\Models\Concerns\LogsActivity;
use App\Models\Concerns\SanitizesHtml;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;

/**
 * Statik sayfalar. 'key' kodda sabit referanstır; 'content' sayfaya özel alanlar (JSON).
 */
#[Fillable(['key', 'title', 'slug', 'content', 'body', 'cover_media_id', 'status', 'meta_title', 'meta_description'])]
class Page extends Model
{
    use FlushesCache, LogsActivity, SanitizesHtml, SoftDeletes;

    protected static array $sanitizedHtml = ['body'];

    /** Ana sayfa kapağı = varsayılan paylaşım görseli */
    protected static array $flushesCacheKeys = ['seo.share_image'];

    protected function casts(): array
    {
        return [
            'content' => 'array',
            'status' => PublishStatus::class,
        ];
    }

    public function cover(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'cover_media_id');
    }

    /** content içinden nokta notasyonuyla değer: $page->field('hero.title') */
    public function field(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->content ?? [], $key, $default);
    }

    public static function findByKey(string $key): ?self
    {
        return static::query()->where('key', $key)->first();
    }
}
