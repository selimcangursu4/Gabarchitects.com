<?php

namespace App\Filament\Resources\Services;

use App\Filament\Concerns\UsesIdRecordUrls;
use App\Filament\Forms\CommonFields;
use App\Filament\Forms\MediaUpload;
use App\Filament\Tables\CommonColumns;
use App\Models\Service;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

/** Talep 7: hizmetler panelden eklenir, düzenlenir, silinir, sıralanır. */
class ServiceResource extends Resource
{
    use UsesIdRecordUrls;

    protected static ?string $model = Service::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static string|UnitEnum|null $navigationGroup = 'İçerik';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'hizmet';

    protected static ?string $pluralModelLabel = 'Hizmetler';

    protected static ?string $recordTitleAttribute = 'title';

    // Sitede slug, panelde sabit ID (başlık değişse de düzenleme adresi değişmez)
    protected static ?string $recordRouteKeyName = 'id';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(['default' => 1, 'lg' => 3])->columnSpanFull()->schema([
                Grid::make(1)->columnSpan(['lg' => 2])->schema([
                    Section::make('Hizmet')->columns(2)->schema([
                        ...CommonFields::titleAndSlug(urlPrefix: 'hizmetler'),
                        Textarea::make('excerpt')->label('Kısa açıklama')->rows(3)->maxLength(500)->columnSpanFull()
                            ->helperText('Ana sayfadaki hizmet listesinde ve kartlarda görünür.'),
                        CommonFields::body('body', 'Detay metni'),
                    ]),
                    Section::make('Görseller')->schema([
                        MediaUpload::make('cover_media_id')->label('Kapak görseli'),
                        MediaUpload::gallery()->label('Galeri'),
                    ]),
                ]),
                Grid::make(1)->columnSpan(['lg' => 1])->schema([
                    CommonFields::publishing(),
                    CommonFields::seo(),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('cover'))
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                CommonColumns::thumbnail(),
                TextColumn::make('title')->label('Hizmet')->searchable()
                    ->description(fn (Service $r) => str($r->excerpt)->limit(80)),
                CommonColumns::publicationState(),
                TextColumn::make('updated_at')->label('Güncelleme')->since()->sortable(),
            ])
            ->filters([
                CommonColumns::publicationFilter(),
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('view_on_site')->label('Sitede gör')->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->url(fn (Service $r) => route('services.show', $r), shouldOpenInNewTab: true)
                    ->visible(fn (Service $r) => $r->isPubliclyVisible()),
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
