<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case UMKM = 'umkm';
    case DISTRIBUTOR = 'distributor';
    case KURIR = 'kurir';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrator',
            self::UMKM => 'Pemilik UMKM',
            self::DISTRIBUTOR => 'Distributor Bahan Baku',
            self::KURIR => 'Kurir Pengiriman',
        };
    }
}
