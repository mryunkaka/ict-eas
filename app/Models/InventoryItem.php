<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryItem extends Model
{
    protected $fillable = [
        'unit_id',
        'scope',
        'code',
        'name',
        'category',
        'specification',
        'quantity_on_hand',
        'minimum_quantity',
        'location',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'specification' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
