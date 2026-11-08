<?php

use App\Models\Media;
use App\Models\Page;
use App\Models\Project;
use App\Models\Property;
use App\Services\MediaService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;

/**
 * Kullanıcı talebi (2026-11-08): canlıya geçmeden önce test amaçlı proje ve ilanlar silinir.
 * Yalnızca panelden girilen "Arkas Railport Uygulama Projemiz" projesi ve "Konya'da Fırsat Tarla" ilanı kalır.
 *
 * Güvenlik: iki kayıt da yoksa (yeni kurulum, lokal, testler) HİÇBİR ŞEY silinmez.
 * Silinen kayıtların yalnızca başka yerde kullanılmayan görselleri (dosyalarıyla) kaldırılır.
 */
return new class extends Migration
{
    private const KEEP_PROJECT = 'arkas-railport-uygulama-projemiz';

    private const KEEP_PROPERTY = 'konyada-firsat-tarla';

    public function up(): void
    {
        $keepProject = Project::withTrashed()->where('slug', self::KEEP_PROJECT)->first();
        $keepProperty = Property::withTrashed()->where('slug', self::KEEP_PROPERTY)->first();

        if (! $keepProject || ! $keepProperty) {
            return;
        }

        $mediaIds = [];

        foreach ([[Project::class, $keepProject->id], [Property::class, $keepProperty->id]] as [$model, $keepId]) {
            $model::withTrashed()->whereKeyNot($keepId)->get()->each(function ($record) use (&$mediaIds) {
                $mediaIds[] = $record->cover_media_id;
                array_push($mediaIds, ...$record->media()->pluck('media.id')->all());

                $record->media()->detach();
                $record->forceDelete();
            });
        }

        $service = app(MediaService::class);

        foreach (Media::whereIn('id', array_unique(array_filter($mediaIds)))->get() as $media) {
            if ($media->usageCount() === 0) {
                $service->purge($media);
            }
        }

        // Ana sayfa "yaklaşım" görseli silinen bir projeye bağlıysa kalan projeye bağlanır
        if ($home = Page::where('key', 'home')->first()) {
            $content = $home->content ?? [];
            $current = $content['approach']['project_id'] ?? null;

            if ($current !== null && (int) $current !== $keepProject->id) {
                $content['approach']['project_id'] = $keepProject->id;
                $home->update(['content' => $content]);
            }
        }

        Cache::flush();
    }

    public function down(): void
    {
        // Geri alınamaz (silinen içerik yedekten dönülür).
    }
};
