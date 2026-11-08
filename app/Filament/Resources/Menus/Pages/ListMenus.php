<?php

namespace App\Filament\Resources\Menus\Pages;

use App\Filament\Resources\Menus\MenuResource;
use Filament\Resources\Pages\ListRecords;

/** Menüler (header, legal) sabittir; yalnızca içerikleri düzenlenir. */
class ListMenus extends ListRecords
{
    protected static string $resource = MenuResource::class;
}
