<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Distributor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_name',
        'address',
        'district',
        'village',
        'city',
        'province',
        'latitude',
        'longitude',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'payout_beneficiary_alias',
        'payout_validated_at',
        'rating',
        'delivery_lead_time_days',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'payout_validated_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function hasCompleteBankDetails(): bool
    {
        return ! empty($this->bank_name)
            && ! empty($this->bank_account_number)
            && ! empty($this->bank_account_name);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Full postal address as one line: street, village, district, city,
     * province — empty parts skipped.
     */
    public function fullAddress(): string
    {
        $parts = array_filter([
            $this->address,
            $this->village,
            $this->district ? 'Kec. '.$this->district : null,
            $this->city,
            $this->province,
        ], fn ($part) => filled($part));

        return implode(', ', array_unique($parts));
    }

    /**
     * Google Maps directions link to this warehouse: exact coordinates
     * when set, otherwise a search by the full address text.
     */
    public function directionsUrl(): string
    {
        if ($this->latitude !== null && $this->longitude !== null) {
            return 'https://www.google.com/maps/dir/?api=1&destination='.$this->latitude.','.$this->longitude;
        }

        return 'https://www.google.com/maps/dir/?api=1&destination='.urlencode($this->fullAddress());
    }

    public function supplierProducts(): HasMany
    {
        return $this->hasMany(SupplierProduct::class);
    }

    public function couriers(): HasMany
    {
        return $this->hasMany(Courier::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(DistributorReview::class);
    }
}
