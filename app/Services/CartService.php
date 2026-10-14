<?php

namespace App\Services;

use App\Models\Product;
use App\Models\SupplierProduct;

class CartService
{
    private const SESSION_KEY = 'cart';

    public function add(int $productId, int $distributorId, float $quantity): void
    {
        if ($quantity <= 0) {
            return;
        }

        $cart = $this->getRawCart();
        $key = $this->itemKey($productId, $distributorId);

        if (isset($cart[$key])) {
            $cart[$key]['quantity'] = round((float) $cart[$key]['quantity'] + $quantity, 2);
        } else {
            $cart[$key] = [
                'product_id' => $productId,
                'distributor_id' => $distributorId,
                'quantity' => round($quantity, 2),
            ];
        }

        session([self::SESSION_KEY => $cart]);
    }

    public function updateQty(int $productId, int $distributorId, float $quantity): void
    {
        $key = $this->itemKey($productId, $distributorId);
        $cart = $this->getRawCart();

        if ($quantity <= 0) {
            unset($cart[$key]);
        } elseif (isset($cart[$key])) {
            $cart[$key]['quantity'] = round($quantity, 2);
        }

        session([self::SESSION_KEY => $cart]);
    }

    public function remove(int $productId, int $distributorId): void
    {
        $key = $this->itemKey($productId, $distributorId);
        $cart = $this->getRawCart();
        unset($cart[$key]);
        session([self::SESSION_KEY => $cart]);
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    public function count(): int
    {
        return count($this->getRawCart());
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function items(): array
    {
        $raw = $this->getRawCart();
        if (empty($raw)) {
            return [];
        }

        $hydrated = [];

        foreach ($raw as $key => $entry) {
            $productId = (int) $entry['product_id'];
            $distributorId = (int) $entry['distributor_id'];
            $qty = (float) $entry['quantity'];

            $product = Product::with('category')->find($productId);
            $supplierProduct = SupplierProduct::with(['distributor', 'priceTiers'])
                ->where('product_id', $productId)
                ->where('distributor_id', $distributorId)
                ->first();

            if (! $product || ! $supplierProduct) {
                continue;
            }

            $unitPrice = $supplierProduct->getPriceForQuantity($qty);
            $subtotal = round($unitPrice * $qty, 2);

            $benchmark = $product->benchmark_retail_price > 0
                ? (float) $product->benchmark_retail_price
                : (float) $supplierProduct->base_retail_price;

            $retailTotal = round($benchmark * $qty, 2);
            $savings = max(0, $retailTotal - $subtotal);

            $tierLabel = 'Eceran';
            if ($qty >= (float) $supplierProduct->moq) {
                $tierLabel = 'Tier Grosir';
            }
            foreach ($supplierProduct->priceTiers as $tier) {
                if ($qty >= (float) $tier->min_quantity) {
                    if (is_null($tier->max_quantity) || $qty <= (float) $tier->max_quantity) {
                        $tierLabel = 'Tier ≥ '.number_format((float) $tier->min_quantity, 0).' '.$product->unit;
                        break;
                    }
                }
            }

            $hydrated[$key] = [
                'key' => $key,
                'product_id' => $productId,
                'distributor_id' => $distributorId,
                'quantity' => $qty,
                'product' => $product,
                'supplier_product' => $supplierProduct,
                'distributor' => $supplierProduct->distributor,
                'unit_price' => $unitPrice,
                'subtotal' => $subtotal,
                'benchmark_retail_price' => $benchmark,
                'retail_total' => $retailTotal,
                'savings' => $savings,
                'tier_label' => $tierLabel,
                'is_available' => (bool) $supplierProduct->is_available,
                'stock_available' => (float) $supplierProduct->stock_available,
                'moq' => (float) $supplierProduct->moq,
            ];
        }

        return $hydrated;
    }

    public function total(): float
    {
        $sum = 0.0;
        foreach ($this->items() as $item) {
            $sum += (float) $item['subtotal'];
        }

        return round($sum, 2);
    }

    public function totalSavings(): float
    {
        $sum = 0.0;
        foreach ($this->items() as $item) {
            $sum += (float) $item['savings'];
        }

        return round($sum, 2);
    }

    public function itemKey(int $productId, int $distributorId): string
    {
        return $productId.':'.$distributorId;
    }

    /**
     * @return array<string, array{product_id: int, distributor_id: int, quantity: float}>
     */
    private function getRawCart(): array
    {
        $cart = session(self::SESSION_KEY, []);

        return is_array($cart) ? $cart : [];
    }
}
