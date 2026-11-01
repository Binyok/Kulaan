<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderDispute extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_item_id',
        'umkm_id',
        'reason',
        'description',
        'evidence_path',
        'status',
        'resolution_note',
        'release_amount',
        'refund_amount',
        'resolved_by',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'release_amount' => 'decimal:2',
            'refund_amount' => 'decimal:2',
            'resolved_at' => 'datetime',
        ];
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function umkm(): BelongsTo
    {
        return $this->belongsTo(Umkm::class);
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
