<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case UNPAID = 'UNPAID';
    case PENDING = 'PENDING';
    case PAID = 'PAID';
    case EXPIRED = 'EXPIRED';
    case CANCELLED = 'CANCELLED';
    case FAILED = 'FAILED';

    public function label(): string
    {
        return match ($this) {
            self::UNPAID => 'Belum Dibayar',
            self::PENDING => 'Menunggu Konfirmasi Midtrans',
            self::PAID => 'Lunas',
            self::EXPIRED => 'Waktu Pembayaran Habis',
            self::CANCELLED => 'Dibatalkan',
            self::FAILED => 'Pembayaran Gagal',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::UNPAID, self::PENDING => 'bg-brand-ochre-soft text-brand-brass',
            self::PAID => 'bg-brand-mint text-brand-emerald',
            self::EXPIRED, self::CANCELLED, self::FAILED => 'bg-brand-brick/10 text-brand-brick',
        };
    }
}
