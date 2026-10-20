<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class LiveRevisionController extends Controller
{
    /**
     * Tables whose latest change should nudge an open page to refresh itself.
     *
     * The revision is a cheap fingerprint of "did anything move?" so the
     * poller only reloads when there is genuinely new data to show.
     */
    private const TABLES = [
        'orders',
        'order_items',
        'shipments',
        'tracking_locations',
        'payments',
        'purchase_requests',
        'matching_groups',
        'products',
        'supplier_products',
        'product_proposals',
        'distributors',
        'couriers',
        'umkms',
        'spk_calculation_histories',
    ];

    public function __invoke(): JsonResponse
    {
        $parts = [];

        foreach (self::TABLES as $table) {
            $row = DB::table($table)
                ->selectRaw('COUNT(*) as total, MAX(updated_at) as touched_at')
                ->first();

            $parts[] = $table.':'.$row->total.':'.($row->touched_at ?? '');
        }

        return response()->json([
            'revision' => md5(implode('|', $parts)),
        ]);
    }
}
