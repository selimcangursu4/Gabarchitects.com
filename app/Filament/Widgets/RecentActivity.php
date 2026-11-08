<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ActivityLogs\ActivityLogResource;
use App\Models\ActivityLog;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentActivity extends TableWidget
{
    protected static ?int $sort = 4;

    protected static ?string $heading = 'Son yönetim işlemleri';

    public static function canView(): bool
    {
        return (bool) auth()->user()?->can('activity_logs.view');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(ActivityLog::query()->with('user')->latest('created_at')->limit(6))
            ->paginated(false)
            ->columns([
                TextColumn::make('user.name')->label('Kullanıcı')->placeholder('Sistem'),
                TextColumn::make('event')->label('İşlem')->badge()->color('gray')
                    ->formatStateUsing(fn (string $state) => ActivityLogResource::EVENTS[$state] ?? $state),
                TextColumn::make('description')->label('Kayıt')->limit(40),
                TextColumn::make('created_at')->label('Zaman')->since(),
            ])
            ->emptyStateHeading('Henüz işlem yok');
    }
}
