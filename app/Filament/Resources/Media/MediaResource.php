<?php

namespace App\Filament\Resources\Media;

use App\Models\Media;
use App\Services\MediaService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;
use UnitEnum;

/**
 * Brief §7.5: merkezi medya kütüphanesi. Orijinaller korunur; WebP/AVIF türevleri otomatik.
 */
class MediaResource extends Resource
{
    protected static ?string $model = Media::class;

    // Kullanıcı kararı (2026-11-08): sidebar'da gösterilmez. Sayfa adresle erişilebilir kalır.
    protected static bool $shouldRegisterNavigation = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static string|UnitEnum|null $navigationGroup = 'Site';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Medya Kütüphanesi';

    protected static ?string $modelLabel = 'görsel';

    protected static ?string $pluralModelLabel = 'Medya Kütüphanesi';

    protected static ?string $recordTitleAttribute = 'original_name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(['default' => 1, 'lg' => 3])->columnSpanFull()->schema([
                Section::make()->columnSpan(['lg' => 2])->schema([
                    ImageEntry::make('preview')->hiddenLabel()
                        ->state(fn (Media $record) => $record->absoluteUrl(1600))
                        ->imageHeight(420)->extraImgAttributes(['class' => 'object-contain']),
                ]),
                Section::make('Bilgiler')->columnSpan(['lg' => 1])->schema([
                    TextInput::make('alt')->label('Alternatif metin (alt)')->maxLength(255)
                        ->helperText('Görme engelli ziyaretçiler ve SEO için görseli kısaca tarif edin.'),
                    TextInput::make('title')->label('Başlık')->maxLength(255),
                    TextEntry::make('original_name')->label('Dosya adı'),
                    TextEntry::make('dimensions')->label('Boyut')->state(fn (Media $r) => "{$r->width} × {$r->height} px"),
                    TextEntry::make('size')->label('Orijinal dosya')->formatStateUsing(fn (int $state) => Number::fileSize($state, 1)),
                    TextEntry::make('formats')->label('Türevler')
                        ->state(fn (Media $r) => collect($r->variants ?? [])->map(fn ($w, $f) => strtoupper($f).': '.implode(', ', $w))->implode(' · ')),
                    TextEntry::make('usage')->label('Kullanım')->state(fn (Media $r) => $r->usageCount().' yerde kullanılıyor'),
                    TextEntry::make('created_at')->label('Yüklenme')->dateTime('d.m.Y H:i'),
                    TextEntry::make('uploader.name')->label('Yükleyen')->placeholder('Sistem'),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->latest())
            ->contentGrid(['md' => 3, 'xl' => 5])
            ->paginated([20, 40, 80])
            ->columns([
                Stack::make([
                    ImageColumn::make('thumb')->state(fn (Media $r) => $r->absoluteUrl(480))
                        ->imageHeight(150)->imageWidth('100%')->extraImgAttributes(['class' => 'object-cover w-full', 'loading' => 'lazy']),
                    TextColumn::make('original_name')->searchable(['original_name', 'alt', 'title'])->limit(32)->weight('medium'),
                    TextColumn::make('meta')->size('xs')->color('gray')
                        ->state(fn (Media $r) => "{$r->width}×{$r->height} · ".Number::fileSize($r->size, 1)),
                    TextColumn::make('alt')->size('xs')->color(fn (?string $state) => $state ? 'gray' : 'warning')
                        ->default('Alt metin yok')->limit(40),
                ])->space(2),
            ])
            ->filters([
                Filter::make('missing_alt')->label('Alt metni olmayanlar')
                    ->query(fn (Builder $query) => $query->where(fn ($q) => $q->whereNull('alt')->orWhere('alt', ''))),
            ])
            ->recordActions([
                EditAction::make()->label('Detay'),
                static::deleteAction(),
            ]);
    }

    /** Yalnızca kullanılmayan görseller silinir; dosyalar (orijinal + türevler) kalıcı olarak kaldırılır. */
    public static function deleteAction(): Action
    {
        return Action::make('purge')->label('Sil')->icon(Heroicon::OutlinedTrash)->color('danger')
            ->requiresConfirmation()
            ->modalDescription(fn (Media $record) => $record->usageCount()
                ? 'Bu görsel '.$record->usageCount().' yerde kullanılıyor; önce o kayıtlardan kaldırın.'
                : 'Görsel ve tüm boyutları kalıcı olarak silinecek.')
            ->authorize(fn (Media $record) => auth()->user()->can('delete', $record))
            ->action(function (Media $record) {
                app(MediaService::class)->purge($record);
                Notification::make()->title('Görsel silindi')->success()->send();
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMedia::route('/'),
            'edit' => Pages\EditMedia::route('/{record}/edit'),
        ];
    }
}
