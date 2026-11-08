<?php

namespace App\Filament\Resources\ActivityLogs;

use App\Models\ActivityLog;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

/** Brief §7.8: yönetim işlemleri loglanır (salt okunur). */
class ActivityLogResource extends Resource
{
    protected static ?string $model = ActivityLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = 'Yönetim';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'işlem kaydı';

    protected static ?string $pluralModelLabel = 'İşlem Kayıtları';

    public const EVENTS = [
        'created' => 'Oluşturdu',
        'updated' => 'Güncelledi',
        'deleted' => 'Sildi',
        'restored' => 'Geri yükledi',
        'login' => 'Giriş yaptı',
    ];

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('user.name')->label('Kullanıcı')->placeholder('Sistem'),
            TextEntry::make('event')->label('İşlem')->formatStateUsing(fn (string $state) => static::EVENTS[$state] ?? $state),
            TextEntry::make('description')->label('Kayıt')->columnSpanFull(),
            TextEntry::make('created_at')->label('Tarih')->dateTime('d.m.Y H:i:s'),
            TextEntry::make('ip_address')->label('IP'),
            KeyValueEntry::make('properties.old')->label('Önceki değerler')->columnSpanFull()
                ->visible(fn (ActivityLog $r) => filled($r->properties['old'] ?? null)),
            KeyValueEntry::make('properties.new')->label('Yeni değerler')->columnSpanFull()
                ->visible(fn (ActivityLog $r) => filled($r->properties['new'] ?? null)),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('user'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')->label('Tarih')->dateTime('d.m.Y H:i')->sortable(),
                TextColumn::make('user.name')->label('Kullanıcı')->placeholder('Sistem')->searchable(),
                TextColumn::make('event')->label('İşlem')->badge()
                    ->formatStateUsing(fn (string $state) => static::EVENTS[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        'created' => 'success', 'deleted' => 'danger', 'updated' => 'warning', default => 'gray',
                    }),
                TextColumn::make('description')->label('Kayıt')->searchable()->wrap(),
                TextColumn::make('ip_address')->label('IP')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('event')->label('İşlem')->options(static::EVENTS),
                SelectFilter::make('user_id')->label('Kullanıcı')->relationship('user', 'name'),
            ])
            ->recordActions([ViewAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListActivityLogs::route('/')];
    }
}
