<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Marka değişikliği (TD-061, kullanıcı kararı 2026-11-08): "GAB Architects" → "GAB Gayrimenkul Proje Geliştirme".
 * Var olan veritabanlarında (lokal, Railway) site içeriğindeki marka adı güncellenir.
 * Geçmiş kayıtlara (iletişim mesajları, işlem kayıtları) dokunulmaz.
 */
return new class extends Migration
{
    private const OLD = 'GAB Architects';

    private const NEW = 'GAB Gayrimenkul Proje Geliştirme';

    /** Tablo → metin sütunları (yalnızca sitede görünen içerik) */
    private const COLUMNS = [
        'site_settings' => ['value'],
        'pages' => ['title', 'content', 'body', 'meta_title', 'meta_description'],
        'services' => ['title', 'excerpt', 'body', 'meta_title', 'meta_description'],
        'projects' => ['title', 'subtitle', 'summary', 'body', 'meta_title', 'meta_description'],
        'project_categories' => ['description', 'meta_title', 'meta_description'],
        'blog_posts' => ['title', 'excerpt', 'body', 'meta_title', 'meta_description'],
        'blog_categories' => ['description', 'meta_title', 'meta_description'],
        'faqs' => ['question', 'answer'],
        'client_references' => ['description'],
        'team_members' => ['bio'],
        'properties' => ['summary', 'body', 'meta_title', 'meta_description'],
        'media' => ['alt', 'title'],
        'menu_items' => ['label'],
    ];

    public function up(): void
    {
        // Türkçe ek uyumu önce: "GAB Architects'in" → "… Geliştirme'nin"
        $this->replace([self::OLD."'in" => self::NEW."'nin", self::OLD => self::NEW]);
    }

    public function down(): void
    {
        $this->replace([self::NEW."'nin" => self::OLD."'in", self::NEW => self::OLD]);
    }

    /** @param  array<string, string>  $pairs */
    private function replace(array $pairs): void
    {
        foreach (self::COLUMNS as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach ($columns as $column) {
                if (! Schema::hasColumn($table, $column)) {
                    continue;
                }

                foreach ($pairs as $from => $to) {
                    // REPLACE: MySQL ve SQLite'ta aynı
                    DB::table($table)->where($column, 'like', '%'.$from.'%')
                        ->update([$column => DB::raw('REPLACE('.DB::getQueryGrammar()->wrap($column).', '.DB::getPdo()->quote($from).', '.DB::getPdo()->quote($to).')')]);
                }
            }
        }

        // Ayar / menü cache'leri eski adı tutmasın
        Cache::flush();
    }
};
