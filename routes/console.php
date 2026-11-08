<?php

use App\Models\Media;
use App\Models\Page;
use App\Models\Property;
use App\Services\MediaService;
use Illuminate\Support\Facades\Artisan;

/*
| Lokal tasarım için üretilen örnek ilanları ve görsellerini kalıcı olarak siler.
| Gerçek ilanlara (is_sample=false) dokunmaz.
*/
Artisan::command('gab:purge-samples', function (MediaService $media) {
    $properties = Property::withTrashed()->where('is_sample', true)->get();

    foreach ($properties as $property) {
        $cover = $property->cover_media_id ? Media::find($property->cover_media_id) : null;
        $property->forceDelete();

        if ($cover && $cover->usageCount() === 0) {
            $media->purge($cover);
        }
    }

    $this->info($properties->count().' örnek ilan silindi.');
})->purpose('Örnek (is_sample) ilanları ve görsellerini siler');

/*
| İlk kurulum: veritabanı boşsa (sayfa kaydı yoksa) seed çalıştırır; doluysa hiçbir şeye dokunmaz.
| Konteyner her başladığında güvenle çağrılabilir (docker/entrypoint.sh). Panelden girilen içerik ezilmez.
*/
Artisan::command('gab:seed-if-empty', function () {
    if (Page::query()->exists()) {
        $this->info('Veritabanı dolu; seed atlandı.');

        return;
    }

    $this->info('Boş veritabanı: ilk içerik yükleniyor…');
    $this->call('db:seed', ['--force' => true]);
})->purpose('Veritabanı boşsa ilk içeriği yükler (idempotent)');

/*
| Test/önizleme sunucusu (GAB_SEED_SAMPLES=true): ÖRNEK ilan hiç yoksa yükler; varsa dokunmaz.
| Kapalıyken hiçbir şey yapmaz. Silmek için: gab:purge-samples (silindikten sonra bayrak kapatılmalı).
*/
Artisan::command('gab:seed-samples-if-missing', function () {
    if (! config('gab.seed_samples')) {
        $this->info('GAB_SEED_SAMPLES kapalı; örnek ilan yüklenmedi.');

        return;
    }

    if (Property::withTrashed()->where('is_sample', true)->exists()) {
        $this->info('Örnek ilanlar zaten var.');

        return;
    }

    $this->call('db:seed', ['--class' => 'SamplePropertySeeder', '--force' => true]);
})->purpose('Test sunucusunda ÖRNEK ilan yoksa yükler (idempotent)');
