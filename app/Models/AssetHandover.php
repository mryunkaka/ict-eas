<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetHandover extends Model
{
    protected $fillable = [
        'ict_request_id',
        'ict_request_item_id',
        'unit_index',
        'asset_id',
        'handover_type',
        'received_at',
        'full_ttd_signed_at',
        'description',
        'serah_terima_path',
        'serah_terima_name',
        'serah_terima_size',
        'serah_terima_mime',
        'full_ttd_path',
        'full_ttd_name',
        'full_ttd_size',
        'full_ttd_mime',
        'surat_jalan_path',
        'surat_jalan_name',
        'surat_jalan_size',
        'surat_jalan_mime',
        'dept',
        'model_specification',
        'serial_number',
        'asset_number',
        'recipient_name',
        'recipient_position',
        'supervisor_name',
        'supervisor_position',
        'witness_name',
        'witness_position',
        'deliverer_name',
        'deliverer_position',
        'handover_report_path',
        'handover_report_name',
        'handover_report_generated_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'unit_index' => 'integer',
            'received_at' => 'datetime',
            'full_ttd_signed_at' => 'datetime',
            'serah_terima_size' => 'integer',
            'full_ttd_size' => 'integer',
            'surat_jalan_size' => 'integer',
            'handover_report_generated_at' => 'datetime',
        ];
    }

    public function ictRequest(): BelongsTo
    {
        return $this->belongsTo(IctRequest::class);
    }

    public function ictRequestItem(): BelongsTo
    {
        return $this->belongsTo(IctRequestItem::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
