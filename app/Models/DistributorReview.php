<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DistributorReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_item_id',
        'umkm_id',
        'distributor_id',
        'rating',
        'comment',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (DistributorReview $review) {
            $review->recalculateDistributorRating();
        });

        static::deleted(function (DistributorReview $review) {
            $review->recalculateDistributorRating();
        });
    }

    public function recalculateDistributorRating(): void
    {
        $distributor = $this->distributor;
        if ($distributor) {
            $avg = self::where('distributor_id', $distributor->id)->avg('rating');
            if ($avg !== null) {
                $distributor->update(['rating' => round($avg, 2)]);
            }
        }
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function umkm(): BelongsTo
    {
        return $this->belongsTo(Umkm::class);
    }

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(Distributor::class);
    }
}
