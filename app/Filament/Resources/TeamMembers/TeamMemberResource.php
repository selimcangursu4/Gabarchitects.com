<?php

namespace App\Filament\Resources\TeamMembers;

use App\Filament\Forms\MediaUpload;
use App\Filament\Tables\CommonColumns;
use App\Models\TeamMember;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

/** Brief §4.2: yönetici ve ekip tanıtımları (kayıt yoksa Hakkımızda'da bölüm gizlenir). */
class TeamMemberResource extends Resource
{
    protected static ?string $model = TeamMember::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|UnitEnum|null $navigationGroup = 'İçerik';

    protected static ?int $navigationSort = 8;

    protected static ?string $modelLabel = 'ekip üyesi';

    protected static ?string $pluralModelLabel = 'Ekip';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Ad Soyad')->required()->maxLength(120),
            TextInput::make('title')->label('Unvan')->maxLength(120),
            Textarea::make('bio')->label('Kısa biyografi')->rows(4)->columnSpanFull(),
            TextInput::make('linkedin_url')->label('LinkedIn')->url()->maxLength(255),
            Toggle::make('is_active')->label('Aktif')->default(true)->inline(false),
            MediaUpload::make('photo_media_id')->label('Fotoğraf')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('photo'))
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                CommonColumns::thumbnail('photo', 'Fotoğraf'),
                TextColumn::make('name')->label('Ad Soyad')->searchable()->description(fn (TeamMember $r) => $r->title),
                ToggleColumn::make('is_active')->label('Aktif')->disabled(fn () => ! auth()->user()->can('team.manage')),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageTeamMembers::route('/')];
    }
}
