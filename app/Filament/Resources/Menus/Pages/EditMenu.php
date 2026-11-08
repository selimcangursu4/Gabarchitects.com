<?php

namespace App\Filament\Resources\Menus\Pages;

use App\Filament\Resources\Menus\MenuResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Cache;

class EditMenu extends EditRecord
{
    protected static string $resource = MenuResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    /** Silinen/sıralanan öğeler sonrası site menüsü cache'i kesin olarak temizlenir. */
    protected function afterSave(): void
    {
        Cache::forget('menus.tree');
    }
}
