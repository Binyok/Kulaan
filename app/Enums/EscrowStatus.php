<?php

namespace App\Enums;

enum EscrowStatus: string
{
    case HELD = 'HELD';
    case RELEASED = 'RELEASED';
    case REFUNDED = 'REFUNDED';
    case FROZEN_DISPUTE = 'FROZEN_DISPUTE';

    public function label(): string
    {
        return match ($this) {
            self::HELD => 'Dana Ditahan Kulaan',
            self::RELEASED => 'Dana Dicairkan',
            self::REFUNDED => 'Dana Direfund',
            self::FROZEN_DISPUTE => 'Dibekukan (Komplain)',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::HELD => 'Ditahan',
            self::RELEASED => 'Dicairkan',
            self::REFUNDED => 'Direfund',
            self::FROZEN_DISPUTE => 'Dibekukan',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::HELD => 'bg-brass-soft text-brass border border-brass/30',
            self::RELEASED => 'bg-success/10 text-success border border-success/30',
            self::REFUNDED => 'bg-danger/10 text-danger border border-danger/30',
            self::FROZEN_DISPUTE => 'bg-danger text-white',
        };
    }
}
