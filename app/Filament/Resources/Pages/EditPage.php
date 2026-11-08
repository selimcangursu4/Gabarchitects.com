<?php

namespace App\Filament\Resources\Pages;

use Filament\Resources\Pages\EditRecord;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return [];   // sayfalar silinemez
    }
}
