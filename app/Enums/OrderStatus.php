<?php

namespace App\Enums;

enum OrderStatus: string
{
    case CREATED = 'CREATED';
    case WAITING_PAYMENT = 'WAITING_PAYMENT';
    case PAID = 'PAID';
    case CONFIRMED = 'CONFIRMED';
    case PREPARING = 'PREPARING';
    case READY_TO_SHIP = 'READY_TO_SHIP';
    case ASSIGNED_TO_COURIER = 'ASSIGNED_TO_COURIER';
    case IN_TRANSIT = 'IN_TRANSIT';
    case DELIVERED = 'DELIVERED';
    case COMPLETED = 'COMPLETED';
    case CANCELLED = 'CANCELLED';
    case EXPIRED = 'EXPIRED';

    public function label(): string
    {
        return match ($this) {
            self::CREATED => 'Dibuat',
            self::WAITING_PAYMENT => 'Menunggu Pembayaran UMKM',
            self::PAID => 'Pembayaran Selesai',
            self::CONFIRMED => 'Dikonfirmasi Distributor',
            self::PREPARING => 'Sedang Dipersiapkan',
            self::READY_TO_SHIP => 'Siap Dikirim',
            self::ASSIGNED_TO_COURIER => 'Kurir Ditugaskan',
            self::IN_TRANSIT => 'Dalam Pengiriman (GPS Aktif)',
            self::DELIVERED => 'Pesanan Sampai',
            self::COMPLETED => 'Selesai',
            self::CANCELLED => 'Dibatalkan',
            self::EXPIRED => 'Kadaluarsa',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::CREATED, self::WAITING_PAYMENT => 'bg-brand-ochre-soft text-brand-brass',
            self::PAID, self::CONFIRMED => 'bg-brand-mint text-brand-emerald',
            self::PREPARING, self::READY_TO_SHIP => 'bg-brand-primary/10 text-brand-primary',
            self::ASSIGNED_TO_COURIER, self::IN_TRANSIT => 'bg-brand-accent/15 text-brand-brass',
            self::DELIVERED, self::COMPLETED => 'bg-brand-primary text-white',
            self::CANCELLED, self::EXPIRED => 'bg-brand-brick/10 text-brand-brick',
        };
    }
}
