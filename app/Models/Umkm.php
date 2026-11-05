<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Umkm extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'business_name',
        'business_type',
        'address',
        'district',
        'village',
        'city',
        'province',
        'latitude',
        'longitude',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Full postal address as one line: street, village, district, city,
     * province — empty parts skipped. $street overrides the profile street
     * (order items snapshot the street used for that delivery).
     */
    public function fullAddress(?string $street = null): string
    {
        $parts = array_filter([
            $street ?? $this->address,
            $this->village,
            $this->district ? 'Kec. '.$this->district : null,
            $this->city,
            $this->province,
        ], fn ($part) => filled($part));

        return implode(', ', array_unique($parts));
    }

    /**
     * Google Maps directions link to this business: exact coordinates when
     * the profile has them, otherwise a search by the full address text.
     */
    public function directionsUrl(?string $street = null): string
    {
        if ($this->latitude !== null && $this->longitude !== null) {
            return 'https://www.google.com/maps/dir/?api=1&destination='.$this->latitude.','.$this->longitude;
        }

        return 'https://www.google.com/maps/dir/?api=1&destination='.urlencode($this->fullAddress($street));
    }
}
