<?php

namespace App\Services;

use App\Enums\ListingType;
use App\Models\Property;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Portföy filtreleme. Hero formu, /portfoy, /satilik, /kiralik aynı mantığı kullanır.
 */
class PropertySearch
{
    /**
     * @param  array<string, mixed>  $filters  PropertySearchRequest::filters()
     */
    public function query(array $filters): Builder
    {
        $query = Property::query()->published()->with('cover');

        if ($type = ListingType::fromSlug($filters['islem'] ?? null)) {
            $query->ofType($type);
        }

        if (! empty($filters['tip'])) {
            $query->where('property_type', $filters['tip']);
        }

        if (! empty($filters['il'])) {
            $query->where('city', $filters['il']);
        }

        if (! empty($filters['ilce'])) {
            $query->where('district', $filters['ilce']);
        }

        // Fiyat filtresi yalnızca fiyatı gösterilen ilanlarda anlamlıdır.
        if (isset($filters['fiyat_min']) || isset($filters['fiyat_max'])) {
            $query->where('show_price', true)->whereNotNull('price');
            isset($filters['fiyat_min']) && $query->where('price', '>=', $filters['fiyat_min']);
            isset($filters['fiyat_max']) && $query->where('price', '<=', $filters['fiyat_max']);
        }

        isset($filters['m2_min']) && $query->where('area_m2', '>=', $filters['m2_min']);
        isset($filters['m2_max']) && $query->where('area_m2', '<=', $filters['m2_max']);

        if (! empty($filters['oda'])) {
            $rooms = array_map('intval', $filters['oda']);
            $query->where(function (Builder $q) use ($rooms) {
                $exact = array_values(array_filter($rooms, fn (int $r) => $r < 5));
                $exact && $q->whereIn('room_count', $exact);
                in_array(5, $rooms, true) && $q->orWhere('room_count', '>=', 5);
            });
        }

        return match ($filters['siralama'] ?? 'yeni') {
            'fiyat-artan' => $query->orderByRaw('price IS NULL')->orderBy('price'),
            'fiyat-azalan' => $query->orderByRaw('price IS NULL')->orderByDesc('price'),
            'm2-azalan' => $query->orderByRaw('area_m2 IS NULL')->orderByDesc('area_m2'),
            default => $query->latestFirst(),
        };
    }

    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->query($filters)
            ->paginate(config('real_estate.per_page'))
            ->withQueryString();
    }

    /**
     * Form seçenekleri: yalnızca yayındaki ilanlarda bulunan il/ilçeler.
     *
     * @return array{cities: Collection, districts: Collection}
     */
    public function locationOptions(): array
    {
        $rows = Property::query()->published()
            ->whereNotNull('city')
            ->distinct()
            ->orderBy('city')->orderBy('district')
            ->get(['city', 'district']);

        return [
            'cities' => $rows->pluck('city')->unique()->values(),
            // { "İstanbul": ["Beşiktaş", "Sarıyer"], ... } → JS ilçe listesini il seçimine göre süzer
            'districts' => $rows->whereNotNull('district')->groupBy('city')
                ->map(fn (Collection $r) => $r->pluck('district')->unique()->values()),
        ];
    }

    /** Aktif filtre sayısı (sıralama hariç) — "Filtreleri temizle" görünürlüğü için. */
    public function activeCount(array $filters): int
    {
        return count(array_diff_key($filters, ['siralama' => true, 'islem' => true]));
    }
}
