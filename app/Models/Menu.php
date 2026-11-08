<?php

namespace App\Models;

use App\Models\Concerns\FlushesCache;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['key', 'name'])]
class Menu extends Model
{
    use FlushesCache, LogsActivity;

    protected static array $flushesCacheKeys = ['menus.tree'];

    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)->orderBy('sort_order');
    }

    /** Üst seviye öğeler (panelde iç içe düzenleme için) */
    public function rootItems(): HasMany
    {
        return $this->hasMany(MenuItem::class)->whereNull('parent_id')->orderBy('sort_order');
    }

    /** Aktif üst seviye öğeler + aktif alt öğeleri. */
    public function tree(): HasMany
    {
        return $this->items()
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->with(['children' => fn ($q) => $q->where('is_active', true)]);
    }
}
