<?php

namespace App\Enums;

enum UrgencyLevel: string
{
    case RENDAH = 'rendah';
    case SEDANG = 'sedang';
    case TINGGI = 'tinggi';

    public function label(): string
    {
        return match ($this) {
            self::RENDAH => 'Biasa (Standar)',
            self::SEDANG => 'Penting',
            self::TINGGI => 'Mendesak / Sangat Penting',
        };
    }
}
