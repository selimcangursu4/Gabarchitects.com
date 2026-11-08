<?php

namespace App\Filament\Resources\Users;

use App\Enums\Role;
use App\Models\User;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Validation\Rules\Password;
use UnitEnum;

/** Brief §7.8: Super Admin / Admin / Editor; her kullanıcıya ayrı hesap. */
class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string|UnitEnum|null $navigationGroup = 'Yönetim';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'kullanıcı';

    protected static ?string $pluralModelLabel = 'Kullanıcılar';

    protected static ?string $recordTitleAttribute = 'name';

    public static function roleOptions(): array
    {
        return collect(Role::cases())->mapWithKeys(fn (Role $r) => [$r->value => $r->label()])->all();
    }

    public static function form(Schema $schema): Schema
    {
        $isSelf = fn (?User $record) => $record?->is(auth()->user());

        return $schema->components([
            Section::make()->columns(2)->columnSpanFull()->schema([
                TextInput::make('name')->label('Ad Soyad')->required()->maxLength(120),
                TextInput::make('email')->label('E-posta')->email()->required()->maxLength(190)
                    ->unique(ignoreRecord: true),
                Select::make('role')->label('Rol')->required()->native(false)
                    ->options(static::roleOptions())
                    ->helperText('Editor: içerik (proje, ilan, blog, hizmet, referans, medya). Admin: kullanıcı yönetimi hariç her şey. Super Admin: tümü.')
                    ->afterStateHydrated(fn (Select $component, ?User $record) => $component->state($record?->roles->first()?->name))
                    ->disabled($isSelf)   // kişi kendi rolünü değiştiremez (kilitlenmeyi önler)
                    ->dehydrated(false)
                    ->saveRelationshipsUsing(function (User $record, ?string $state) use ($isSelf) {
                        if ($state && ! $isSelf($record)) {
                            $record->syncRoles([$state]);
                        }
                    }),
                Toggle::make('is_active')->label('Aktif')->default(true)->inline(false)
                    ->disabled($isSelf)
                    ->helperText('Pasif kullanıcı panele giremez.'),
                TextInput::make('password')->label('Şifre')->password()->revealable()
                    ->rule(Password::min(10)->letters()->numbers())
                    ->required(fn (string $operation) => $operation === 'create')
                    ->dehydrated(fn (?string $state) => filled($state))
                    ->confirmed()
                    ->helperText(fn (string $operation) => $operation === 'edit' ? 'Değiştirmek istemiyorsanız boş bırakın.' : 'En az 10 karakter, harf ve rakam içermeli.'),
                TextInput::make('password_confirmation')->label('Şifre (tekrar)')->password()->revealable()
                    ->required(fn (string $operation) => $operation === 'create')
                    ->dehydrated(false),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('roles'))
            ->columns([
                TextColumn::make('name')->label('Ad Soyad')->searchable()->description(fn (User $r) => $r->email),
                TextColumn::make('roles.name')->label('Rol')->badge()
                    ->formatStateUsing(fn (string $state) => Role::tryFrom($state)?->label() ?? $state)
                    ->color(fn (string $state) => $state === Role::SuperAdmin->value ? 'danger' : ($state === Role::Admin->value ? 'warning' : 'gray')),
                IconColumn::make('is_active')->label('Aktif')->boolean(),
                TextColumn::make('last_login_at')->label('Son giriş')->since()->placeholder('Hiç')->sortable(),
                TextColumn::make('created_at')->label('Oluşturma')->date('d.m.Y')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('roles')->label('Rol')->relationship('roles', 'name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => Role::tryFrom($record->name)?->label() ?? $record->name),
                TrashedFilter::make(),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make(), RestoreAction::make()]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
