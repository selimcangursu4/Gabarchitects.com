<?php

namespace App\Enums;

enum ListingType: string
{
    case Sale = 'sale';
    case Rent = 'rent';
    case Exchange = 'exchange';   // Kat karşılığı

    public function label(): string
    {
        return config("real_estate.listing_types.{$this->value}.label");
    }

    /** URL segmenti: satilik | kiralik | kat-karsiligi */
    public function slug(): string
    {
        return config("real_estate.listing_types.{$this->value}.slug");
    }

    public static function fromSlug(?string $slug): ?self
    {
        foreach (self::cases() as $case) {
            if ($case->slug() === $slug) {
                return $case;
            }
        }

        return null;
    }
}
