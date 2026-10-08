<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpkCalculationHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'matching_group_id',
        'method',
        'weights_used',
        'decision_matrix',
        'rankings',
    ];

    protected function casts(): array
    {
        return [
            'weights_used' => 'array',
            'decision_matrix' => 'array',
            'rankings' => 'array',
        ];
    }

    public function matchingGroup(): BelongsTo
    {
        return $this->belongsTo(MatchingGroup::class);
    }
}
