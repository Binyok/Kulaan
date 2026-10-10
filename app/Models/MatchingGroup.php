<?php

namespace App\Models;

use App\Enums\MatchingGroupStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class MatchingGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_code',
        'product_id',
        'district',
        'total_quantity',
        'target_moq',
        'status',
        'selected_distributor_id',
        'agreed_unit_price',
        'matched_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => MatchingGroupStatus::class,
            'total_quantity' => 'decimal:2',
            'target_moq' => 'decimal:2',
            'agreed_unit_price' => 'decimal:2',
            'matched_at' => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($group) {
            if (empty($group->group_code)) {
                $group->group_code = 'GRP-'.strtoupper(Str::random(8));
            }
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function selectedDistributor(): BelongsTo
    {
        return $this->belongsTo(Distributor::class, 'selected_distributor_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(MatchingMember::class);
    }

    public function purchaseRequests(): HasMany
    {
        return $this->hasMany(PurchaseRequest::class);
    }
}
