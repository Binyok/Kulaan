<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'matching_group_id',
        'distributor_id',
        'total_amount',
        'total_quantity',
        'status',
        'payment_deadline',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'total_quantity' => 'decimal:2',
            'status' => OrderStatus::class,
            'payment_deadline' => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = 'ORD-'.date('Ymd').'-'.strtoupper(Str::random(6));
            }
        });
    }

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(Distributor::class);
    }

    public function matchingGroup(): BelongsTo
    {
        return $this->belongsTo(MatchingGroup::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Check if all UMKM members have paid their order item invoices.
     */
    public function isFullyPaid(): bool
    {
        return $this->items()->where('payment_status', '!=', 'PAID')->count() === 0;
    }
}
