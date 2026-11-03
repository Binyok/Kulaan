<?php

return [
    'auto_confirm_hours' => (int) env('ESCROW_AUTO_CONFIRM_HOURS', 48),
    'ship_deadline_days' => (int) env('ESCROW_SHIP_DEADLINE_DAYS', 2),
    'payout_driver' => env('PAYOUT_DRIVER', 'auto'),
    'iris' => [
        'api_key' => env('MIDTRANS_IRIS_API_KEY'),
        'creator_key' => env('MIDTRANS_IRIS_CREATOR_KEY'),
        'approver_key' => env('MIDTRANS_IRIS_APPROVER_KEY'),
        'merchant_key' => env('MIDTRANS_IRIS_MERCHANT_KEY'),
        'is_production' => (bool) env('MIDTRANS_PRODUCTION', false),
    ],
];
