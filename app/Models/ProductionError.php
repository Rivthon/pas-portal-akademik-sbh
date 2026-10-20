<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductionError extends Model
{
    protected $fillable = [
        'fingerprint',
        'exception_class',
        'message',
        'file',
        'line',
        'trace',
        'http_status',
        'method',
        'path',
        'route_name',
        'actor_guard',
        'actor_type',
        'actor_id',
        'actor_name',
        'actor_identifier',
        'context',
        'occurrences',
        'first_seen_at',
        'last_seen_at',
    ];

    protected function casts(): array
    {
        return [
            'context' => 'array',
            'first_seen_at' => 'datetime',
            'last_seen_at' => 'datetime',
        ];
    }

    public function events(): HasMany
    {
        return $this->hasMany(ProductionErrorEvent::class);
    }
}
