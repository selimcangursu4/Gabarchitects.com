<?php

use App\Services\SettingService;

if (! function_exists('settings')) {
    /** Site ayarı oku: settings('contact.address') */
    function settings(string $key, mixed $default = null): mixed
    {
        return app(SettingService::class)->get($key, $default);
    }
}
