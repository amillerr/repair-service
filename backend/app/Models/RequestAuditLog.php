<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequestAuditLog extends Model
{
    public const UPDATED_AT = null;

    public const ACTION_CREATED = 'created';
    public const ACTION_ASSIGNED = 'assigned';
    public const ACTION_CANCELED = 'canceled';
    public const ACTION_TAKEN = 'taken';
    public const ACTION_COMPLETED = 'completed';

    protected $fillable = [
        'request_id',
        'action',
        'user_id',
        'user_name',
        'from_status',
        'to_status',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(Request::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
