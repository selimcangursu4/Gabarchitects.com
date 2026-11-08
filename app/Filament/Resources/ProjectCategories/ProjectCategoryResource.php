<?php

namespace App\Filament\Resources\ProjectCategories;

use App\Filament\Forms\CommonFields;
use App\Models\ProjectCategory;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

/** Brief §4.4: proje kategorileri yönetilebilir. */
class ProjectCategoryResource extends Resource
{
    protected static ?string $model = ProjectCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static string|UnitEnum|null $navigationGroup = 'İçerik';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'proje kategorisi';

    protected static ?string $pluralModelLabel = 'Proje Kategorileri';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            ...CommonFields::titleAndSlug('name', 'Kategori adı', 'projeler/kategori'),
            Textarea::make('description')->label('Açıklama')->rows(3)->columnSpanFull(),
            Toggle::make('is_active')->label('Aktif')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('name')->label('Kategori')->searchable(),
                TextColumn::make('projects_count')->label('Proje')->counts('projects'),
                ToggleColumn::make('is_active')->label('Aktif')->disabled(fn () => ! auth()->user()->can('projects.manage')),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageProjectCategories::route('/')];
    }
}
