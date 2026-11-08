<?php

namespace App\Filament\Widgets;

use App\Enums\ContactStatus;
use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Models\ContactMessage;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestContactMessages extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Son iletişim talepleri';

    public static function canView(): bool
    {
        return (bool) auth()->user()?->can('contact_messages.manage');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(ContactMessage::query()->latest()->limit(6))
            ->paginated(false)
            ->recordUrl(fn (ContactMessage $r) => ContactMessageResource::getUrl('view', ['record' => $r]))
            ->recordClasses(fn (ContactMessage $r) => $r->read_at ? null : 'font-semibold')
            ->columns([
                TextColumn::make('name')->label('Ad Soyad')->description(fn (ContactMessage $r) => $r->email),
                TextColumn::make('subject')->label('Konu')->placeholder('—')->limit(40),
                TextColumn::make('source')->label('Kaynak')->badge()->color('gray')
                    ->formatStateUsing(fn (string $state) => ContactMessageResource::SOURCES[$state] ?? $state),
                TextColumn::make('status')->label('Durum')->badge()
                    ->formatStateUsing(fn (ContactStatus $state) => $state->label())
                    ->color(fn (ContactStatus $state) => ContactMessageResource::statusColor($state)),
                TextColumn::make('created_at')->label('Tarih')->since(),
            ])
            ->emptyStateHeading('Henüz form gönderilmedi');
    }
}
