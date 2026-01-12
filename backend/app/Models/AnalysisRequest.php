<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class AnalysisRequest extends Model
{
    protected $table = 'analysis_requests';

    protected $fillable = [
        'tenant_id',
        'item_id',
        'analysis_version',
        'payload_hash',
        'idempotency_key',
        'status',
        'started_at',
        'finished_at',
        'error_code',
        'error_message',
        'retry_count',
    ];

    protected $casts = [
        'started_at'  => 'datetime',
        'finished_at' => 'datetime',
        'retry_count' => 'integer',
    ];
}