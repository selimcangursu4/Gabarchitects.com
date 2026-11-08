<?php

namespace App\Services;

use App\Models\Media;
use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Görsel içe aktarma:
 *  - Orijinal dosya değiştirilmeden private diske kopyalanır (ULID adla).
 *  - public/media/{uuid}/{genişlik}.webp (+ .avif) türevleri üretilir.
 *  - Dosya adı kullanıcıdan alınmaz; MIME ve görsel bütünlüğü doğrulanır.
 */
class MediaService
{
    public function storeUpload(UploadedFile $file, array $attributes = []): Media
    {
        return $this->importFile($file->getRealPath(), $file->getClientOriginalName(), $attributes);
    }

    public function importFile(string $path, ?string $originalName = null, array $attributes = []): Media
    {
        $bytes = @file_get_contents($path);
        $info = $bytes !== false ? @getimagesizefromstring($bytes) : false;

        if (! $info || ! in_array($info['mime'], config('media.allowed_mimes'), true)) {
            throw new InvalidArgumentException('Desteklenmeyen veya bozuk görsel dosyası.');
        }

        if (strlen($bytes) > config('media.max_upload_kb') * 1024) {
            throw new InvalidArgumentException('Görsel izin verilen boyutu aşıyor.');
        }

        // Boyut başlıktan okunur (görsel açılmadan): devasa piksel sayısı belleği tüketmeden reddedilir
        if ($info[0] < 1 || $info[1] < 1 || config('media.max_pixels') < $info[0] * $info[1]) {
            throw new InvalidArgumentException('Görselin piksel boyutu çok büyük (en fazla '.round(config('media.max_pixels') / 1_000_000).' megapiksel).');
        }

        $image = @imagecreatefromstring($bytes);

        if (! $image instanceof GdImage) {
            throw new InvalidArgumentException('Görsel okunamadı.');
        }

        $uuid = (string) Str::ulid();
        $extension = match ($info['mime']) {
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => 'jpg',
        };

        $originalPath = "media/originals/{$uuid}.{$extension}";
        Storage::disk(config('media.original_disk'))->put($originalPath, $bytes);

        $variants = $this->generateVariants($image, $uuid);
        imagedestroy($image);

        return Media::create([
            'uuid' => $uuid,
            'disk' => config('media.disk'),
            'original_disk' => config('media.original_disk'),
            'original_path' => $originalPath,
            'original_name' => Str::limit(basename($originalName ?? $path), 250, ''),
            'mime_type' => $info['mime'],
            'size' => strlen($bytes),
            'width' => $info[0],
            'height' => $info[1],
            'variants' => $variants,
            ...$attributes,
        ]);
    }

    /**
     * Medyayı ve tüm dosyalarını kalıcı olarak siler.
     */
    public function purge(Media $media): void
    {
        Storage::disk($media->disk)->deleteDirectory($media->directory());
        Storage::disk($media->original_disk)->delete($media->original_path);
        $media->forceDelete();
    }

    public static function avifSupported(): bool
    {
        return config('media.avif') && function_exists('imageavif') && (gd_info()['AVIF Support'] ?? false);
    }

    /** @return array<string, array<int, int>> */
    private function generateVariants(GdImage $image, string $uuid): array
    {
        $disk = Storage::disk(config('media.disk'));
        $w = imagesx($image);
        $h = imagesy($image);

        // Kaynaktan küçük genişlikler + kaynak genişliği (en fazla 2400). Büyütme yapılmaz.
        $widths = array_values(array_filter(config('media.widths'), fn (int $tw) => $tw < $w));
        $widths[] = min($w, max(config('media.widths')));
        $widths = array_values(array_unique($widths));

        $formats = ['webp'];
        if (static::avifSupported()) {
            $formats[] = 'avif';
        }

        $variants = array_fill_keys($formats, []);

        foreach ($widths as $tw) {
            $th = (int) round($h * $tw / $w);
            $resized = imagecreatetruecolor($tw, $th);
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $tw, $th, $w, $h);

            foreach ($formats as $format) {
                ob_start();
                $format === 'avif'
                    ? imageavif($resized, null, config('media.quality.avif'), 6)
                    : imagewebp($resized, null, config('media.quality.webp'));
                $disk->put("{$uuid}/{$tw}.{$format}", (string) ob_get_clean());
                $variants[$format][] = $tw;
            }

            imagedestroy($resized);
        }

        return $variants;
    }
}
