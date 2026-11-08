<?php

namespace App\Enums;

/** Gayrimenkul ilanının ticari durumu (yayın durumundan bağımsız). */
enum ListingStatus: string
{
    case Available = 'available';
    case Reserved = 'reserved';
    case Sold = 'sold';
    case Rented = 'rented';

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Satışta',
            self::Reserved => 'Opsiyonlu',
            self::Sold => 'Satıldı',
            self::Rented => 'Kiralandı',
        };
    }
}
