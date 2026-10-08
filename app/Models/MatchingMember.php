<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchingMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'matching_group_id',
        'purchase_request_id',
        'umkm_id',
        'quantity',
        'individual_subtotal',
        'individual_savings',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'individual_subtotal' => 'decimal:2',
            'individual_savings' => 'decimal:2',
        ];
    }

    public function matchingGroup(): BelongsTo
    {
        return $this->belongsTo(MatchingGroup::class);
    }

    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function umkm(): BelongsTo
    {
        return $this->belongsTo(Umkm::class);
    }
}
