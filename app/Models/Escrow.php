<?php

namespace App\Models;

use App\Enums\EscrowStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Escrow extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_item_id',
        'order_id',
        'distributor_id',
        'umkm_id',
        'amount',
        'status',
        'held_at',
        'delivered_at',
        'release_due_at',
        'released_at',
        'payout_reference',
        'payout_status',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'status' => EscrowStatus::class,
            'held_at' => 'datetime',
            'delivered_at' => 'datetime',
            'release_due_at' => 'datetime',
            'released_at' => 'datetime',
        ];
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(Distributor::class);
    }

    public function umkm(): BelongsTo
    {
        return $this->belongsTo(Umkm::class);
    }
}
