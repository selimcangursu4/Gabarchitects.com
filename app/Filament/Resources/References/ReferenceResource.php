<?php

namespace App\Filament\Resources\References;

use App\Filament\Forms\MediaUpload;
use App\Filament\Tables\CommonColumns;
use App\Models\Reference;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

/** Talep 8: ana sayfada öne çıkan son 16; Referanslar sayfasında tüm aktifler. */
class ReferenceResource extends Resource
{
    protected static ?string $model = Reference::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;

    protected static string|UnitEnum|null $navigationGroup = 'İçerik';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'referans';

    protected static ?string $pluralModelLabel = 'Referanslar';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(2)->columnSpanFull()->schema([
                TextInput::make('name')->label('Kurum / proje adı')->required()->maxLength(255),
                TextInput::make('service')->label('Verilen hizmet')->maxLength(255)->placeholder('Tasarım ve uygulama'),
                Textarea::make('description')->label('Açıklama')->rows(3)->columnSpanFull(),
                Select::make('project_id')->label('İlişkili proje')->relationship('project', 'title')
                    ->searchable()->preload()->native(false),
                TextInput::make('website_url')->label('Web sitesi')->url()->maxLength(255),
                MediaUpload::make('logo_media_id')->label('Logo')
                    ->helperText('Logo kullanım izni alınmış olmalıdır.')->columnSpanFull(),
                Toggle::make('is_active')->label('Aktif (Referanslar sayfasında göster)')->default(true),
                Toggle::make('is_featured')->label('Ana sayfada öne çıkar')
                    ->helperText('Öne çıkan son 16 referans ana sayfada listelenir.'),
                TextInput::make('sort_order')->label('Sıra')->numeric()->default(0),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('logo'))
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                CommonColumns::thumbnail('logo', 'Logo'),
                TextColumn::make('name')->label('Ad')->searchable()->description(fn (Reference $r) => $r->service),
                ToggleColumn::make('is_active')->label('Aktif')->disabled(fn () => ! auth()->user()->can('references.manage')),
                ToggleColumn::make('is_featured')->label('Öne çıkan')->disabled(fn () => ! auth()->user()->can('references.manage')),
                TextColumn::make('created_at')->label('Eklenme')->date('d.m.Y')->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Aktif'),
                TernaryFilter::make('is_featured')->label('Öne çıkan'),
                TrashedFilter::make(),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make(), RestoreAction::make(), ForceDeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReferences::route('/'),
            'create' => Pages\CreateReference::route('/create'),
            'edit' => Pages\EditReference::route('/{record}/edit'),
        ];
    }
}
