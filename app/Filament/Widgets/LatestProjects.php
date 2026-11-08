<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Projects\ProjectResource;
use App\Filament\Tables\CommonColumns;
use App\Models\Project;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestProjects extends TableWidget
{
    protected static ?int $sort = 3;

    protected static ?string $heading = 'Son eklenen projeler';

    public static function canView(): bool
    {
        return (bool) auth()->user()?->can('projects.manage');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Project::query()->with('cover')->latest()->limit(5))
            ->paginated(false)
            ->recordUrl(fn (Project $r) => ProjectResource::getUrl('edit', ['record' => $r]))
            ->columns([
                CommonColumns::thumbnail(),
                TextColumn::make('title')->label('Proje'),
                CommonColumns::publicationState(),
                IconColumn::make('is_featured')->label('Öne çıkan')->boolean(),
            ]);
    }
}
