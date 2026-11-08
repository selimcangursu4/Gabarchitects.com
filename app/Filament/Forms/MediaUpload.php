<?php

namespace App\Filament\Forms;

use App\Models\Media;
use App\Services\MediaService;
use Filament\Forms\Components\BaseFileUpload;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * Medya kütüphanesine bağlı görsel alanları.
 *
 * Alan durumu dosya yolu değil medya ID'sidir. Yüklenen dosya MediaService'e verilir:
 * orijinal korunur, WebP/AVIF türevleri üretilir, Media kaydı oluşur (brief §7.5).
 * Alandan görsel kaldırmak dosyayı silmez; silme yalnızca Medya Kütüphanesi'nden yapılır.
 */
class MediaUpload
{
    /** Tek görsel: cover_media_id, logo_media_id, photo_media_id... */
    public static function make(string $name): FileUpload
    {
        return static::base(FileUpload::make($name));
    }

    /**
     * Sıralanabilir galeri (mediables, collection = gallery).
     * Modelde HasMediaGallery gerekir.
     */
    public static function gallery(string $name = 'gallery_media'): FileUpload
    {
        return static::base(FileUpload::make($name))
            ->multiple()
            ->reorderable()
            ->appendFiles()
            ->panelLayout('grid')
            ->maxFiles(60)
            ->afterStateHydrated(function (BaseFileUpload $component, ?Model $record) {
                $component->rawState(static::keyed($record?->exists ? $record->gallery()->pluck('media.id')->all() : []));
            })
            ->dehydrated(false)
            // Filament'in yol kontrolü alanı model sütunu sanır (galeri ilişkidir) → kapatılır;
            // yerine kaydederken yalnızca var olan medya ID'leri sırasıyla kabul edilir.
            ->preventFilePathTampering(false)
            ->saveRelationshipsUsing(function (Model $record, ?array $state) {
                $ids = static::ids($state);
                $existing = Media::whereKey($ids)->pluck('id')->all();
                $record->syncGallery(array_values(array_filter($ids, fn (int $id) => in_array($id, $existing, true))));
            });
    }

    private static function base(FileUpload $field): FileUpload
    {
        return $field
            ->image()
            ->acceptedFileTypes(config('media.allowed_mimes'))
            ->maxSize(config('media.max_upload_kb'))
            ->imagePreviewHeight('180')
            ->fetchFileInformation(false)
            ->afterStateHydrated(function (BaseFileUpload $component, $state) {
                $component->rawState(static::keyed(Arr::wrap($state)));
            })
            ->getUploadedFileUsing(function (string $file): ?array {
                $media = ctype_digit($file) ? Media::find($file) : null;

                return $media ? [
                    'name' => $media->original_name,
                    'size' => $media->size,
                    'type' => $media->mime_type,
                    'url' => $media->url(480),
                ] : null;
            })
            // MediaService reddederse (bozuk dosya, piksel sınırı) sunucu hatası değil alan altında mesaj
            ->saveUploadedFileUsing(function (BaseFileUpload $component, TemporaryUploadedFile $file): string {
                try {
                    return (string) app(MediaService::class)->storeUpload($file, ['uploaded_by' => auth()->id()])->id;
                } catch (InvalidArgumentException $e) {
                    throw ValidationException::withMessages([$component->getStatePath() => $e->getMessage()]);
                }
            })
            ->deleteUploadedFileUsing(fn () => null)
            // İstemciden gelen değerler: yalnızca var olan medya ID'leri kabul edilir
            ->preventFilePathTampering(allowFilePathUsing: fn (string $file): bool => ctype_digit($file) && Media::whereKey($file)->exists());
    }

    /** @return array<string, string> uuid => medya ID */
    private static function keyed(array $ids): array
    {
        return collect($ids)->filter()->mapWithKeys(fn ($id) => [(string) Str::uuid() => (string) $id])->all();
    }

    /** @return array<int, int> */
    private static function ids(?array $state): array
    {
        // Not: ->map('intval') KULLANILMAZ — map anahtarı 2. parametre (sayı tabanı) olarak geçirir: intval("1", 1) = 0
        return collect($state ?? [])->filter(fn ($v) => is_string($v) && ctype_digit($v))->map(fn (string $v) => (int) $v)->values()->all();
    }
}
