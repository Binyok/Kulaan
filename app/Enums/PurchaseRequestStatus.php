<?php

namespace App\Enums;

enum PurchaseRequestStatus: string
{
    case PENDING_MATCH = 'PENDING_MATCH';
    case MATCHED = 'MATCHED';
    case FAILED_MATCH_FALLBACK = 'FAILED_MATCH_FALLBACK';
    case CANCELLED = 'CANCELLED';
    case ORDERED = 'ORDERED';

    public function label(): string
    {
        return match ($this) {
            self::PENDING_MATCH => 'Mencari Pasangan Kolektif (Matching)',
            self::MATCHED => 'Matching Berhasil (Terkuota)',
            self::FAILED_MATCH_FALLBACK => 'Jalur Pembelian Cepat (Fallback)',
            self::CANCELLED => 'Dibatalkan',
            self::ORDERED => 'Diproses dalam Pesanan',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::PENDING_MATCH => 'bg-brand-ochre-soft text-brand-brass',
            self::MATCHED => 'bg-brand-mint text-brand-emerald',
            self::FAILED_MATCH_FALLBACK => 'bg-brand-ochre-soft text-brand-brass',
            self::CANCELLED => 'bg-brand-brick/10 text-brand-brick',
            self::ORDERED => 'bg-brand-mint text-brand-emerald',
        };
    }
}
