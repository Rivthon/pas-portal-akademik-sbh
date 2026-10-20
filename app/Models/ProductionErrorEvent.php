<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionErrorEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'production_error_id',
        'actor_guard',
        'actor_type',
        'actor_id',
        'actor_name',
        'actor_identifier',
        'context',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'context' => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    public function productionError(): BelongsTo
    {
        return $this->belongsTo(ProductionError::class);
    }
}
