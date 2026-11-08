<?php

namespace App\Models;

use App\Services\SettingService;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['group', 'key', 'value'])]
class SiteSetting extends Model
{
    protected function casts(): array
    {
        return ['value' => 'json'];
    }

    protected static function booted(): void
    {
        // Ayar değişince cache temizlenir (SettingService tüm ayarları tek anahtarda tutar).
        static::saved(fn () => SettingService::flush());
        static::deleted(fn () => SettingService::flush());
    }
}
