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
            self::MATCHING => 'bg-[#FEF9E7] text-[#D4A017]',
            self::COMPLETED => 'bg-[#E8F5E9] text-[#16A34A]',
            self::EXPIRED_FALLBACK => 'bg-amber-100 text-amber-800',
            self::ORDERED => 'bg-emerald-100 text-emerald-800',
        };
    }
}
