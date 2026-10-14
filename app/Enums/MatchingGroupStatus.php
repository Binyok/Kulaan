<?php

namespace App\Enums;

enum MatchingGroupStatus: string
{
    case MATCHING = 'MATCHING';
    case COMPLETED = 'COMPLETED';
    case EXPIRED_FALLBACK = 'EXPIRED_FALLBACK';
    case ORDERED = 'ORDERED';

    public function label(): string
    {
        return match ($this) {
            self::MATCHING => 'Sedang Mengumpulkan Kuantitas',
            self::COMPLETED => 'Kuota Terpenuhi',
            self::EXPIRED_FALLBACK => 'Tenggat Berakhir (Jalur Cepat)',
            self::ORDERED => 'Sudah Menjadi Pesanan',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::MATCHING => 'bg-brand-ochre-soft text-brand-brass',
            self::COMPLETED => 'bg-brand-mint text-brand-emerald',
            self::EXPIRED_FALLBACK => 'bg-brand-ochre-soft text-brand-brass',
            self::ORDERED => 'bg-brand-mint text-brand-emerald',
        };
    }
}
