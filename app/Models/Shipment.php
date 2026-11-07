<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_number',
        'order_id',
        'courier_id',
        'status',
        'estimated_arrival_at',
        'delivered_at',
        'proof_image_path',
        'notes',
        'return_reason',
        'returned_at',
    ];

    protected function casts(): array
    {
        return [
            'estimated_arrival_at' => 'datetime',
            'delivered_at' => 'datetime',
            'returned_at' => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($shipment) {
            if (empty($shipment->shipment_number)) {
                $shipment->shipment_number = 'SHP-'.date('Ymd').'-'.strtoupper(Str::random(6));
            }
        });
    }

    /**
     * Batalkan shipment ini dan bebaskan kurirnya (kembali tersedia untuk
     * ditugaskan). Dipakai semua jalur pembatalan/kedaluwarsa order agar
     * status is_available kurir tidak pernah nyangkut false.
     */
    public function cancelAndReleaseCourier(): void
    {
        if ($this->status === 'DELIVERED') {
            return;
        }

        $courier = $this->courier;

        $this->update(['status' => 'CANCELLED']);

        if ($courier) {
            $courier->update(['is_available' => true]);
        }
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function courier(): BelongsTo
    {
        return $this->belongsTo(Courier::class);
    }

    public function trackingLocations(): HasMany
    {
        return $this->hasMany(TrackingLocation::class)->latest('recorded_at');
    }

    public function latestLocation()
    {
        return $this->hasOne(TrackingLocation::class)->latestOfMany('recorded_at');
    }
}
