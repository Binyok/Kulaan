<?php

namespace App\Models;

use App\Enums\ProposalStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductProposal extends Model
{
    protected $fillable = [
        'distributor_id',
        'category_id',
        'name',
        'unit',
        'standard_specification',
        'stock_available',
        'moq',
        'base_retail_price',
        'status',
        'admin_note',
        'product_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProposalStatus::class,
        ];
    }

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(Distributor::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
