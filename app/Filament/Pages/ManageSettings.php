<?php

namespace App\Filament\Pages;

use App\Models\ActivityLog;
use App\Models\SiteSetting;
use App\Services\SettingService;
use App\Support\Phone;
use BackedEnum;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;
use UnitEnum;

/**
 * Site ayarları (site_settings). Yalnızca 'settings.manage' izni olanlar (Admin, Super Admin).
 *
 * @property-read Schema $form
 */
class ManageSettings extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'Site';

    protected static ?int $navigationSort = 6;

    protected static ?string $navigationLabel = 'Site Ayarları';

    protected static ?string $title = 'Site Ayarları';

    protected static ?string $slug = 'settings';

    protected string $view = 'filament.pages.manage-settings';

    /** Formdaki alan → site_settings anahtarı ve grubu */
    private const KEYS = [
        'site.brand' => 'general',
        'site.tagline' => 'general',
        'site.description' => 'seo',
        'contact.address' => 'contact',
        'contact.address_short' => 'contact',
        'contact.phone' => 'contact',
        'contact.email' => 'contact',
        'contact.whatsapp' => 'contact',
        'contact.map_url' => 'contact',
        'contact.notification_email' => 'contact',
        'social.links' => 'social',
        'modules.real_estate' => 'modules',
        'legal.cookie_banner_text' => 'legal',
    ];

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->can('settings.manage');
    }

    public function mount(): void
    {
        $values = [];
        foreach (array_keys(self::KEYS) as $key) {
            data_set($values, $key, settings($key));
        }

        $this->form->fill($values);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Genel')->columns(2)->schema([
                    TextInput::make('site.brand')->label('Marka adı')->required()->maxLength(80),
                    TextInput::make('site.tagline')->label('Slogan')->maxLength(120),
                    Textarea::make('site.description')->label('Varsayılan meta açıklama')->rows(2)->maxLength(320)->columnSpanFull()
                        ->helperText('Kendi açıklaması olmayan sayfalarda arama motorlarına gösterilir.'),
                ]),
                Section::make('İletişim bilgileri')->columns(2)->schema([
                    Textarea::make('contact.address')->label('Adres')->rows(3)->required()->columnSpanFull(),
                    TextInput::make('contact.address_short')->label('Kısa adres')->maxLength(120),
                    TextInput::make('contact.map_url')->label('Google Haritalar bağlantısı')->url()->maxLength(500),
                    TextInput::make('contact.phone')->label('Telefon')->tel()->maxLength(40)
                        ->helperText('İlan sayfalarındaki "Hemen arayın" butonu bu numarayı arar. Boşsa buton soluk ve tıklanamaz görünür.')
                        ->rule(fn () => function (string $attribute, $value, $fail) {
                            if (filled($value) && ! Phone::international($value)) {
                                $fail('Geçerli bir telefon numarası girin (ör. 0212 555 00 00).');
                            }
                        }),
                    TextInput::make('contact.whatsapp')->label('WhatsApp numarası')->tel()->maxLength(40)
                        ->placeholder('0532 000 00 00')
                        ->helperText('İlan sayfalarındaki "WhatsApp\'tan yazın" butonu bu numaraya, ilan bilgisiyle hazır bir mesaj açar. Boşsa buton soluk ve tıklanamaz görünür.')
                        ->rule(fn () => function (string $attribute, $value, $fail) {
                            if (filled($value) && ! Phone::international($value)) {
                                $fail('Geçerli bir cep telefonu numarası girin (ör. 0532 000 00 00).');
                            }
                        }),
                    TextInput::make('contact.email')->label('E-posta')->email()->maxLength(190)
                        ->helperText('Boş bırakılırsa sitede gösterilmez.'),
                    TextInput::make('contact.notification_email')->label('Web formu bildirim adresi')->email()->maxLength(190)
                        ->helperText('Formlar bu adrese e-postayla bildirilir. Boşsa sistem gönderen adresi kullanılır.')
                        ->columnSpanFull(),
                ]),
                Section::make('Sosyal medya')->schema([
                    Repeater::make('social.links')->hiddenLabel()
                        ->schema([
                            TextInput::make('label')->label('Ad')->required()->maxLength(40),
                            TextInput::make('url')->label('Adres')->url()->required()->maxLength(255),
                        ])
                        ->columns(2)->reorderable()->addActionLabel('Bağlantı ekle')->defaultItems(0),
                ]),
                Section::make('Modüller')->schema([
                    Toggle::make('modules.real_estate')->label('Gayrimenkul portföyü')
                        ->helperText('Kapatılırsa ana sayfadaki filtre ve Portföyümüz bölümü gizlenir; Satılık/Kiralık sayfaları 404 döner.'),
                ]),
                Section::make('Çerez bildirimi')->collapsed()->schema([
                    Textarea::make('legal.cookie_banner_text')->label('Çerez bandı metni')->rows(3)->maxLength(500)
                        ->helperText('Hukuk danışmanınızın onayladığı metni girin. Boşsa: "yalnızca zorunlu çerezler kullanılır" bilgisi gösterilir. Metin değişince bant ziyaretçilere yeniden gösterilir. Siteye analiz/reklam aracı eklenirse bu bant onay (kabul/ret) bandına dönüştürülmelidir.'),
                ]),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $settings = app(SettingService::class);
        $changed = [];

        foreach (self::KEYS as $key => $group) {
            $new = data_get($data, $key);
            $old = settings($key);

            if ($new != $old) {
                $settings->set($key, $new, $group);
                $changed[$key] = ['old' => $old, 'new' => $new];
            }
        }

        if ($changed) {
            ActivityLog::create([
                'user_id' => auth()->id(),
                'event' => 'updated',
                'subject_type' => (new SiteSetting)->getMorphClass(),
                'description' => 'Site ayarları: '.implode(', ', array_keys($changed)),
                'properties' => [
                    'old' => array_map(fn ($c) => is_array($c['old']) ? json_encode($c['old'], JSON_UNESCAPED_UNICODE) : $c['old'], $changed),
                    'new' => array_map(fn ($c) => is_array($c['new']) ? json_encode($c['new'], JSON_UNESCAPED_UNICODE) : $c['new'], $changed),
                ],
                'ip_address' => request()->ip(),
                'user_agent' => Str::limit((string) request()->userAgent(), 490, ''),
                'created_at' => now(),
            ]);
        }

        Notification::make()->title($changed ? 'Ayarlar kaydedildi' : 'Değişiklik yok')->success()->send();
    }
}
