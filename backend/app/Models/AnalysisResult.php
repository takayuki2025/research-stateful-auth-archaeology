<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class AnalysisResult extends Model
{
    protected $table = 'analysis_results';

    protected $fillable = [
        'analysis_request_id',
        'item_id',
        'payload',
        'tags',
        'confidence',
        'generated_version',
        'raw_text',
        'status',
        'decided_by',
        'decided_user_id',
        'decided_at',
    ];

    protected $casts = [
        'payload'    => 'array',
        'tags'       => 'array',
        'confidence' => 'array',
        'decided_at' => 'datetime',
    ];
}