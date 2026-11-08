<?php

namespace App\Models;

use App\Enums\ListingStatus;
use App\Enums\ListingType;
use App\Models\Concerns\HasMediaGallery;
use App\Models\Concerns\HasPublishing;
use App\Models\Concerns\HasSlug;
use App\Models\Concerns\LogsActivity;
use App\Models\Concerns\SanitizesHtml;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;

#[Fillable([
    'title', 'slug', 'listing_type', 'property_type', 'city', 'district', 'location', 'listing_status',
    'summary', 'body', 'price', 'currency', 'show_price', 'area_m2', 'net_area_m2', 'rooms', 'bathrooms',
    'cover_media_id', 'status', 'published_at', 'is_featured', 'is_sample', 'sort_order',
    'meta_title', 'meta_description',
])]
class Property extends Model
{
    use HasFactory, HasMediaGallery, HasPublishing, HasSlug, LogsActivity, SanitizesHtml, SoftDeletes;

    protected static array $sanitizedHtml = ['body'];

    protected function casts(): array
    {
        return [
            'listing_type' => ListingType::class,
            'listing_status' => ListingStatus::class,
            'price' => 'decimal:2',
            'show_price' => 'boolean',
            'is_featured' => 'boolean',
            'is_sample' => 'boolean',
            'area_m2' => 'integer',
            'net_area_m2' => 'integer',
            'room_count' => 'integer',
            'bathrooms' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        // "3+1" → room_count = 3 (oda filtresi bu alan üzerinden çalışır)
        static::saving(function (self $property) {
            $property->room_count = preg_match('/^\s*(\d+)/', (string) $property->rooms, $m) ? (int) $m[1] : null;
        });
    }

    public function contactMessages(): HasMany
    {
        return $this->hasMany(ContactMessage::class);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /** "Son eklenen" sırası (talep: öne çıkan son 9 ilan) */
    public function scopeLatestFirst(Builder $query): Builder
    {
        return $query->orderByDesc('created_at')->orderByDesc('id');
    }

    public function scopeOfType(Builder $query, ListingType $type): Builder
    {
        return $query->where('listing_type', $type->value);
    }

    public function propertyTypeLabel(): ?string
    {
        return Arr::collapse(config('real_estate.property_types'))[$this->property_type] ?? null;
    }

    public function locationLabel(): ?string
    {
        return collect([$this->location, $this->district, $this->city])->filter()->unique()->implode(', ') ?: null;
    }

    public function formattedPrice(): ?string
    {
        if (! $this->show_price || $this->price === null) {
            return null;
        }

        $symbol = config("real_estate.currencies.{$this->currency}", $this->currency);
        $amount = number_format((float) $this->price, 0, ',', '.');
        $suffix = $this->listing_type === ListingType::Rent ? ' / ay' : '';

        return "{$amount} {$symbol}{$suffix}";
    }

    public function url(): string
    {
        return url('/portfoy/'.$this->slug);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
