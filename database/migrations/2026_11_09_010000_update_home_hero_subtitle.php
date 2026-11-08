<?php

use App\Models\Page;
use Illuminate\Database\Migrations\Migration;

/**
 * Kullanıcı talebi (2026-11-08): hero alt metni → "Mimarlık | Gayrimenkul Proje Geliştirme | İnşaat".
 * Yalnızca önceki varsayılan metinlerden biri duruyorsa değişir; panelden farklı bir metin girilmişse dokunulmaz.
 */
return new class extends Migration
{
    private const NEW = 'Mimarlık | Gayrimenkul Proje Geliştirme | İnşaat';

    private const PREVIOUS = [
        'Gayrimenkul proje geliştirme ve inşaat çözümleri.',
        'Entegre gayrimenkul proje geliştirme ve inşaat çözümleri.',
    ];

    public function up(): void
    {
        $page = Page::where('key', 'home')->first();

        if (! $page || ! in_array($page->content['hero']['text'] ?? null, self::PREVIOUS, true)) {
            return;
        }

        $content = $page->content;
        $content['hero']['text'] = self::NEW;
        $page->update(['content' => $content]);
    }

    public function down(): void
    {
        $page = Page::where('key', 'home')->first();

        if ($page && ($page->content['hero']['text'] ?? null) === self::NEW) {
            $content = $page->content;
            $content['hero']['text'] = self::PREVIOUS[0];
            $page->update(['content' => $content]);
        }
    }
};
