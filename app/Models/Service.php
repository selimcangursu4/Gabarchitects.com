<?php

namespace App\Models;

use App\Models\Concerns\FlushesCache;
use App\Models\Concerns\HasMediaGallery;
use App\Models\Concerns\HasPublishing;
use App\Models\Concerns\HasSlug;
use App\Models\Concerns\LogsActivity;
use App\Models\Concerns\SanitizesHtml;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['title', 'slug', 'excerpt', 'body', 'cover_media_id', 'status', 'published_at', 'sort_order', 'meta_title', 'meta_description'])]
class Service extends Model
{
    use FlushesCache, HasFactory, HasMediaGallery, HasPublishing, HasSlug, LogsActivity, SanitizesHtml, SoftDeletes;

    protected static array $sanitizedHtml = ['body'];

    protected static array $flushesCacheKeys = ['footer.services'];

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
