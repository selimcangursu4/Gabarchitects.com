<?php

use App\Models\MenuItem;
use App\Models\Page;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;

/**
 * Kullanıcı talepleri (2026-11-08), var olan veritabanları için:
 *  - Hero üst etiketi "Mimarlık · Proje Geliştirme · İnşaat" kaldırılır
 *  - Hero metninden "Entegre" çıkarılır
 *  - Menü "Portföyümüz" → "Portföylerimiz"
 * Yalnızca tam eşleşen eski metinler değişir; panelden farklı bir metin girilmişse dokunulmaz.
 */
return new class extends Migration
{
    public function up(): void
    {
        $page = Page::where('key', 'home')->first();

        if ($page) {
            $content = $page->content ?? [];

            if (($content['hero']['eyebrow'] ?? null) === 'Mimarlık · Proje Geliştirme · İnşaat') {
                $content['hero']['eyebrow'] = null;
            }
            if (($content['hero']['text'] ?? null) === 'Entegre gayrimenkul proje geliştirme ve inşaat çözümleri.') {
                $content['hero']['text'] = 'Gayrimenkul proje geliştirme ve inşaat çözümleri.';
            }

            if ($content !== $page->content) {
                $page->update(['content' => $content]);
            }
        }

        MenuItem::where('label', 'Portföyümüz')->get()->each->update(['label' => 'Portföylerimiz']);

        Cache::forget('menus.tree');
    }

    public function down(): void
    {
        MenuItem::where('label', 'Portföylerimiz')->get()->each->update(['label' => 'Portföyümüz']);
        Cache::forget('menus.tree');
    }
};
