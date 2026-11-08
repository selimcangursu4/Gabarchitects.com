<?php

namespace App\Enums;

/** Bir kaydın şu anki görünürlük durumu (PublishStatus + published_at'ten türetilir). */
enum PublicationState: string
{
    case Draft = 'draft';
    case Scheduled = 'scheduled';
    case Published = 'published';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Taslak',
            self::Scheduled => 'Zamanlanmış',
            self::Published => 'Yayında',
        };
    }
}
