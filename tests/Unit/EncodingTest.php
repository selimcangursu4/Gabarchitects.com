<?php

namespace Tests\Unit;

use FilesystemIterator;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Türkçe karakterler (ç ğ ı İ ö ş ü) bozulmasın: depodaki tüm metin dosyaları geçerli UTF-8 olmalı.
 * Örnek hata: Windows'ta PowerShell Add-Content varsayılan ANSI ile yazar → "Yönetici" → "Y?netici".
 */
class EncodingTest extends TestCase
{
    private const SKIP = ['vendor', 'node_modules', '.git', 'storage', 'public/build', 'public/media', 'public/css', 'public/js', 'public/fonts', 'database/seeders/assets'];

    private const EXTENSIONS = ['php', 'js', 'css', 'md', 'json', 'xml', 'yml', 'yaml', 'txt'];

    public function test_all_text_files_are_valid_utf8(): void
    {
        $root = dirname(__DIR__, 2);
        $invalid = [];
        $checked = 0;

        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));

        foreach ($files as $file) {
            $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));

            foreach (self::SKIP as $dir) {
                if (str_starts_with($relative, $dir.'/')) {
                    continue 2;
                }
            }

            $isEnv = str_starts_with($file->getFilename(), '.env');
            if (! $isEnv && ! in_array(strtolower($file->getExtension()), self::EXTENSIONS, true)) {
                continue;
            }

            $checked++;
            if (! mb_check_encoding((string) file_get_contents($file->getPathname()), 'UTF-8')) {
                $invalid[] = $relative;
            }
        }

        $this->assertGreaterThan(50, $checked);
        $this->assertSame([], $invalid, 'Geçersiz UTF-8 (Türkçe karakter bozulması): '.implode(', ', $invalid));
    }
}
