<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Asset;
use App\Models\AssetHandover;
use App\Models\IctRequest;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class IctRequestProcurementWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_ict_request_can_complete_through_procurement_and_goods_receipt(): void
    {
        Storage::fake('public');

        $unit = Unit::create([
            'code' => 'UNIT-01',
            'name' => 'Unit 01',
            'type' => 'unit',
            'is_active' => true,
        ]);

        $requester = User::factory()->create(['unit_id' => $unit->id, 'role' => UserRole::UnitUser]);
        $staff = User::factory()->create(['unit_id' => $unit->id, 'role' => UserRole::StaffIct]);
        $asmen = User::factory()->create(['unit_id' => $unit->id, 'role' => UserRole::AsmenIct]);
        $admin = User::factory()->create(['unit_id' => $unit->id, 'role' => UserRole::AdminIct]);

        $this->actingAs($requester)
            ->post(route('forms.ict-requests.store'), [
                'requester_name' => 'Pemohon Procurement',
                'department_name' => 'Unit 01',
                'needed_at' => '2026-04-16',
                'request_category' => 'hardware',
                'priority' => 'normal',
                'quotation_mode' => 'global',
                'justification' => 'Pengadaan perangkat kerja',
                'items' => [
                    [
                        'item_category' => 'Laptop/Notebook',
                        'item_name' => 'Laptop',
                        'brand_type' => 'ThinkPad',
                        'quantity' => 1,
                        'unit' => 'unit',
                        'estimated_price' => 15000000,
                        'item_notes' => 'Untuk operasional',
                    ],
                ],
            ])
            ->assertRedirect(route('forms.ict-requests.index'));

        $ictRequest = IctRequest::query()->with('items')->firstOrFail();
        $this->assertSame('drafted', $ictRequest->status);
        $this->assertCount(1, $ictRequest->items);

        $this->actingAs($staff)
            ->post(route('approvals.ict.update', $ictRequest), ['action' => 'approve'])
            ->assertRedirect();
        $ictRequest->refresh();
        $this->assertSame('ttd_in_progress', $ictRequest->status);

        $this->actingAs($asmen)
            ->post(route('approvals.ict.update', $ictRequest), ['action' => 'approve'])
            ->assertRedirect();
        $ictRequest->refresh();
        $this->assertSame('checked_by_asmen', $ictRequest->status);

        $this->actingAs($admin)
            ->post(route('approvals.ict.update', $ictRequest), [
                'action' => 'upload_signed_pdf',
                'signed_pdf' => UploadedFile::fake()->create('signed.pdf', 200, 'application/pdf'),
            ])
            ->assertRedirect();
        $ictRequest->refresh();
        $this->assertSame('progress_ppnk', $ictRequest->status);
        $this->assertNotNull($ictRequest->final_signed_pdf_path);
        Storage::disk('public')->assertExists($ictRequest->final_signed_pdf_path);

        $item = $ictRequest->items->firstOrFail();

        $this->actingAs($admin)
            ->post(route('forms.ict-requests.ppnk.store', $ictRequest), [
                'items' => [
                    [
                        'item_id' => $item->id,
                        'ppnk_number' => 'PPNK-001',
                        'ppnk_attachment' => UploadedFile::fake()->create('ppnk-001.pdf', 150, 'application/pdf'),
                    ],
                ],
            ])
            ->assertRedirect();
        $ictRequest->refresh();
        $this->assertSame('progress_verifikasi_audit', $ictRequest->status);

        $this->actingAs($admin)
            ->post(route('forms.ict-requests.verify-audit', $ictRequest), [
                'items' => [
                    [
                        'item_id' => $item->id,
                        'audit_status' => 'approved',
                        'audit_reason' => '',
                    ],
                ],
            ])
            ->assertRedirect();
        $ictRequest->refresh();
        $this->assertSame('progress_ppm', $ictRequest->status);

        $this->actingAs($admin)
            ->post(route('forms.ict-requests.ppm.store', $ictRequest), [
                'items' => [
                    [
                        'item_id' => $item->id,
                        'line_number' => 1,
                        'ppm_number' => 'PPM-001',
                        'pr_number' => 'PR-001',
                        'ppm_attachment' => UploadedFile::fake()->create('ppm-001.pdf', 150, 'application/pdf'),
                    ],
                ],
            ])
            ->assertRedirect();
        $ictRequest->refresh();
        $this->assertSame('progress_po', $ictRequest->status);

        $this->actingAs($admin)
            ->post(route('forms.ict-requests.po.store', $ictRequest), [
                'items' => [
                    [
                        'item_id' => $item->id,
                        'po_number' => 'PO-001',
                        'po_attachment' => UploadedFile::fake()->create('po-001.pdf', 150, 'application/pdf'),
                    ],
                ],
            ])
            ->assertRedirect();
        $ictRequest->refresh();
        $this->assertSame('progress_waiting_goods', $ictRequest->status);

        $this->actingAs($admin)
            ->post(route('forms.ict-requests.goods-receipt.store', $ictRequest), [
                'items' => [
                    [
                        'item_id' => $item->id,
                        'handover_type' => 'asset',
                        'dept' => 'Finance',
                        'model_specification' => 'ThinkPad X1 Carbon Gen 12',
                        'serial_number' => 'SN-001',
                        'asset_number' => 'AST-001',
                        'recipient_name' => 'User Finance',
                        'recipient_position' => 'Staff',
                        'witness_name' => 'ICT Staff',
                        'witness_position' => 'Staff ICT',
                        'deliverer_name' => 'HRGA',
                        'deliverer_position' => 'Officer',
                    ],
                ],
            ])
            ->assertRedirect();

        $ictRequest->refresh();
        $this->assertSame('completed', $ictRequest->status);

        $this->assertDatabaseCount('assets', 1);
        $asset = Asset::query()->firstOrFail();
        $this->assertSame($unit->id, $asset->unit_id);
        $this->assertSame('Laptop', $asset->name);
        $this->assertSame('AST-001', $asset->asset_number);
        $this->assertSame('SN-001', $asset->serial_number);

        $this->assertDatabaseCount('asset_handovers', 1);
        $handover = AssetHandover::query()->firstOrFail();
        $this->assertSame($ictRequest->id, $handover->ict_request_id);
        $this->assertSame($item->id, $handover->ict_request_item_id);
        $this->assertSame($asset->id, $handover->asset_id);
        $this->assertNotNull($handover->handover_report_path);
        Storage::disk('public')->assertExists($handover->handover_report_path);
    }
}
