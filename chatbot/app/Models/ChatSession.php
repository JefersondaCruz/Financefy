<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatSession extends Model
{
    protected $fillable = [
        'user_id',
        'current_state',
        'context_data',
        'is_active',
        'last_activity',
        'ended_at',
    ];

    protected $casts = [
        'context_data' => 'array',
        'is_active' => 'boolean',
        'last_activity' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
