<?php

namespace App\Filament\Resources\Faqs;

use App\Models\Faq;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

class FaqResource extends Resource
{
    protected static ?string $model = Faq::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQuestionMarkCircle;

    protected static string|UnitEnum|null $navigationGroup = 'İçerik';

    protected static ?int $navigationSort = 7;

    protected static ?string $modelLabel = 'soru';

    protected static ?string $pluralModelLabel = 'SSS';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('question')->label('Soru')->required()->maxLength(255)->columnSpanFull(),
            Textarea::make('answer')->label('Cevap')->required()->rows(5)->columnSpanFull(),
            Toggle::make('is_active')->label('Aktif')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('question')->label('Soru')->searchable()->wrap(),
                ToggleColumn::make('is_active')->label('Aktif')->disabled(fn () => ! auth()->user()->can('faqs.manage')),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageFaqs::route('/')];
    }
}
