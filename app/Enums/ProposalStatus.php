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
            self::PENDING => 'bg-brand-ochre-soft text-brand-brass',
            self::APPROVED => 'bg-brand-mint text-brand-emerald',
            self::REJECTED => 'bg-brand-brick/10 text-brand-brick',
        };
    }
}
