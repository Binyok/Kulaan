<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_reference',
        'order_id',
        'order_item_id',
        'umkm_id',
        'amount',
        'payment_method',
        'payment_status',
        'snap_token',
        'snap_redirect_url',
        'transaction_id',
        'paid_at',
        'expired_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_status' => PaymentStatus::class,
            'paid_at' => 'datetime',
            'expired_at' => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($payment) {
            if (empty($payment->payment_reference)) {
                $payment->payment_reference = 'PAY-'.date('Ymd').'-'.strtoupper(Str::random(6));
            }
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function umkm(): BelongsTo
    {
        return $this->belongsTo(Umkm::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(PaymentEvent::class);
    }
}
