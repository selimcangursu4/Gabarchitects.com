<?php

namespace App\Models\Concerns;

use App\Models\Media;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Kapak görseli (cover_media_id) ve sıralanabilir galeri (mediables pivotu).
 */
trait HasMediaGallery
{
    public function cover(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'cover_media_id');
    }

    public function media(): MorphToMany
    {
        return $this->morphToMany(Media::class, 'mediable', 'mediables')
            ->withPivot(['collection', 'sort_order', 'caption']);
    }

    public function gallery(): MorphToMany
    {
        return $this->media()
            ->wherePivot('collection', 'gallery')
            ->orderByPivot('sort_order');
    }

    /**
     * Galeriyi verilen sırayla yeniden kurar.
     *
     * @param  array<int, int>  $mediaIds  sıralı medya ID'leri
     */
    public function syncGallery(array $mediaIds): void
    {
        $this->media()->wherePivot('collection', 'gallery')->detach();

        foreach (array_values($mediaIds) as $order => $id) {
            $this->media()->attach($id, ['collection' => 'gallery', 'sort_order' => $order]);
        }
    }
}
