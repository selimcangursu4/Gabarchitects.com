<?php

namespace App\Filament\Resources\References\Pages;

use App\Filament\Resources\References\ReferenceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateReference extends CreateRecord
{
    protected static string $resource = ReferenceResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
