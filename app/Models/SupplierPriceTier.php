<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierPriceTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_product_id',
        'min_quantity',
        'max_quantity',
        'unit_price',
    ];

    protected function casts(): array
    {
        return [
            'min_quantity' => 'decimal:2',
            'max_quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
        ];
    }

    public function supplierProduct(): BelongsTo
    {
        return $this->belongsTo(SupplierProduct::class);
    }
}
