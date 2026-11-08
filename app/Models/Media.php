<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

#[Table('media')]
#[Fillable(['uuid', 'disk', 'original_disk', 'original_path', 'original_name', 'mime_type', 'size', 'width', 'height', 'variants', 'alt', 'title', 'uploaded_by'])]
class Media extends Model
{
    use LogsActivity, SoftDeletes;

    protected function casts(): array
    {
        return [
            'variants' => 'array',
            'size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
        ];
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /** Türev klasörü: {uuid}/ */
    public function directory(): string
    {
        return $this->uuid;
    }

    /** @return array<int, int> */
    public function widths(string $format = 'webp'): array
    {
        return $this->variants[$format] ?? [];
    }

    public function hasFormat(string $format): bool
    {
        return $this->widths($format) !== [];
    }

    public function url(?int $width = null, string $format = 'webp'): ?string
    {
        $widths = $this->widths($format);

        if ($widths === []) {
            return null;
        }

        $width ??= collect($widths)->first(fn (int $w) => $w >= 960) ?? last($widths);

        return Storage::disk($this->disk)->url("{$this->directory()}/{$width}.{$format}");
    }

    public function srcset(string $format = 'webp'): string
    {
        return collect($this->widths($format))
            ->map(fn (int $w) => $this->url($w, $format)." {$w}w")
            ->implode(', ');
    }

    /**
     * Paylaşım (Open Graph / schema) görseli: hedef genişliğe en yakın gerçek WebP türevi.
     * Yalnızca var olan türevler kullanılır (olmayan dosyaya adres üretilmez).
     *
     * @return array{url:string,width:int,height:int,alt:?string}|null
     */
    public function shareImage(): ?array
    {
        $widths = $this->widths('webp');

        if ($widths === []) {
            return null;
        }

        $target = (int) config('seo.share_image_width', 1200);
        $width = collect($widths)->sort()->first(fn (int $w) => $w >= $target) ?? max($widths);

        return [
            'url' => $this->url($width),
            'width' => $width,
            'height' => $this->width ? (int) round($width * $this->height / $this->width) : 0,
            'alt' => $this->alt,
        ];
    }

    /** Bu medyayı kullanan kayıt sayısı (galeri + kapak). Silme uyarısı için. */
    public function usageCount(): int
    {
        $count = DB::table('mediables')->where('media_id', $this->id)->count();

        foreach (self::coverColumns() as [$table, $column]) {
            $count += DB::table($table)->where($column, $this->id)->count();
        }

        return $count;
    }

    /** @return array<int, array{0:string,1:string}> */
    public static function coverColumns(): array
    {
        return [
            ['services', 'cover_media_id'],
            ['projects', 'cover_media_id'],
            ['pages', 'cover_media_id'],
            ['blog_posts', 'cover_media_id'],
            ['properties', 'cover_media_id'],
            ['client_references', 'logo_media_id'],
            ['team_members', 'photo_media_id'],
        ];
    }
}
