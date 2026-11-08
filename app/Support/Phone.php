<?php

namespace App\Support;

/**
 * Telefon numarasını bağlantı biçimlerine çevirir (Türkiye varsayılan).
 *   "+90 (532) 000 00 00" | "0532 000 00 00" | "532 000 00 00"
 *     → tel:  "+905320000000"
 *     → wa.me "905320000000"
 */
class Phone
{
    /** Ülke kodlu yalın rakamlar (wa.me biçimi) ya da geçersizse null. */
    public static function international(?string $number): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $number);

        $digits = match (true) {
            str_starts_with($digits, '00') => substr($digits, 2),          // 0090...
            str_starts_with($digits, '90') && strlen($digits) === 12 => $digits,
            str_starts_with($digits, '0') && strlen($digits) === 11 => '9'.$digits,   // 0532... → 90532...
            strlen($digits) === 10 => '90'.$digits,                         // 532...
            default => $digits,
        };

        return strlen($digits) >= 10 && strlen($digits) <= 15 ? $digits : null;
    }

    public static function telHref(?string $number): ?string
    {
        $intl = static::international($number);

        return $intl ? 'tel:+'.$intl : null;
    }

    public static function whatsappHref(?string $number, ?string $message = null): ?string
    {
        $intl = static::international($number);

        if (! $intl) {
            return null;
        }

        return 'https://wa.me/'.$intl.($message ? '?text='.rawurlencode($message) : '');
    }
}
