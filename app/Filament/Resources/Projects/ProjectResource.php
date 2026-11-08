<?php

namespace App\Filament\Resources\Projects;

use App\Enums\ProjectProgress;
use App\Filament\Concerns\UsesIdRecordUrls;
use App\Filament\Forms\CommonFields;
use App\Filament\Forms\MediaUpload;
use App\Filament\Tables\CommonColumns;
use App\Models\Project;
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
use UnitEnum;

class ProjectResource extends Resource
{
    use UsesIdRecordUrls;

    protected static ?string $model = Project::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static string|UnitEnum|null $navigationGroup = 'İçerik';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'proje';

    protected static ?string $pluralModelLabel = 'Projeler';

    protected static ?string $recordTitleAttribute = 'title';

    // Sitede slug, panelde sabit ID (başlık değişse de düzenleme adresi değişmez)
    protected static ?string $recordRouteKeyName = 'id';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(['default' => 1, 'lg' => 3])->columnSpanFull()->schema([
                Grid::make(1)->columnSpan(['lg' => 2])->schema([
                    Section::make('Proje')->columns(2)->schema([
                        ...CommonFields::titleAndSlug(urlPrefix: 'projeler'),
                        TextInput::make('subtitle')->label('Alt başlık')->maxLength(255)->columnSpanFull(),
                        Select::make('project_category_id')->label('Kategori')->relationship('category', 'name')
                            ->searchable()->preload()->native(false)
                            ->createOptionForm([TextInput::make('name')->label('Kategori adı')->required()]),
                        Select::make('progress')->label('Proje durumu')->native(false)
                            ->options(collect(ProjectProgress::cases())->mapWithKeys(fn ($p) => [$p->value => $p->label()]))
                            ->helperText('"Devam eden" projeler Projeler sayfasında ayrı bölümde listelenir.'),
                        TextInput::make('location')->label('Lokasyon')->maxLength(255),
                        TextInput::make('client')->label('İşveren / müşteri')->maxLength(255),
                        TextInput::make('year')->label('Yıl')->numeric()->minValue(1950)->maxValue((int) date('Y') + 10),
                        TextInput::make('area_m2')->label('Alan')->numeric()->minValue(1)->suffix('m²'),
                        Select::make('services')->label('İlgili hizmetler')->relationship('services', 'title')
                            ->multiple()->preload()->native(false)->columnSpanFull(),
                    ]),
                    Section::make('Açıklama')->schema([
                        Textarea::make('summary')->label('Özet')->rows(3)->maxLength(600),
                        CommonFields::body(),
                    ]),
                    Section::make('Görseller')->schema([
                        MediaUpload::make('cover_media_id')->label('Kapak görseli'),
                        MediaUpload::gallery()->label('Mimari galeri')->helperText('Sürükleyerek sıralayabilirsiniz. Sitede bu sırayla gösterilir.'),
                    ]),
                ]),
                Grid::make(1)->columnSpan(['lg' => 1])->schema([
                    CommonFields::publishing([
                        Toggle::make('is_featured')->label('Ana sayfada öne çıkar')
                            ->helperText('Öne çıkan olarak işaretlenen son eklenen 4 proje ana sayfada listelenir.'),
                        TextInput::make('sort_order')->label('Sıra')->numeric()->default(0)
                            ->helperText('Projeler sayfasındaki sıralama (küçük önce).'),
                    ]),
                    CommonFields::seo(),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['cover', 'category']))
            ->defaultSort('created_at', 'desc')
            ->columns([
                CommonColumns::thumbnail(),
                TextColumn::make('title')->label('Proje')->searchable()->wrap()
                    ->description(fn (Project $r) => $r->location),
                TextColumn::make('category.name')->label('Kategori')->placeholder('—'),
                TextColumn::make('progress')->label('Durum')->badge()->placeholder('—')
                    ->formatStateUsing(fn (ProjectProgress $state) => $state->label()),
                CommonColumns::publicationState(),
                ToggleColumn::make('is_featured')->label('Öne çıkan')
                    ->disabled(fn () => ! auth()->user()->can('projects.manage')),
                TextColumn::make('sort_order')->label('Sıra')->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->label('Eklenme')->date('d.m.Y')->sortable(),
            ])
            ->filters([
                SelectFilter::make('project_category_id')->label('Kategori')->relationship('category', 'name'),
                SelectFilter::make('progress')->label('Proje durumu')
                    ->options(collect(ProjectProgress::cases())->mapWithKeys(fn ($p) => [$p->value => $p->label()])),
                CommonColumns::publicationFilter(),
                TernaryFilter::make('is_featured')->label('Öne çıkan'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('view_on_site')->label('Sitede gör')->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->url(fn (Project $r) => $r->url(), shouldOpenInNewTab: true)
                    ->visible(fn (Project $r) => $r->isPubliclyVisible()),
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make(), RestoreBulkAction::make()]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }
}
