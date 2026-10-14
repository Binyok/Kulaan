<?php

namespace App\Rules;

use App\Models\SupplierProduct;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class DistributorOffersProduct implements ValidationRule
{
    public function __construct(private mixed $productId, private mixed $quantity) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_null($value)) {
            return;
        }

        if (empty($this->productId)) {
            return;
        }

        $sp = SupplierProduct::with('product')->where('distributor_id', $value)
            ->where('product_id', $this->productId)
            ->where('is_available', true)
            ->first();

        if (! $sp) {
            $fail('Distributor yang dipilih tidak menjual produk ini atau sedang tidak aktif.');

            return;
        }

        if ($sp->stock_available !== null && (float) $this->quantity > (float) $sp->stock_available) {
            $fail('Stok '.$sp->stock_available.' '.($sp->product->unit ?? 'unit').' tidak mencukupi untuk permintaan '.$this->quantity.'.');
        }
    }
}
