<?php

use App\Models\Media;
use App\Models\Page;
use App\Services\MediaService;
use Illuminate\Database\Migrations\Migration;

/**
 * Kullanıcı talebi (2026-11-08): ana sayfa hero görseli → Boğaziçi yalıları.
 * Var olan veritabanlarında (Railway) görsel bir kez içe aktarılır, ana sayfa kapağı ve görsel notu güncellenir.
 * Yeni kurulumlar aynı görseli SiteContentSeeder'dan alır. Tekrar çalışırsa görseli ikinci kez eklemez.
 */
return new class extends Migration
{
    private const FILE = 'bogazici-yalilar.jpg';

    public function up(): void
    {
        $page = Page::where('key', 'home')->first();
        $path = database_path('seeders/assets/legacy/'.self::FILE);

        // Boş veritabanı (ilk kurulum / testler): seed zaten bu görseli kullanır
        if (! $page || ! is_file($path)) {
            return;
        }

        $media = Media::where('original_name', self::FILE)->first()
            ?? app(MediaService::class)->importFile($path, self::FILE);

        if (! $media->alt) {
            $media->update(['alt' => 'Boğaziçi kıyısında yalılar ve köprü']);
        }

        $content = $page->content ?? [];
        $content['hero']['image_caption'] = 'Boğaziçi yalıları';

        $page->update(['cover_media_id' => $media->id, 'content' => $content]);
    }

    public function down(): void
    {
        // Geri alma: önceki kapak bilinmediği için içerik değiştirilmez (görsel panelden değiştirilebilir)
    }
};
