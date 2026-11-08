<?php

namespace App\Http\Controllers\Site;

use App\Enums\ListingType;
use App\Http\Controllers\Controller;
use App\Http\Requests\PropertySearchRequest;
use App\Models\Property;
use App\Services\PropertySearch;
use App\Support\Phone;
use Illuminate\View\View;

class PropertyController extends Controller
{
    /** /portfoy, /satilik, /kiralik (modül kapalıysa route middleware'i 404 döner) */
    public function index(PropertySearchRequest $request, PropertySearch $search, ?string $listingType = null): View
    {
        $filters = $request->filters();

        if ($listingType) {
            $filters['islem'] = $listingType;   // /satilik ve /kiralik sabit işlem tipiyle açılır
        }

        $type = ListingType::fromSlug($filters['islem'] ?? null);

        return view('pages.properties.index', [
            'properties' => $search->paginate($filters),
            'filters' => $filters,
            'listingType' => $type,
            'fixedType' => (bool) $listingType,
            'locations' => $search->locationOptions(),
            'activeFilterCount' => $search->activeCount($filters),
            'title' => $type ? $type->label().' Portföy' : 'Portföylerimiz',
        ]);
    }

    public function show(Property $property): View
    {
        abort_unless($property->isPubliclyVisible(), 404);

        $property->load(['cover', 'gallery']);

        // İlan sayfasında form yok: WhatsApp + arama (kullanıcı kararı 2026-11-08).
        // Mesaj, hangi ilanın sorulduğu anlaşılsın diye önceden doldurulur.
        $reference = 'GAB-'.str_pad((string) $property->id, 5, '0', STR_PAD_LEFT);
        $message = "Merhaba, \"{$property->title}\" ilanı (İlan no: {$reference}) hakkında bilgi almak istiyorum.\n{$property->url()}";

        return view('pages.properties.show', [
            'property' => $property,
            'reference' => $reference,
            'contact' => [
                'whatsapp' => Phone::whatsappHref(settings('contact.whatsapp'), $message),
                'tel' => Phone::telHref(settings('contact.phone')),
                'phone' => settings('contact.phone'),
            ],
            'similar' => Property::published()
                ->whereKeyNot($property->id)
                ->where('listing_type', $property->listing_type)
                ->where(fn ($q) => $q->where('property_type', $property->property_type)->orWhere('city', $property->city))
                ->latestFirst()->with('cover')->limit(3)->get(),
        ]);
    }
}
