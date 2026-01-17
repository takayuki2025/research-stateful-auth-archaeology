<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemEntity extends Model
{
    protected $table = 'item_entities';

    protected $fillable = [
        'item_id',
        'analysis_request_id',
        'review_decision_id',
        'replaced_item_entity_id',

        'brand_entity_id',
        'condition_entity_id',
        'color_entity_id',

        'confidence',
        'is_latest',
        'source',
        'generated_version',
        'decision_type',
        'generated_at',
    ];

    protected $casts = [
        'generated_at' => 'datetime',
        'confidence'   => 'array',
        'is_latest'    => 'boolean',
    ];

    /* =============================
       Relations
    ============================= */

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function reviewDecision(): BelongsTo
    {
        return $this->belongsTo(
            ReviewDecision::class,
            'review_decision_id'
        );
    }

    public function replacedFrom(): BelongsTo
    {
        return $this->belongsTo(
            self::class,
            'replaced_item_entity_id'
        );
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(BrandEntity::class, 'brand_entity_id');
    }

    public function condition(): BelongsTo
    {
        return $this->belongsTo(ConditionEntity::class, 'condition_entity_id');
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(ColorEntity::class, 'color_entity_id');
    }
}