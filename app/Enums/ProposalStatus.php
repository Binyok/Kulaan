<?php

namespace App\Enums;

enum ProposalStatus: string
{
    case PENDING = 'PENDING';
    case APPROVED = 'APPROVED';
    case REJECTED = 'REJECTED';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Menunggu Review',
            self::APPROVED => 'Disetujui',
            self::REJECTED => 'Ditolak',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::PENDING => 'bg-[#FEF9E7] text-[#D4A017]',
            self::APPROVED => 'bg-[#E8F5E9] text-[#16A34A]',
            self::REJECTED => 'bg-red-50 text-[#C0392B]',
        };
    }
}
