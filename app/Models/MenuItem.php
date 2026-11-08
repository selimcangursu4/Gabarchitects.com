<?php

namespace App\Models;

use App\Models\Concerns\FlushesCache;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['menu_id', 'parent_id', 'label', 'url', 'open_in_new_tab', 'sort_order', 'is_active'])]
class MenuItem extends Model
{
    use FlushesCache, LogsActivity;

    protected static array $flushesCacheKeys = ['menus.tree'];

    protected function casts(): array
    {
        return [
            'open_in_new_tab' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * Cache'e konabilecek düz dizi (Laravel 13: cache'ten nesne geri yüklenmez — serializable_classes=false).
     *
     * @return array{label:string,url:string,new_tab:bool,children:array}
     */
    public function toNavArray(): array
    {
        return [
            'label' => $this->label,
            'url' => $this->url,
            'new_tab' => $this->open_in_new_tab,
            'children' => $this->relationLoaded('children')
                ? $this->children->map->toNavArray()->values()->all()
                : [],
        ];
    }

    public static function isExternal(string $url): bool
    {
        return Str::startsWith($url, ['http://', 'https://', 'mailto:', 'tel:']);
    }

    public static function hrefFor(string $url): string
    {
        return static::isExternal($url) ? $url : url($url);
    }

    /** Geçerli istek bu adresin altında mı? */
    public static function isActiveUrl(string $url): bool
    {
        if (static::isExternal($url)) {
            return false;
        }

        $path = trim($url, '/');

        return $path === '' ? request()->is('/') : request()->is($path, $path.'/*');
    }
}
