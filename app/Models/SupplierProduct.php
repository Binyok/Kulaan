<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplierProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'distributor_id',
        'product_id',
        'stock_available',
        'moq',
        'base_retail_price',
        'is_available',
    ];

    protected function casts(): array
    {
        return [
            'stock_available' => 'decimal:2',
            'moq' => 'decimal:2',
            'base_retail_price' => 'decimal:2',
            'is_available' => 'boolean',
        ];
    }

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(Distributor::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function priceTiers(): HasMany
    {
        return $this->hasMany(SupplierPriceTier::class)->orderBy('min_quantity', 'asc');
    }

    /**
     * Get price per unit for a given aggregated quantity.
     */
    public function getPriceForQuantity(float $quantity): float
    {
        $tiers = $this->priceTiers;
        foreach ($tiers as $tier) {
            if ($quantity >= $tier->min_quantity) {
                if (is_null($tier->max_quantity) || $quantity <= $tier->max_quantity) {
                    return (float) $tier->unit_price;
                }
            }
        }

        // If above all tiers, take the best (highest min_quantity) tier price
        $highestTier = $tiers->last();
        if ($highestTier && $quantity >= $highestTier->min_quantity) {
            return (float) $highestTier->unit_price;
        }

        return (float) $this->base_retail_price;
    }
}
