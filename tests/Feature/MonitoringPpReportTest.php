<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\IctRequest;
use App\Models\IctRequestItem;
use App\Models\IctRequestPoDocument;
use App\Models\IctRequestPpmDocument;
use App\Models\IctRequestPpnkDocument;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MonitoringPpReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_unit_scoped_user_only_sees_monitoring_data_from_own_unit(): void
    {
        [$unitA, $userA, $itemA] = $this->createMonitoringItem('UNIT-A', 'Unit A', UserRole::AdminIct, 'Laptop A');
        [, , $itemB] = $this->createMonitoringItem('UNIT-B', 'Unit B', UserRole::AdminIct, 'Laptop B');

        $response = $this->actingAs($userA)->get(route('reports.monitoring-pp', [
            'unit_id' => $itemB->ictRequest->unit_id,
        ]));

        $response->assertOk();
        $response->assertSee('Laptop A');
        $response->assertDontSee('Laptop B');
        $response->assertDontSee('Semua Unit');
    }

    public function test_asmen_can_view_all_units_in_monitoring_pp(): void
    {
        [$unitA] = $this->createMonitoringItem('UNIT-A', 'Unit A', UserRole::AdminIct, 'Laptop A');
        [$unitB] = $this->createMonitoringItem('UNIT-B', 'Unit B', UserRole::AdminIct, 'Laptop B');
        $asmen = User::factory()->create([
            'unit_id' => $unitA->id,
            'role' => UserRole::AsmenIct,
            'name' => 'Asmen ICT',
        ]);

        $response = $this->actingAs($asmen)->get(route('reports.monitoring-pp'));

        $response->assertOk();
        $response->assertSee('Laptop A');
        $response->assertSee('Laptop B');
        $response->assertSee('Semua Unit');

        $filtered = $this->actingAs($asmen)->get(route('reports.monitoring-pp', ['unit_id' => $unitB->id]));

        $filtered->assertOk();
        $filtered->assertDontSee('Laptop A');
        $filtered->assertSee('Laptop B');
    }

    public function test_import_monitoring_pp_keeps_ppnk_ppm_and_po_documents_empty(): void
    {
        Storage::fake('public');

        $unit = Unit::create([
            'code' => 'UNIT-IMP',
            'name' => 'Unit Import',
            'type' => 'unit',
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'unit_id' => $unit->id,
            'role' => UserRole::AdminIct,
            'name' => 'Importer ICT',
        ]);

        $csv = implode("\n", [
            'Form ICT,Tanggal Form ICT,Jenis Barang,Nama Barang,Merk,Harga,Keterangan,Status,Tanggal PPNK/PPK,No PPNK/PPK,Nama PPM,Tanggal PPM/PR,No PPM,No PR,Tanggal PO,No PO,Tanggal Diterima,Tanggal Pembuatan BA',
            'ICT-IMP-001,2026-04-01,Laptop,ThinkPad X1,Lenovo,15000000,Import test,completed,2026-04-01,PPNK-001,PPM April,2026-04-01,PPM-001,PR-001,2026-04-03,PO-001,2026-04-03,2026-04-04',
        ]);

        $file = UploadedFile::fake()->createWithContent('monitoring-pp.csv', $csv);

        $response = $this->actingAs($user)->post(route('reports.monitoring-pp.import-excel'), [
            'import_file' => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $item = IctRequestItem::query()->with('ictRequest')->sole();

        $this->assertNull($item->ppnk_document_id);
        $this->assertNull($item->ppm_document_id);
        $this->assertNull($item->po_document_id);
        $this->assertSame('PPNK-001', $item->ppnk_number);
        $this->assertSame('2026-04-01', $item->ppnk_uploaded_at?->toDateString());
        $this->assertSame('PPM April', $item->ppm_name);
        $this->assertSame('PPM-001', $item->ppm_number);
        $this->assertSame('2026-04-01', $item->ppm_uploaded_at?->toDateString());
        $this->assertSame('PR-001', $item->pr_number);
        $this->assertSame('PO-001', $item->po_number);
        $this->assertSame('2026-04-03', $item->po_uploaded_at?->toDateString());
        $this->assertSame('UNIT-IMP-FORM ICT-001', $item->ictRequest->form_number);
        $this->assertSame('UNIT-IMP-FORM ICT-001', $item->ictRequest->subject);
        $this->assertSame(0, IctRequestPpnkDocument::query()->count());
        $this->assertSame(0, IctRequestPpmDocument::query()->count());
        $this->assertSame(0, IctRequestPoDocument::query()->count());
    }

    /**
     * @return array{0: Unit, 1: User, 2: IctRequestItem}
     */
    protected function createMonitoringItem(string $code, string $unitName, UserRole $role, string $itemName): array
    {
        $unit = Unit::create([
            'code' => $code,
            'name' => $unitName,
            'type' => 'unit',
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'unit_id' => $unit->id,
            'role' => $role,
            'name' => $unitName.' User',
        ]);

        $request = IctRequest::create([
            'unit_id' => $unit->id,
            'requester_id' => $user->id,
            'form_number' => 'F-'.$code,
            'subject' => 'Permintaan '.$itemName,
            'request_category' => 'hardware',
            'priority' => 'normal',
            'status' => 'progress_ppnk',
            'justification' => 'Kebutuhan operasional',
        ]);

        $item = IctRequestItem::create([
            'ict_request_id' => $request->id,
            'line_number' => 1,
            'item_name' => $itemName,
            'item_category' => 'Laptop',
            'brand_type' => 'Lenovo',
            'quantity' => 1,
            'estimated_price' => 10000000,
        ]);

        return [$unit, $user, $item];
    }
}
