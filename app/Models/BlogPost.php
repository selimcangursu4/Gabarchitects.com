<?php

namespace App\Models;

use App\Models\Concerns\HasPublishing;
use App\Models\Concerns\HasSlug;
use App\Models\Concerns\LogsActivity;
use App\Models\Concerns\SanitizesHtml;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'blog_category_id', 'author_id', 'title', 'slug', 'excerpt', 'body', 'cover_media_id',
    'status', 'published_at', 'is_featured', 'meta_title', 'meta_description',
])]
class BlogPost extends Model
{
    use HasFactory, HasPublishing, HasSlug, LogsActivity, SanitizesHtml, SoftDeletes;

    protected static array $sanitizedHtml = ['body'];

    protected function casts(): array
    {
        return ['is_featured' => 'boolean'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function cover(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'cover_media_id');
    }

    /** Dakika cinsinden tahmini okuma süresi (≈200 kelime/dk). */
    public function readingTime(): int
    {
        return max(1, (int) ceil(str_word_count(strip_tags((string) $this->body)) / 200));
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
