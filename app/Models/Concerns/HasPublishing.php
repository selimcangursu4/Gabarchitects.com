<?php

namespace App\Models\Concerns;

use App\Enums\PublicationState;
use App\Enums\PublishStatus;
use Illuminate\Database\Eloquent\Builder;

/**
 * status (draft|published) + published_at.
 * Yayına alınırken tarih boşsa "şimdi" atanır; gelecekteki tarih = zamanlanmış yayın.
 */
trait HasPublishing
{
    protected static function bootHasPublishing(): void
    {
        static::saving(function ($model) {
            if ($model->status === PublishStatus::Published && $model->published_at === null) {
                $model->published_at = now();
            }
        });
    }

    public function initializeHasPublishing(): void
    {
        $this->mergeCasts([
            'status' => PublishStatus::class,
            'published_at' => 'datetime',
        ]);
    }

    /** Ziyaretçiye görünen kayıtlar. */
    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where($this->qualifyColumn('status'), PublishStatus::Published->value)
            ->whereNotNull($this->qualifyColumn('published_at'))
            ->where($this->qualifyColumn('published_at'), '<=', now());
    }

    public function scopeScheduled(Builder $query): Builder
    {
        return $query
            ->where($this->qualifyColumn('status'), PublishStatus::Published->value)
            ->where($this->qualifyColumn('published_at'), '>', now());
    }

    public function publicationState(): PublicationState
    {
        if ($this->status !== PublishStatus::Published) {
            return PublicationState::Draft;
        }

        return $this->published_at?->isFuture() ? PublicationState::Scheduled : PublicationState::Published;
    }

    public function isPubliclyVisible(): bool
    {
        return $this->publicationState() === PublicationState::Published;
    }
}
