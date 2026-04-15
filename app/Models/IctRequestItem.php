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
        'takeout_qty',
        'estimated_price',
        'notes',
        'photo_name',
        'photo_path',
        'photo_size',
        'ppnk_document_id',
        'ppm_document_id',
        'pr_number',
        'po_document_id',
        'audit_status',
        'audit_reason',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'takeout_qty' => 'integer',
            'estimated_price' => 'decimal:2',
        ];
    }

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

    public function ppmDocument(): BelongsTo
    {
        return $this->belongsTo(IctRequestPpmDocument::class, 'ppm_document_id');
    }

    public function poDocument(): BelongsTo
    {
        return $this->belongsTo(IctRequestPoDocument::class, 'po_document_id');
    }
}
