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
            self::UNPAID, self::PENDING => 'bg-[#FEF9E7] text-[#D4A017]',
            self::PAID => 'bg-[#E8F5E9] text-[#16A34A]',
            self::EXPIRED, self::CANCELLED, self::FAILED => 'bg-red-50 text-[#C0392B]',
        };
    }
}
