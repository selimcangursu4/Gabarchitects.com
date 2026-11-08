<?php

/*
| Gayrimenkul portföyü: işlem tipleri, emlak tipleri ve filtre seçenekleri.
| Panelde (PHASE 04) seçim listeleri ve sitede filtreler buradan beslenir.
*/

return [
    // İşlem tipi → URL segmenti
    'listing_types' => [
        'sale' => ['label' => 'Satılık', 'slug' => 'satilik'],
        'rent' => ['label' => 'Kiralık', 'slug' => 'kiralik'],
    ],

    // Emlak tipleri (gruplu). Anahtar veritabanında saklanır.
    'property_types' => [
        'Konut' => [
            'daire' => 'Daire',
            'rezidans' => 'Rezidans',
            'villa' => 'Villa',
            'mustakil-ev' => 'Müstakil Ev',
            'yali' => 'Yalı / Köşk',
        ],
        'İşyeri' => [
            'ofis' => 'Ofis',
            'dukkan' => 'Dükkan / Mağaza',
            'bina' => 'Komple Bina',
            'depo' => 'Depo / Fabrika',
            'otel' => 'Otel / Turistik Tesis',
        ],
        'Arsa' => [
            'arsa' => 'Arsa',
            'tarla' => 'Tarla',
        ],
    ],

    // Oda filtresi: room_count (ilk sayı) üzerinden. "5" = 5 ve üzeri.
    'room_options' => [
        1 => '1+0 / 1+1',
        2 => '2+1',
        3 => '3+1',
        4 => '4+1',
        5 => '5+1 ve üzeri',
    ],

    // Oda filtresinin anlamsız olduğu tipler (form bu tiplerde oda seçimini gizler)
    'types_without_rooms' => ['arsa', 'tarla', 'depo', 'bina', 'otel'],

    'sort_options' => [
        'yeni' => 'En yeni',
        'fiyat-artan' => 'Fiyat (artan)',
        'fiyat-azalan' => 'Fiyat (azalan)',
        'm2-azalan' => 'Metrekare (büyükten küçüğe)',
    ],

    'currencies' => ['TRY' => '₺', 'USD' => '$', 'EUR' => '€', 'GBP' => '£'],

    'per_page' => 12,

    'home_featured_count' => 9,
];
