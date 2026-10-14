<?php

namespace App\Models;

use App\Enums\PurchaseRequestStatus;
use App\Enums\UrgencyLevel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'umkm_id',
        'product_id',
        'quantity',
        'unit',
        'deadline',
        'urgency',
        'delivery_district',
        'delivery_address',
        'notes',
        'status',
        'matching_group_id',
        'preferred_distributor_id',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'deadline' => 'datetime',
            'status' => PurchaseRequestStatus::class,
            'urgency' => UrgencyLevel::class,
        ];
    }

    public function umkm(): BelongsTo
    {
        return $this->belongsTo(Umkm::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function matchingGroup(): BelongsTo
    {
        return $this->belongsTo(MatchingGroup::class, 'matching_group_id');
    }

    public function preferredDistributor(): BelongsTo
    {
        return $this->belongsTo(Distributor::class, 'preferred_distributor_id');
    }

    /**
     * Check if request deadline has passed.
     */
    public function isExpired(): bool
    {
        return now()->greaterThanOrEqualTo($this->deadline);
    }
}
