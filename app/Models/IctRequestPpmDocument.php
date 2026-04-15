<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IctRequestPpmDocument extends Model
{
    protected $fillable = [
        'ict_request_id',
        'ppm_number',
        'attachment_name',
        'attachment_path',
        'attachment_size',
        'attachment_mime',
        'uploaded_by',
        'uploaded_at',
    ];

    protected function casts(): array
    {
        return [
            'uploaded_at' => 'datetime',
            'attachment_size' => 'integer',
        ];
    }

    public function ictRequest(): BelongsTo
    {
        return $this->belongsTo(IctRequest::class);
    }

    public function ictRequestItems(): HasMany
    {
        return $this->hasMany(IctRequestItem::class, 'ppm_document_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
