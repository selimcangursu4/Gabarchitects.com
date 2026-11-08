<?php

namespace App\Enums;

/**
 * Kayıtlı durum yalnızca draft | published.
 * "Zamanlanmış" ayrı bir durum değildir: published + gelecekteki published_at (TD-006).
 * Böylece zamanlanmış yayın için cron gerekmez.
 */
enum PublishStatus: string
{
    case Draft = 'draft';
    case Published = 'published';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Taslak',
            self::Published => 'Yayında',
        };
    }
}
