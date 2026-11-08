<?php

namespace App\Enums;

enum ProjectProgress: string
{
    case Ongoing = 'ongoing';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Ongoing => 'Devam eden',
            self::Completed => 'Tamamlanan',
        };
    }
}
