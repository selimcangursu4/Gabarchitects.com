<?php

use App\Models\Media;
use App\Models\Page;
use App\Services\MediaService;
use Illuminate\Database\Migrations\Migration;

/**
 * Kullanıcı talebi (2026-11-08): ana sayfa hero görseli → Boğaziçi sahili (5120×3413).
 * "Çözünürlüğü asla düşürme": türevler 5120 px'e kadar üretilir (config/media.php), orijinal değişmeden saklanır.
 * Var olan veritabanlarında görsel bir kez içe aktarılır; yeni kurulumlar SiteContentSeeder'dan alır.
 */
return new class extends Migration
{
    private const FILE = 'bogazici-sahil.jpg';

    public function up(): void
    {
        $page = Page::where('key', 'home')->first();
        $path = database_path('seeders/assets/legacy/'.self::FILE);

        if (! $page || ! is_file($path)) {
            return;
        }

        $media = Media::where('original_name', self::FILE)->first()
            ?? app(MediaService::class)->importFile($path, self::FILE);

        if (! $media->alt) {
            $media->update(['alt' => 'Boğaziçi kıyısında yamaca yayılan konutlar ve tekneler']);
        }

        $content = $page->content ?? [];
        $content['hero']['image_caption'] = 'Boğaziçi';

        $page->update(['cover_media_id' => $media->id, 'content' => $content]);
    }

    public function down(): void
    {
        // Önceki kapak panelden yeniden seçilebilir; içerik değiştirilmez
    }
};
