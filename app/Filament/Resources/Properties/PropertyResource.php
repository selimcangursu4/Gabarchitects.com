<?php

namespace App\Filament\Resources\Properties;

use App\Enums\ListingStatus;
use App\Enums\ListingType;
use App\Filament\Concerns\UsesIdRecordUrls;
use App\Filament\Forms\CommonFields;
use App\Filament\Forms\MediaUpload;
use App\Filament\Tables\CommonColumns;
use App\Models\Property;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Arr;
use UnitEnum;

class PropertyResource extends Resource
{
    use UsesIdRecordUrls;

    protected static ?string $model = Property::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHomeModern;

    protected static string|UnitEnum|null $navigationGroup = 'Portföy';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'ilan';

    protected static ?string $pluralModelLabel = 'İlanlar';

    protected static ?string $recordTitleAttribute = 'title';

    // Sitede slug, panelde sabit ID (başlık değişse de düzenleme adresi değişmez)
    protected static ?string $recordRouteKeyName = 'id';

    public static function form(Schema $schema): Schema
    {
        $types = config('real_estate.property_types');

        return $schema->components([
            Grid::make(['default' => 1, 'lg' => 3])->columnSpanFull()->schema([
                Grid::make(1)->columnSpan(['lg' => 2])->schema([
                    Section::make('İlan bilgileri')->columns(2)->schema([
                        ...CommonFields::titleAndSlug(urlPrefix: 'portfoy'),
                        Select::make('listing_type')->label('İşlem tipi')->required()->native(false)
                            ->options(collect(ListingType::cases())->mapWithKeys(fn ($t) => [$t->value => $t->label()])),
                        Select::make('property_type')->label('Emlak tipi')->required()->native(false)->searchable()
                            ->options($types)->live(),
                        Select::make('listing_status')->label('İlan durumu')->required()->native(false)
                            ->options(collect(ListingStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()]))
                            ->default(ListingStatus::Available->value),
                        TextInput::make('rooms')->label('Oda sayısı')->placeholder('3+1')->maxLength(20)
                            ->rule('regex:/^\d{1,2}(\+\d{1,2})?$/')
                            ->hidden(fn (Get $get) => in_array($get('property_type'), config('real_estate.types_without_rooms'), true)),
                        TextInput::make('area_m2')->label('Brüt alan')->numeric()->minValue(1)->suffix('m²'),
                        TextInput::make('net_area_m2')->label('Net alan')->numeric()->minValue(1)->suffix('m²'),
                        TextInput::make('bathrooms')->label('Banyo')->numeric()->minValue(0)->maxValue(20),
                    ]),
                    Section::make('Konum')->columns(3)->schema([
                        TextInput::make('city')->label('İl')->required()->maxLength(80)
                            ->datalist(fn () => Property::query()->distinct()->orderBy('city')->pluck('city')->filter()->all()),
                        TextInput::make('district')->label('İlçe')->maxLength(80)
                            ->datalist(fn () => Property::query()->distinct()->orderBy('district')->pluck('district')->filter()->all()),
                        TextInput::make('location')->label('Semt / açık konum')->maxLength(255),
                    ]),
                    Section::make('Fiyat')->columns(3)->schema([
                        TextInput::make('price')->label('Fiyat')->numeric()->minValue(0)
                            ->helperText('Kiralıkta aylık bedel.'),
                        Select::make('currency')->label('Para birimi')->native(false)->default('TRY')
                            ->options(collect(config('real_estate.currencies'))->mapWithKeys(fn ($s, $c) => [$c => "{$c} ({$s})"])),
                        Toggle::make('show_price')->label('Fiyatı sitede göster')->default(true)->inline(false),
                    ]),
                    Section::make('Açıklama')->schema([
                        Textarea::make('summary')->label('Kısa açıklama')->rows(3)->maxLength(500),
                        CommonFields::body(),
                    ]),
                    Section::make('Görseller')->schema([
                        MediaUpload::make('cover_media_id')->label('Kapak görseli'),
                        MediaUpload::gallery()->label('Galeri')->helperText('Sürükleyerek sıralayabilirsiniz.'),
                    ]),
                ]),
                Grid::make(1)->columnSpan(['lg' => 1])->schema([
                    CommonFields::publishing([
                        Toggle::make('is_featured')->label('Ana sayfada öne çıkar')
                            ->helperText('Öne çıkan olarak işaretlenen son 9 ilan ana sayfada listelenir.'),
                        Toggle::make('is_sample')->label('Örnek ilan')
                            ->helperText('Tasarım için üretilmiş örnek. Gerçek ilan girildiğinde silinmelidir.')
                            ->disabled()->dehydrated(false)
                            ->visible(fn (?Property $record) => (bool) $record?->is_sample),
                    ]),
                    CommonFields::seo(),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('cover'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                CommonColumns::thumbnail(),
                TextColumn::make('title')->label('Başlık')->searchable()->limit(45)->wrap()
                    ->description(fn (Property $r) => $r->locationLabel()),
                TextColumn::make('listing_type')->label('İşlem')->badge()
                    ->formatStateUsing(fn (ListingType $state) => $state->label())
                    ->color(fn (ListingType $state) => match ($state) {
                        ListingType::Sale => 'primary',
                        ListingType::Rent => 'info',
                        ListingType::Exchange => 'warning',
                    }),
                TextColumn::make('property_type')->label('Tip')
                    ->formatStateUsing(fn (string $state) => Arr::collapse(config('real_estate.property_types'))[$state] ?? $state),
                TextColumn::make('price')->label('Fiyat')->sortable()
                    ->state(fn (Property $r) => $r->formattedPrice() ?? '—'),
                CommonColumns::publicationState(),
                ToggleColumn::make('is_featured')->label('Öne çıkan')
                    ->disabled(fn () => ! auth()->user()->can('properties.manage')),
                TextColumn::make('created_at')->label('Eklenme')->date('d.m.Y')->sortable(),
            ])
            ->filters([
                SelectFilter::make('listing_type')->label('İşlem')
                    ->options(collect(ListingType::cases())->mapWithKeys(fn ($t) => [$t->value => $t->label()])),
                SelectFilter::make('property_type')->label('Emlak tipi')->options(config('real_estate.property_types')),
                SelectFilter::make('city')->label('İl')
                    ->options(fn () => Property::query()->distinct()->orderBy('city')->pluck('city', 'city')->filter()->all()),
                CommonColumns::publicationFilter(),
                TernaryFilter::make('is_featured')->label('Öne çıkan'),
                TernaryFilter::make('is_sample')->label('Örnek ilan'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('view_on_site')->label('Sitede gör')->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->url(fn (Property $r) => $r->url(), shouldOpenInNewTab: true)
                    ->visible(fn (Property $r) => $r->isPubliclyVisible()),
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProperties::route('/'),
            'create' => Pages\CreateProperty::route('/create'),
            'edit' => Pages\EditProperty::route('/{record}/edit'),
        ];
    }
}
