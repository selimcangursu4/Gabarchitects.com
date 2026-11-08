<?php

namespace App\Filament\Resources\Redirects;

use App\Models\Redirect;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

/** Brief §9: 301 yönlendirme yönetimi. Yalnızca bulunamayan (404) adreslerde devreye girer. */
class RedirectResource extends Resource
{
    protected static ?string $model = Redirect::class;

    // Kullanıcı kararı (2026-11-08): sidebar'da gösterilmez. Sayfa adresle erişilebilir kalır.
    protected static bool $shouldRegisterNavigation = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUturnRight;

    protected static string|UnitEnum|null $navigationGroup = 'Site';

    protected static ?int $navigationSort = 5;

    protected static ?string $modelLabel = 'yönlendirme';

    protected static ?string $pluralModelLabel = 'Yönlendirmeler';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('from_path')->label('Eski adres')->required()->maxLength(255)
                ->placeholder('/eski-sayfa')
                ->helperText('Alan adı olmadan, "/" ile başlayan yol. Büyük/küçük harf ve sondaki "/" fark etmez.')
                ->rule('regex:/^\/[^\s]*$/')
                ->unique(ignoreRecord: true),
            TextInput::make('to_url')->label('Yeni adres')->required()->maxLength(255)
                ->placeholder('/projeler veya https://...')
                ->rule('regex:/^(\/[^\s]*|https?:\/\/[^\s]+)$/'),
            Select::make('status_code')->label('Tür')->native(false)->default(301)->required()
                ->options([301 => '301 — Kalıcı (SEO değeri aktarılır)', 302 => '302 — Geçici']),
            Toggle::make('is_active')->label('Aktif')->default(true)->inline(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('from_path')->label('Eski adres')->searchable()->fontFamily('mono'),
                TextColumn::make('to_url')->label('Yeni adres')->searchable()->fontFamily('mono'),
                TextColumn::make('status_code')->label('Tür')->badge()->color('gray'),
                TextColumn::make('hits')->label('Kullanım')->numeric()->sortable()
                    ->description(fn (Redirect $r) => $r->last_hit_at?->diffForHumans()),
                ToggleColumn::make('is_active')->label('Aktif')->disabled(fn () => ! auth()->user()->can('redirects.manage')),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageRedirects::route('/')];
    }
}
