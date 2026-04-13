<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IctRequestItem extends Model
{
    protected $fillable = [
        'ict_request_id',
        'line_number',
        'item_name',
        'item_category',
        'brand_type',
        'unit',
        'quantity',
        'estimated_price',
        'notes',
        'photo_name',
        'photo_path',
        'photo_size',
        'ppnk_document_id',
        'audit_status',
        'audit_reason',
    ];

    public function ictRequest(): BelongsTo
    {
        return $this->belongsTo(IctRequest::class);
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(IctRequestQuotation::class);
    }

    public function ppnkDocument(): BelongsTo
    {
        return $this->belongsTo(IctRequestPpnkDocument::class, 'ppnk_document_id');
    }
}
