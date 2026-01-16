<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class LearningCandidate extends Model
{
    protected $table = 'learning_candidates';

    protected $fillable = [
        'entity_type',
        'proposed_name',
        'normalized_key',
        'decision_type',      // ★追加
        'entity_id',          // ★追加
        'source',
        'confidence',
        'analysis_request_id',
        'review_decision_id',
        'status',
    ];

    protected $casts = [
        'confidence' => 'float',
    ];
}