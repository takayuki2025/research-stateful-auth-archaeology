<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class ReviewDecision extends Model
{
    protected $table = 'review_decisions';

    protected $fillable = [
        'analysis_request_id',
        'subject_type',
        'subject_id',
        'decision_type',
        'decision_reason',
        'note',
        'before_snapshot',
        'after_snapshot',
        'decided_by_type',
        'decided_by',
        'decided_at',
    ];

    protected $casts = [
        'before_snapshot' => 'array',
        'after_snapshot' => 'array',
        'decided_at' => 'datetime',
    ];
}