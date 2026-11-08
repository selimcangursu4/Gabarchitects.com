<?php

namespace App\Enums;

enum ContactStatus: string
{
    case New = 'new';
    case InReview = 'in_review';
    case Answered = 'answered';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::New => 'Yeni',
            self::InReview => 'İnceleniyor',
            self::Answered => 'Yanıtlandı',
            self::Archived => 'Arşivlendi',
        };
    }
}
