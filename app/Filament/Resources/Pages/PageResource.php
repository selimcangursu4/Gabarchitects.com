<?php

namespace App\Filament\Resources\Pages;

use App\Enums\PublishStatus;
use App\Filament\Forms\CommonFields;
use App\Filament\Forms\MediaUpload;
use App\Models\Page;
use App\Models\Project;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

/**
 * Brief §7.3: ana sayfa ve statik sayfa içerikleri kod düzenlemeden değiştirilir.
 * Sayfalar sabit anahtarlıdır (home, about, contact, kvkk, privacy, cookies); oluşturulamaz/silinemez.
 */
class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    // Kullanıcı kararı (2026-11-08): sidebar'da gösterilmez. Sayfa adresle erişilebilir kalır.
    protected static bool $shouldRegisterNavigation = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|UnitEnum|null $navigationGroup = 'Site';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'sayfa';

    protected static ?string $pluralModelLabel = 'Sayfalar';

    protected static ?string $recordTitleAttribute = 'title';

    public const LEGAL_KEYS = ['kvkk', 'privacy', 'cookies'];

    public static function form(Schema $schema): Schema
    {
        $is = fn (string ...$keys) => fn (?Page $record) => in_array($record?->key, $keys, true);

        return $schema->components([
            // ---------------- Ana sayfa ----------------
            Tabs::make('home')->columnSpanFull()->visible($is('home'))->tabs([
                Tab::make('Hero')->schema([
                    TextInput::make('content.hero.eyebrow')->label('Üst etiket')->maxLength(120),
                    TextInput::make('content.hero.title')->label('Başlık')->required()->maxLength(120),
                    Textarea::make('content.hero.text')->label('Açıklama')->rows(2)->maxLength(300),
                    MediaUpload::make('cover_media_id')->label('Hero görseli')
                        ->helperText('Yatay, en az 2400 px genişlikte görsel önerilir.'),
                    TextInput::make('content.hero.image_caption')->label('Görsel açıklaması')->maxLength(120)
                        ->helperText('Görselin altında "Görsel: …" olarak gösterilir.'),
                ]),
                Tab::make('Tanıtım')->schema([
                    Textarea::make('content.statement')->label('Marka cümlesi')->rows(3),
                    TextInput::make('content.intro.title')->label('Hakkımızda başlığı'),
                    Textarea::make('content.intro.text')->label('Hakkımızda özeti')->rows(4),
                    TagsInput::make('content.intro.values')->label('Değerler')->reorderable(),
                    Textarea::make('content.services_intro')->label('Hizmetler giriş metni')->rows(3),
                ]),
                Tab::make('Mimari yaklaşım')->schema([
                    TextInput::make('content.approach.title')->label('Başlık'),
                    Textarea::make('content.approach.text')->label('Metin')->rows(3),
                    Textarea::make('content.approach.text_secondary')->label('İkinci paragraf')->rows(3),
                    Select::make('content.approach.project_id')->label('Görseli kullanılacak proje')->native(false)->searchable()
                        ->options(fn () => Project::orderBy('title')->pluck('title', 'id')),
                ]),
                Tab::make('Süreç')->schema([
                    TextInput::make('content.process.title')->label('Başlık'),
                    Textarea::make('content.process.text')->label('Açıklama')->rows(3),
                    Repeater::make('content.process.steps')->label('Adımlar')
                        ->simple(TextInput::make('step')->required()->maxLength(60))
                        ->reorderable()->addActionLabel('Adım ekle')->maxItems(8),
                ]),
                Tab::make('İletişim çağrısı')->schema([
                    TextInput::make('content.cta.title')->label('Başlık'),
                    Textarea::make('content.cta.text')->label('Metin')->rows(2),
                ]),
                Tab::make('SEO')->schema([CommonFields::seo()->collapsed(false)]),
            ]),

            // ---------------- Hakkımızda ----------------
            Section::make('Hakkımızda')->columnSpanFull()->visible($is('about'))->schema([
                TextInput::make('content.title')->label('Başlık')->required(),
                TextInput::make('content.subtitle')->label('Alt başlık'),
                Repeater::make('content.paragraphs')->label('Tanıtım paragrafları')
                    ->simple(Textarea::make('text')->rows(3)->required())
                    ->reorderable()->addActionLabel('Paragraf ekle'),
                Textarea::make('content.vision')->label('Vizyon')->rows(2),
                Textarea::make('content.mission')->label('Misyon')->rows(2)->helperText('Boş bırakılırsa sitede gösterilmez.'),
                Textarea::make('content.story')->label('Kuruluş hikâyesi')->rows(4)->helperText('Boş bırakılırsa sitede gösterilmez.'),
                TagsInput::make('content.values')->label('Değerler')->reorderable(),
            ]),
            Section::make()->columnSpanFull()->visible($is('about'))->schema([CommonFields::seo()]),

            // ---------------- İletişim ----------------
            Section::make('İletişim sayfası')->columnSpanFull()->visible($is('contact'))
                ->description('Adres, telefon, e-posta ve sosyal medya "Site Ayarları"ndan yönetilir.')
                ->schema([
                    Textarea::make('content.intro')->label('Giriş metni')->rows(2),
                    CommonFields::seo(),
                ]),

            // ---------------- Hukuki metinler ----------------
            Section::make('Hukuki metin')->columnSpanFull()->visible($is(...self::LEGAL_KEYS))
                ->description('Metin, şirketin hukuk danışmanı tarafından onaylanmış olmalıdır. Yayınlanana kadar sitede "hazırlanıyor" notu gösterilir.')
                ->schema([
                    TextInput::make('title')->label('Başlık')->required(),
                    Select::make('status')->label('Durum')->native(false)->required()
                        ->options(collect(PublishStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])),
                    CommonFields::body('body', 'Metin'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id')
            ->paginated(false)
            ->columns([
                TextColumn::make('title')->label('Sayfa'),
                TextColumn::make('url')->label('Adres')->state(fn (Page $r) => '/'.($r->slug ?? '')),
                TextColumn::make('status')->label('Durum')->badge()
                    ->formatStateUsing(fn (PublishStatus $state) => $state->label())
                    ->color(fn (PublishStatus $state) => $state === PublishStatus::Published ? 'success' : 'gray'),
                TextColumn::make('updated_at')->label('Güncelleme')->since(),
            ])
            ->recordActions([
                Action::make('view_on_site')->label('Sitede gör')->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->url(fn (Page $r) => url('/'.($r->slug ?? '')), shouldOpenInNewTab: true),
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPages::route('/'),
            'edit' => EditPage::route('/{record}/edit'),
        ];
    }
}
