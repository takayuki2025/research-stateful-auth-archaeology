<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class AnalysisRequest extends Model
{
    use HasFactory;

    protected $table = 'analysis_requests';

    /**
     * Mass Assignment
     */
    protected $fillable = [
        'tenant_id',
        'item_id',
        'analysis_version',
        'requested_version',
        'payload_hash',
        'idempotency_key',
        'status',

        'original_request_id',
        'replay_index',

        'retry_count',

        'triggered_by_type',
        'triggered_by',
        'trigger_reason',

        'started_at',
        'finished_at',
    ];

    /**
     * Casts
     */
    protected $casts = [
        'tenant_id'           => 'integer',
        'item_id'             => 'integer',
        'retry_count'         => 'integer',
        'original_request_id' => 'integer',
        'replay_index'        => 'integer',
        'triggered_by'        => 'integer',

        'started_at'  => 'datetime',
        'finished_at' => 'datetime',
    ];

    /**
     * Status constants（Infrastructure 用）
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_RUNNING = 'running';
    public const STATUS_DONE    = 'done';
    public const STATUS_FAILED  = 'failed';

    /**
     * Event Ledger
     */
    public function events(): HasMany
    {
        return $this->hasMany(AnalysisRequestEvent::class, 'analysis_request_id');
    }

    /**
     * Replay 元リクエスト
     */
    public function original()
    {
        return $this->belongsTo(self::class, 'original_request_id');
    }

    /**
     * Replay 派生リクエスト群
     */
    public function replays(): HasMany
    {
        return $this->hasMany(self::class, 'original_request_id');
    }
}