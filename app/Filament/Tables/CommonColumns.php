<?php

namespace App\Filament\Tables;

use App\Enums\PublicationState;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

/**
 * Tekrarlanan tablo parçaları.
 */
class CommonColumns
{
    public static function thumbnail(string $relation = 'cover', string $label = 'Görsel'): ImageColumn
    {
        return ImageColumn::make($relation.'_thumb')
            ->label($label)
            ->state(fn ($record) => $record->{$relation}?->url(480))
            ->imageHeight(52)
            ->imageWidth(70)
            ->extraImgAttributes(['class' => 'object-cover', 'loading' => 'lazy']);
    }

    /** Taslak / Zamanlanmış / Yayında (published_at'e göre türetilir) */
    public static function publicationState(): TextColumn
    {
        return TextColumn::make('publication_state')
            ->label('Durum')
            ->state(fn ($record) => $record->publicationState()->label())
            ->badge()
            ->color(fn ($record) => match ($record->publicationState()) {
                PublicationState::Published => 'success',
                PublicationState::Scheduled => 'info',
                PublicationState::Draft => 'gray',
            })
            ->description(fn ($record) => $record->publicationState() === PublicationState::Scheduled
                ? $record->published_at->format('d.m.Y H:i')
                : null);
    }

    public static function publicationFilter(): SelectFilter
    {
        return SelectFilter::make('publication')
            ->label('Durum')
            ->options(collect(PublicationState::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()]))
            ->query(fn (Builder $query, array $data) => match ($data['value'] ?? null) {
                'published' => $query->published(),
                'scheduled' => $query->scheduled(),
                'draft' => $query->where('status', 'draft'),
                default => $query,
            });
    }
}
