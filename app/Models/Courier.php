<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Courier extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'distributor_id',
        'vehicle_type',
        'vehicle_number',
        'is_available',
        'current_latitude',
        'current_longitude',
        'last_location_updated_at',
    ];

    protected function casts(): array
    {
        return [
            'is_available' => 'boolean',
            'last_location_updated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(Distributor::class);
    }
}
