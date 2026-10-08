<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AhpWeight extends Model
{
    use HasFactory;

    protected $fillable = [
        'matrix',
        'weights',
        'lambda_max',
        'ci',
        'cr',
        'is_consistent',
    ];

    protected function casts(): array
    {
        return [
            'matrix' => 'array',
            'weights' => 'array',
            'lambda_max' => 'decimal:4',
            'ci' => 'decimal:4',
            'cr' => 'decimal:4',
            'is_consistent' => 'boolean',
        ];
    }
}
