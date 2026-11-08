<?php

namespace App\Filament\Resources\ContactMessages\Pages;

use App\Enums\ContactStatus;
use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Models\ContactMessage;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListContactMessages extends ListRecords
{
    protected static string $resource = ContactMessageResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Tümü'),
            'unread' => Tab::make('Okunmamış')
                ->badge(fn () => ContactMessage::unread()->count() ?: null)
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('read_at')),
            'open' => Tab::make('Açık')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', [ContactStatus::New->value, ContactStatus::InReview->value])),
            'archived' => Tab::make('Arşiv')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', ContactStatus::Archived->value)),
        ];
    }
}
