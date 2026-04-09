<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncidentReport extends Model
{
    protected $fillable = [
        'unit_id',
        'reported_by_id',
        'asset_id',
        'repair_request_id',
        'incident_type',
        'title',
        'description',
        'follow_up',
        'repairable',
        'status',
        'occurred_at',
        'known_by_manager_id',
        'known_by_ict_head_id',
    ];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'repairable' => 'boolean',
        ];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by_id');
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }
}
