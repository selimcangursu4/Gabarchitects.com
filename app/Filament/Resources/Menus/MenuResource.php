<?php

namespace App\Filament\Resources\Menus;

use App\Models\Menu;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

/**
 * Brief §7.6: header ve footer/yasal menüleri; ekleme, silme, sıralama, alt menü.
 */
class MenuResource extends Resource
{
    protected static ?string $model = Menu::class;

    // Kullanıcı kararı (2026-11-08): sidebar'da gösterilmez. Sayfa adresle erişilebilir kalır.
    protected static bool $shouldRegisterNavigation = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBars3;

    protected static string|UnitEnum|null $navigationGroup = 'Site';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'menü';

    protected static ?string $pluralModelLabel = 'Menüler';

    protected static ?string $recordTitleAttribute = 'name';

    /** Site içi yol (/projeler) ya da tam adres; boş = açılır menü başlığı */
    private static function urlField(bool $nullable = true): TextInput
    {
        return TextInput::make('url')->label('Bağlantı')
            ->placeholder('/projeler')
            ->maxLength(255)
            ->required(! $nullable)
            ->rule('regex:/^(\/[^\s]*|https?:\/\/[^\s]+|mailto:[^\s]+|tel:[^\s]+)$/')
            ->validationMessages(['regex' => '"/" ile başlayan bir site içi yol ya da http(s)://, mailto:, tel: adresi girin.']);
    }

    private static function itemFields(bool $urlNullable): array
    {
        return [
            Grid::make(['default' => 1, 'md' => 4])->schema([
                TextInput::make('label')->label('Etiket')->required()->maxLength(60),
                static::urlField($urlNullable)->columnSpan(['md' => 2])
                    ->helperText($urlNullable ? 'Boş bırakılırsa alt menüyü açan bir başlık olur.' : null),
                Grid::make(1)->schema([
                    Toggle::make('is_active')->label('Aktif')->default(true),
                    Toggle::make('open_in_new_tab')->label('Yeni sekmede aç'),
                ]),
            ]),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columnSpanFull()->schema([
                TextInput::make('name')->label('Menü adı')->required()->maxLength(60),
                Repeater::make('rootItems')->label('Öğeler')
                    ->relationship('rootItems')
                    ->orderColumn('sort_order')
                    ->reorderable()
                    ->collapsible()
                    ->itemLabel(fn (array $state) => ($state['label'] ?? '').(blank($state['url'] ?? null) ? ' ▾' : ''))
                    ->addActionLabel('Menü öğesi ekle')
                    ->schema([
                        ...static::itemFields(urlNullable: true),
                        Repeater::make('children')->label('Alt menü')
                            ->relationship('children')
                            ->orderColumn('sort_order')
                            ->reorderable()
                            ->collapsible()
                            ->collapsed()
                            ->itemLabel(fn (array $state) => $state['label'] ?? null)
                            ->addActionLabel('Alt öğe ekle')
                            ->defaultItems(0)
                            // Alt öğeler de aynı menüye bağlanır
                            ->mutateRelationshipDataBeforeCreateUsing(function (array $data, $livewire): array {
                                $data['menu_id'] = $livewire->getRecord()->getKey();

                                return $data;
                            })
                            ->schema(static::itemFields(urlNullable: false)),
                    ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->columns([
                TextColumn::make('name')->label('Menü'),
                TextColumn::make('key')->label('Konum')->badge()->color('gray')
                    ->formatStateUsing(fn (string $state) => ['header' => 'Üst menü (header)', 'legal' => 'Alt menü (yasal)'][$state] ?? $state),
                TextColumn::make('items_count')->label('Öğe')->counts('items'),
                TextColumn::make('updated_at')->label('Güncelleme')->since(),
            ])
            ->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMenus::route('/'),
            'edit' => Pages\EditMenu::route('/{record}/edit'),
        ];
    }
}
