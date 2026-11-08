<?php

namespace App\Filament\Resources\ContactMessages;

use App\Enums\ContactStatus;
use App\Models\ContactMessage;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

/**
 * Talep 9: "Bir proje konuşalım", İletişim ve ilan bilgi talebi formları.
 */
class ContactMessageResource extends Resource
{
    protected static ?string $model = ContactMessage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxArrowDown;

    protected static string|UnitEnum|null $navigationGroup = 'İletişim';

    protected static ?string $navigationLabel = 'Web Formları';

    protected static ?string $modelLabel = 'web formu';

    protected static ?string $pluralModelLabel = 'Web Formları';

    protected static ?string $recordTitleAttribute = 'name';

    public const SOURCES = [
        'home_cta' => 'Ana sayfa · Bir proje konuşalım',
        'contact_page' => 'İletişim sayfası',
        'property' => 'İlan bilgi talebi',
    ];

    /** Menüde okunmamış mesaj sayısı */
    public static function getNavigationBadge(): ?string
    {
        $count = ContactMessage::unread()->count();

        return $count ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Okunmamış mesaj';
    }

    public static function statusOptions(): array
    {
        return collect(ContactStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])->all();
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(['default' => 1, 'lg' => 3])->columnSpanFull()->schema([
                Section::make('Mesaj')->columnSpan(['lg' => 2])->schema([
                    TextEntry::make('subject')->label('Konu')->placeholder('—'),
                    TextEntry::make('message')->label('Mesaj')->prose()->columnSpanFull()
                        ->formatStateUsing(fn (string $state) => nl2br(e($state)))->html(),
                    TextEntry::make('internal_notes')->label('İç notlar')->placeholder('Not yok')->columnSpanFull()
                        ->formatStateUsing(fn (?string $state) => nl2br(e((string) $state)))->html(),
                ]),
                Section::make('Gönderen')->columnSpan(['lg' => 1])->schema([
                    TextEntry::make('name')->label('Ad Soyad'),
                    TextEntry::make('email')->label('E-posta')->copyable()
                        ->url(fn (ContactMessage $r) => 'mailto:'.$r->email),
                    TextEntry::make('phone')->label('Telefon')->placeholder('—')->copyable()
                        ->url(fn (ContactMessage $r) => $r->phone ? 'tel:'.preg_replace('/[^\d+]/', '', $r->phone) : null),
                    TextEntry::make('status')->label('Durum')->badge()
                        ->formatStateUsing(fn (ContactStatus $state) => $state->label())
                        ->color(fn (ContactStatus $state) => static::statusColor($state)),
                    TextEntry::make('source')->label('Kaynak')->formatStateUsing(fn (string $state) => static::SOURCES[$state] ?? $state),
                    TextEntry::make('property.title')->label('İlgili ilan')->placeholder('—')
                        ->url(fn (ContactMessage $r) => $r->property?->url(), shouldOpenInNewTab: true),
                    TextEntry::make('created_at')->label('Tarih')->dateTime('d.m.Y H:i'),
                    TextEntry::make('consented_at')->label('KVKK onayı')->dateTime('d.m.Y H:i'),
                    TextEntry::make('handler.name')->label('İlgilenen')->placeholder('—'),
                    TextEntry::make('ip_address')->label('IP')->placeholder('—'),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('property'))
            ->defaultSort('created_at', 'desc')
            ->recordClasses(fn (ContactMessage $r) => $r->read_at ? null : 'font-semibold')
            ->columns([
                TextColumn::make('name')->label('Ad Soyad')->searchable()
                    ->description(fn (ContactMessage $r) => $r->email),
                TextColumn::make('subject')->label('Konu')->placeholder('—')->limit(40)
                    ->description(fn (ContactMessage $r) => str($r->message)->limit(70)),
                TextColumn::make('source')->label('Kaynak')->badge()->color('gray')
                    ->formatStateUsing(fn (string $state) => static::SOURCES[$state] ?? $state),
                TextColumn::make('status')->label('Durum')->badge()
                    ->formatStateUsing(fn (ContactStatus $state) => $state->label())
                    ->color(fn (ContactStatus $state) => static::statusColor($state)),
                TextColumn::make('created_at')->label('Tarih')->dateTime('d.m.Y H:i')->sortable()
                    ->description(fn (ContactMessage $r) => $r->created_at->diffForHumans()),
            ])
            ->filters([
                SelectFilter::make('status')->label('Durum')->options(static::statusOptions()),
                SelectFilter::make('source')->label('Kaynak')->options(static::SOURCES),
                TernaryFilter::make('read_at')->label('Okundu')->nullable(),
                TrashedFilter::make(),
            ])
            ->recordActions([ViewAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('mark_read')->label('Okundu işaretle')->icon(Heroicon::OutlinedEnvelopeOpen)
                        ->action(fn (Collection $records) => $records->each(fn (ContactMessage $r) => $r->read_at || $r->update(['read_at' => now()])))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('archive')->label('Arşivle')->icon(Heroicon::OutlinedArchiveBox)
                        ->action(fn (Collection $records) => $records->each->update(['status' => ContactStatus::Archived]))
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /** Detay sayfası başlık işlemleri */
    public static function statusActions(): array
    {
        return [
            Action::make('reply')->label('E-postayla yanıtla')->icon(Heroicon::OutlinedPaperAirplane)
                ->url(fn (ContactMessage $record) => 'mailto:'.$record->email.'?subject='.rawurlencode('Re: '.($record->subject ?: (settings('site.brand') ?: config('gab.brand'))))),
            Action::make('update_status')->label('Durumu güncelle')->icon(Heroicon::OutlinedPencilSquare)
                ->fillForm(fn (ContactMessage $record) => ['status' => $record->status->value, 'internal_notes' => $record->internal_notes])
                ->schema([
                    Select::make('status')->label('Durum')->options(static::statusOptions())->required()->native(false),
                    Textarea::make('internal_notes')->label('İç notlar')->rows(5)
                        ->helperText('Yalnızca panel kullanıcıları görür.'),
                ])
                ->action(function (ContactMessage $record, array $data) {
                    $record->update([...$data, 'handled_by' => auth()->id()]);
                })
                ->successNotificationTitle('Güncellendi'),
        ];
    }

    public static function statusColor(ContactStatus $status): string
    {
        return match ($status) {
            ContactStatus::New => 'danger',
            ContactStatus::InReview => 'warning',
            ContactStatus::Answered => 'success',
            ContactStatus::Archived => 'gray',
        };
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContactMessages::route('/'),
            'view' => Pages\ViewContactMessage::route('/{record}'),
        ];
    }
}
