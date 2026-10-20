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
    ];

    protected function casts(): array
    {
        return [
            'estimated_arrival_at' => 'datetime',
            'delivered_at' => 'datetime',
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
