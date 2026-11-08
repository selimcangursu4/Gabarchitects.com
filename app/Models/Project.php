<?php

namespace App\Models;

use App\Enums\ProjectProgress;
use App\Models\Concerns\HasMediaGallery;
use App\Models\Concerns\HasPublishing;
use App\Models\Concerns\HasSlug;
use App\Models\Concerns\LogsActivity;
use App\Models\Concerns\SanitizesHtml;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'project_category_id', 'title', 'slug', 'subtitle', 'location', 'client', 'year', 'area_m2', 'progress',
    'summary', 'body', 'cover_media_id', 'status', 'published_at', 'is_featured', 'sort_order',
    'meta_title', 'meta_description',
])]
class Project extends Model
{
    use HasFactory, HasMediaGallery, HasPublishing, HasSlug, LogsActivity, SanitizesHtml, SoftDeletes;

    protected static array $sanitizedHtml = ['body'];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'year' => 'integer',
            'area_m2' => 'integer',
            'progress' => ProjectProgress::class,
        ];
    }

    /** "Son eklenen" sırası (talep: öne çıkan son 4 proje) */
    public function scopeLatestFirst(Builder $query): Builder
    {
        return $query->orderByDesc('created_at')->orderByDesc('id');
    }

    public function scopeProgress(Builder $query, ProjectProgress $progress): Builder
    {
        return $query->where('progress', $progress->value);
    }

    public function url(): string
    {
        return url('/projeler/'.$this->slug);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProjectCategory::class, 'project_category_id');
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class);
    }

    public function references(): HasMany
    {
        return $this->hasMany(Reference::class);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderByDesc('published_at');
    }

    /** Yayındaki bir sonraki proje (sıralamaya göre); son projede null. */
    public function next(): ?self
    {
        return static::published()->ordered()
            ->where(fn (Builder $q) => $q->where('sort_order', '>', $this->sort_order)
                ->orWhere(fn (Builder $q) => $q->where('sort_order', $this->sort_order)->where('id', '>', $this->id)))
            ->orderBy('id')
            ->first();
    }

    /** Yayındaki bir önceki proje; ilk projede null. */
    public function previous(): ?self
    {
        return static::published()
            ->where(fn (Builder $q) => $q->where('sort_order', '<', $this->sort_order)
                ->orWhere(fn (Builder $q) => $q->where('sort_order', $this->sort_order)->where('id', '<', $this->id)))
            ->orderByDesc('sort_order')
            ->orderByDesc('id')
            ->first();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
