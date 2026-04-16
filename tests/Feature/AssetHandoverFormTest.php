<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AssetHandover;
use App\Models\IctRequest;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AssetHandoverFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_manual_asset_handover_and_download_pdf(): void
    {
        Storage::fake('public');

        $unit = Unit::create(['code' => 'UNIT-01', 'name' => 'Unit 01', 'type' => 'unit', 'is_active' => true]);
        $admin = User::factory()->create(['unit_id' => $unit->id, 'role' => UserRole::AdminIct]);

        $ictRequest = IctRequest::create([
            'unit_id' => $unit->id,
            'requester_id' => $admin->id,
            'subject' => 'Laptop baru',
            'request_category' => 'hardware',
            'priority' => 'normal',
            'status' => 'progress_waiting_goods',
            'justification' => 'Kebutuhan operasional',
        ]);

        $item = $ictRequest->items()->create([
            'line_number' => 1,
            'item_name' => 'Laptop',
            'brand_type' => 'ThinkPad',
            'item_category' => 'hardware',
            'quantity' => 1,
        ]);

        $this->actingAs($admin)
            ->post(route('forms.asset-handovers.store'), [
                'ict_request_id' => $ictRequest->id,
                'ict_request_item_id' => $item->id,
                'handover_type' => 'asset',
                'dept' => 'Finance',
                'model_specification' => 'ThinkPad X1',
                'serial_number' => 'SN-123',
                'asset_number' => 'AST-123',
                'recipient_name' => 'User Finance',
                'recipient_position' => 'Staff',
                'witness_name' => 'ICT Staff',
                'witness_position' => 'Staff ICT',
                'deliverer_name' => 'HRGA',
                'deliverer_position' => 'Officer',
                'serah_terima' => UploadedFile::fake()->image('foto.jpg', 1200, 800),
                'surat_jalan' => UploadedFile::fake()->create('sj.pdf', 120, 'application/pdf'),
            ])
            ->assertRedirect(route('forms.asset-handovers.index'));

        $this->assertDatabaseCount('asset_handovers', 1);
        $handover = AssetHandover::query()->firstOrFail();
        $this->assertNotNull($handover->handover_report_path);
        Storage::disk('public')->assertExists($handover->handover_report_path);

        $this->actingAs($admin)
            ->get(route('forms.asset-handovers.pdf', $handover))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}

