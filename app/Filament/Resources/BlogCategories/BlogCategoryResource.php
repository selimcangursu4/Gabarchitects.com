<?php

namespace App\Filament\Resources\BlogCategories;

use App\Filament\Forms\CommonFields;
use App\Models\BlogCategory;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class BlogCategoryResource extends Resource
{
    protected static ?string $model = BlogCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFolder;

    protected static string|UnitEnum|null $navigationGroup = 'İçerik';

    protected static ?int $navigationSort = 6;

    protected static ?string $modelLabel = 'blog kategorisi';

    protected static ?string $pluralModelLabel = 'Blog Kategorileri';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            ...CommonFields::titleAndSlug('name', 'Kategori adı', 'blog/kategori'),
            Textarea::make('description')->label('Açıklama')->rows(3)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('name')->label('Kategori')->searchable(),
                TextColumn::make('posts_count')->label('Yazı')->counts('posts'),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageBlogCategories::route('/')];
    }
}
