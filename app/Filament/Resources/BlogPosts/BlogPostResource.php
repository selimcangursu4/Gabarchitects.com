<?php

namespace App\Filament\Resources\BlogPosts;

use App\Filament\Concerns\UsesIdRecordUrls;
use App\Filament\Forms\CommonFields;
use App\Filament\Forms\MediaUpload;
use App\Filament\Tables\CommonColumns;
use App\Models\BlogPost;
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
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class BlogPostResource extends Resource
{
    use UsesIdRecordUrls;

    protected static ?string $model = BlogPost::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;

    protected static string|UnitEnum|null $navigationGroup = 'İçerik';

    protected static ?int $navigationSort = 5;

    protected static ?string $modelLabel = 'blog yazısı';

    protected static ?string $pluralModelLabel = 'Blog Yazıları';

    protected static ?string $recordTitleAttribute = 'title';

    // Sitede slug, panelde sabit ID (başlık değişse de düzenleme adresi değişmez)
    protected static ?string $recordRouteKeyName = 'id';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(['default' => 1, 'lg' => 3])->columnSpanFull()->schema([
                Grid::make(1)->columnSpan(['lg' => 2])->schema([
                    Section::make('Yazı')->columns(2)->schema([
                        ...CommonFields::titleAndSlug(urlPrefix: 'blog'),
                        Textarea::make('excerpt')->label('Özet')->rows(3)->maxLength(500)->columnSpanFull(),
                        CommonFields::body(),
                    ]),
                    Section::make('Kapak')->schema([MediaUpload::make('cover_media_id')->label('Kapak görseli')]),
                ]),
                Grid::make(1)->columnSpan(['lg' => 1])->schema([
                    CommonFields::publishing([
                        Select::make('blog_category_id')->label('Kategori')->relationship('category', 'name')
                            ->searchable()->preload()->native(false)
                            ->createOptionForm([TextInput::make('name')->label('Kategori adı')->required()]),
                        Select::make('author_id')->label('Yazar')->relationship('author', 'name')
                            ->default(fn () => auth()->id())->native(false),
                        Toggle::make('is_featured')->label('Öne çıkan yazı'),
                    ]),
                    CommonFields::seo(),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['cover', 'category', 'author']))
            ->defaultSort('published_at', 'desc')
            ->columns([
                CommonColumns::thumbnail(),
                TextColumn::make('title')->label('Başlık')->searchable()->wrap(),
                TextColumn::make('category.name')->label('Kategori')->placeholder('—'),
                TextColumn::make('author.name')->label('Yazar')->placeholder('—'),
                CommonColumns::publicationState(),
                TextColumn::make('published_at')->label('Yayın')->dateTime('d.m.Y H:i')->sortable()->placeholder('—'),
            ])
            ->filters([
                CommonColumns::publicationFilter(),
                SelectFilter::make('blog_category_id')->label('Kategori')->relationship('category', 'name'),
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
            'index' => Pages\ListBlogPosts::route('/'),
            'create' => Pages\CreateBlogPost::route('/create'),
            'edit' => Pages\EditBlogPost::route('/{record}/edit'),
        ];
    }
}
