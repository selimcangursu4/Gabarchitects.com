<?php

namespace App\Filament\Forms;

use App\Enums\PublishStatus;
use App\Support\Slug;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

/**
 * Tekrarlanan form parçaları: başlık + slug, yayın durumu, SEO, zengin metin.
 */
class CommonFields
{
    /** Başlık yazıldıkça (slug elle değiştirilmediyse) Türkçe slug üretir. */
    public static function titleAndSlug(string $title = 'title', string $label = 'Başlık', ?string $urlPrefix = null): array
    {
        return [
            TextInput::make($title)
                ->label($label)
                ->required()
                ->maxLength(255)
                ->live(onBlur: true)
                ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state) {
                    // Slug boşsa ya da önceki başlıktan üretilmişse güncelle (elle yazılmış slug korunur)
                    if (blank($get('slug')) || $get('slug') === static::slug($old)) {
                        $set('slug', static::slug($state));
                    }
                }),
            TextInput::make('slug')
                ->label('Adres (slug)')
                ->helperText('Boş bırakılırsa başlıktan otomatik üretilir. Yalnızca küçük harf, rakam ve tire.')
                ->prefix($urlPrefix ? url($urlPrefix).'/' : null)
                ->maxLength(255)
                ->rule('regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
                ->unique(ignoreRecord: true),
        ];
    }

    /** @param  array<int, mixed>  $extra  bölümün sonuna eklenecek alanlar (ör. "öne çıkar") */
    public static function publishing(array $extra = []): Section
    {
        return Section::make('Yayın')
            ->schema([
                Select::make('status')
                    ->label('Durum')
                    ->options(collect(PublishStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()]))
                    ->default(PublishStatus::Draft->value)
                    ->required()
                    ->native(false),
                DateTimePicker::make('published_at')
                    ->label('Yayın tarihi')
                    ->helperText('Boşsa kaydederken "şimdi" atanır. İleri bir tarih seçilirse içerik o tarihte otomatik yayına girer (zamanlanmış yayın).')
                    ->seconds(false)
                    ->displayFormat('d.m.Y H:i')
                    ->native(false),
                ...$extra,
            ]);
    }

    public static function seo(): Section
    {
        return Section::make('SEO')
            ->description('Boş bırakılırsa başlık ve özet kullanılır.')
            ->collapsible()
            ->collapsed()
            ->schema([
                TextInput::make('meta_title')->label('Meta başlık')->maxLength(70)
                    ->helperText('Önerilen: en fazla 60 karakter.'),
                Textarea::make('meta_description')->label('Meta açıklama')->rows(3)->maxLength(320)
                    ->helperText('Önerilen: 120–160 karakter.'),
            ]);
    }

    /** Zengin metin. Kaydederken model tarafında temizlenir (SanitizesHtml). */
    public static function body(string $name = 'body', string $label = 'İçerik'): RichEditor
    {
        return RichEditor::make($name)
            ->label($label)
            ->toolbarButtons([
                ['bold', 'italic', 'link'],
                ['h2', 'h3', 'blockquote'],
                ['bulletList', 'orderedList'],
                ['undo', 'redo'],
            ])
            ->columnSpanFull();
    }

    private static function slug(?string $value): string
    {
        return Slug::make($value);
    }
}
