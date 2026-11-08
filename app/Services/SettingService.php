<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

/**
 * Site ayarları: tüm ayarlar tek cache anahtarında tutulur, değişince temizlenir.
 * Kullanım: settings('contact.address'), settings('modules.real_estate', false)
 */
class SettingService
{
    private const CACHE_KEY = 'site_settings.all';

    private ?array $settings = null;

    public function get(string $key, mixed $default = null): mixed
    {
        $this->settings ??= Cache::rememberForever(
            self::CACHE_KEY,
            fn () => SiteSetting::query()->pluck('value', 'key')->all(),
        );

        return array_key_exists($key, $this->settings)
            ? $this->settings[$key]
            : Arr::get($this->settings, $key, $default);
    }

    public function set(string $key, mixed $value, string $group = 'general'): void
    {
        SiteSetting::updateOrCreate(['key' => $key], ['value' => $value, 'group' => $group]);
        $this->settings = null;
    }

    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
        app()->forgetInstance(self::class);
    }
}
