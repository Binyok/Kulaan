<?php

namespace App\Support;

/**
 * Builds contact links (WhatsApp / phone) from Indonesian phone numbers
 * as users type them (0812…, 62812…, +62 812… all accepted).
 */
class ContactLinks
{
    /**
     * Digits in international format (62…), or null when unusable.
     */
    public static function normalizePhone(?string $phone): ?string
    {
        if (! filled($phone)) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone);

        if (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '62'.$digits;
        }

        return strlen($digits) >= 10 ? $digits : null;
    }

    public static function whatsApp(?string $phone): ?string
    {
        $digits = self::normalizePhone($phone);

        return $digits ? 'https://wa.me/'.$digits : null;
    }

    public static function tel(?string $phone): ?string
    {
        $digits = self::normalizePhone($phone);

        return $digits ? 'tel:+'.$digits : null;
    }
}
