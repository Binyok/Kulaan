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
            self::PENDING_MATCH => 'bg-[#FEF9E7] text-[#D4A017]',
            self::MATCHED => 'bg-[#E8F5E9] text-[#16A34A]',
            self::FAILED_MATCH_FALLBACK => 'bg-amber-100 text-amber-800',
            self::CANCELLED => 'bg-red-50 text-[#C0392B]',
            self::ORDERED => 'bg-emerald-100 text-emerald-800',
        };
    }
}
