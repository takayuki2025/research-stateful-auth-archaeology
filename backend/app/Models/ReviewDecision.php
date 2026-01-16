<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class ReviewDecision extends Model
{
    protected $table = 'review_decisions';

    // ✅ migration で timestamps() を使っているので true が正しい
    public $timestamps = true;

    protected $fillable = [
        'analysis_request_id',
        'subject_type',
        'subject_id',

        'decision_type',
        'decision_reason',
        'note',

        'resolved_entities',
        'before_snapshot',
        'after_snapshot',

        'decided_by_type',
        'decided_by',
        'decided_at',

        // ✅ 明示してもOK（不要だが、運用上の事故防止になる）
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'resolved_entities' => 'array',
        'after_snapshot'    => 'array',
        'before_snapshot'   => 'array',
        'decided_at'       => 'datetime',
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
    ];
}