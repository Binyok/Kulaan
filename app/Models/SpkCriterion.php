<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpkCriterion extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type',
        'default_weight',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'default_weight' => 'decimal:4',
            'is_active' => 'boolean',
        ];
    }
}
