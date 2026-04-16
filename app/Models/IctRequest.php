<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IctRequest extends Model
{
    public const STATUS_LABELS = [
        'drafted' => 'Draft Admin ICT',
        'ttd_in_progress' => 'Validated Staff ICT',
        'checked_by_asmen' => 'Validated Asmen ICT',
        'progress_ppnk' => 'Progress PPNK',
        'progress_verifikasi_audit' => 'Progress Verifikasi Audit',
        'progress_ppm' => 'Progress PPM',
        'progress_po' => 'Progress PO',
        'progress_waiting_goods' => 'Progress Menunggu Barang Diterima',
        'approved_by_manager' => 'Approved Manager ICT',
        'completed' => 'Barang Sudah Diterima',
        'needs_revision' => 'Perlu Revisi',
        'rejected' => 'Rejected',
    ];

    protected $fillable = [
        'unit_id',
        'requester_id',
        'requester_name',
        'department_name',
        'form_number',
        'revision_number',
        'print_count',
        'last_printed_at',
        'subject',
        'request_category',
        'priority',
        'status',
        'needed_at',
        'quotation_mode',
        'is_pta_request',
        'justification',
        'additional_budget_reason',
        'pta_budget_not_listed_reason',
        'pta_additional_budget_reason',
        'drafted_by_name',
        'drafted_by_title',
        'acknowledged_by_name',
        'acknowledged_by_title',
        'approved_1_name',
        'approved_1_title',
        'approved_2_name',
        'approved_2_title',
        'approved_3_name',
        'approved_3_title',
        'approved_4_name',
        'approved_4_title',
        'staff_validated_by',
        'staff_validated_at',
        'asmen_checked_by',
        'asmen_checked_at',
        'manager_approved_by',
        'manager_approved_at',
        'final_signed_pdf_name',
        'final_signed_pdf_path',
        'final_signed_pdf_uploaded_by',
        'final_signed_pdf_uploaded_at',
        'rejected_reason',
        'rejected_by',
        'rejected_at',
        'revision_note',
        'revision_attachment_name',
        'revision_attachment_path',
        'revision_attachment_size',
        'revision_attachment_mime',
        'revision_requested_by',
        'revision_requested_at',
    ];

    protected function casts(): array
    {
        return [
            'needed_at' => 'date',
            'revision_number' => 'integer',
            'print_count' => 'integer',
            'last_printed_at' => 'datetime',
            'quotation_mode' => 'string',
            'is_pta_request' => 'boolean',
            'staff_validated_at' => 'datetime',
            'asmen_checked_at' => 'datetime',
            'manager_approved_at' => 'datetime',
            'final_signed_pdf_uploaded_at' => 'datetime',
            'rejected_at' => 'datetime',
            'revision_attachment_size' => 'integer',
            'revision_requested_at' => 'datetime',
        ];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(IctRequestItem::class);
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(IctRequestQuotation::class);
    }

    public function ppnkDocuments(): HasMany
    {
        return $this->hasMany(IctRequestPpnkDocument::class)->latest('uploaded_at')->latest('id');
    }

    public function ppmDocuments(): HasMany
    {
        return $this->hasMany(IctRequestPpmDocument::class)->latest('uploaded_at')->latest('id');
    }

    public function poDocuments(): HasMany
    {
        return $this->hasMany(IctRequestPoDocument::class)->latest('uploaded_at')->latest('id');
    }

    public function assetHandovers(): HasMany
    {
        return $this->hasMany(AssetHandover::class);
    }

    public function reviewHistories(): HasMany
    {
        return $this->hasMany(IctRequestReviewHistory::class)->latest('reviewed_at')->latest('id');
    }

    public function statusLabel(): string
    {
        if ($this->status === 'checked_by_asmen' && (int) $this->print_count > 0 && ! $this->final_signed_pdf_path) {
            return 'Progress TTD';
        }

        return self::STATUS_LABELS[$this->status]
            ?? str($this->status)->replace('_', ' ')->title()->toString();
    }

    public function requesterDisplayName(): string
    {
        return (string) ($this->requester_name ?: $this->requester?->name ?: '-');
    }

    public function departmentDisplayName(): string
    {
        return (string) ($this->department_name ?: $this->unit?->name ?: '-');
    }
}
