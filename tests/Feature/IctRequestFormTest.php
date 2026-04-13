<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\IctRequest;
use App\Models\IctRequestPpnkDocument;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class IctRequestFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_ict_request_form_can_be_rendered_with_default_category(): void
    {
        $unit = Unit::create(['code' => 'UNIT-01', 'name' => 'Unit 01', 'type' => 'unit', 'is_active' => true]);
        $user = User::factory()->create([
            'unit_id' => $unit->id,
            'role' => UserRole::AdminIct,
            'name' => 'Admin ICT Unit',
            'job_title' => 'Admin ICT',
        ]);
        User::factory()->create([
            'unit_id' => $unit->id,
            'role' => UserRole::StaffIct,
            'name' => 'Staff ICT Unit',
            'job_title' => 'Staff ICT',
        ]);

        $response = $this->actingAs($user)->get(route('forms.ict-requests.create'));

        $response->assertOk();
        $response->assertSee('value="hardware"', false);
        $response->assertSee('value="Staff ICT Unit"', false);
        $response->assertSee('value="Staff ICT"', false);
        $response->assertDontSee('value="Admin ICT Unit"', false);
        $response->assertSee('Permohonan Pembuatan PTA');
        $response->assertSee('Penawaran Vendor');
        $response->assertSee('Global 1 Form');
        $response->assertSee('Tambah Barang');
        $response->assertDontSee('Subjek Permintaan');
        $response->assertDontSee('Dibutuhkan Tanggal');
    }

    public function test_legacy_unit_user_role_can_access_ict_request_pages(): void
    {
        $unit = Unit::create(['code' => 'UNIT-01', 'name' => 'Unit 01', 'type' => 'unit', 'is_active' => true]);
        $user = User::factory()->create(['unit_id' => $unit->id, 'role' => UserRole::UnitUser]);

        $this->actingAs($user)
            ->get(route('forms.ict-requests.index'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('forms.ict-requests.create'))
            ->assertOk();
    }

    public function test_latest_pta_profile_is_prefilled_for_the_requester(): void
    {
        $unit = Unit::create(['code' => 'UNIT-01', 'name' => 'Unit 01', 'type' => 'unit', 'is_active' => true]);
        $user = User::factory()->create(['unit_id' => $unit->id, 'role' => UserRole::AdminIct]);

        IctRequest::create([
            'unit_id' => $unit->id,
            'requester_id' => $user->id,
            'subject' => 'Hardware - Laptop',
            'request_category' => 'hardware',
            'priority' => 'normal',
            'status' => 'drafted',
            'needed_at' => now()->toDateString(),
            'is_pta_request' => true,
            'justification' => 'Kebutuhan operasional',
            'additional_budget_reason' => 'Tambahan budget lama',
            'pta_budget_not_listed_reason' => 'Belum masuk RKAP',
            'pta_additional_budget_reason' => 'Ada kebutuhan mendadak',
            'drafted_by_name' => 'Ali',
            'drafted_by_title' => 'Staff ICT',
            'acknowledged_by_name' => 'Budi',
            'acknowledged_by_title' => 'Supervisor',
            'approved_1_name' => 'Cici',
            'approved_1_title' => 'Manager',
            'approved_2_name' => 'Dedi',
            'approved_2_title' => 'GM',
            'approved_3_name' => 'Eka',
            'approved_3_title' => 'Director',
            'approved_4_name' => 'Feri',
            'approved_4_title' => 'President Director',
        ]);

        $response = $this->actingAs($user)->get(route('forms.ict-requests.create'));

        $response->assertOk();
        $response->assertSee('value="'.$user->name.'"', false);
        $response->assertSee('Belum masuk RKAP');
        $response->assertSee('Ada kebutuhan mendadak');
        $response->assertSee('value="Eka"', false);
        $response->assertSee('value="Feri"', false);
    }

    public function test_latest_approval_profile_is_prefilled_from_the_logged_in_unit(): void
    {
        $unit = Unit::create(['code' => 'UNIT-01', 'name' => 'Unit 01', 'type' => 'unit', 'is_active' => true]);
        $otherUnit = Unit::create(['code' => 'UNIT-02', 'name' => 'Unit 02', 'type' => 'unit', 'is_active' => true]);

        $user = User::factory()->create(['unit_id' => $unit->id, 'role' => UserRole::AdminIct]);
        $otherUser = User::factory()->create(['unit_id' => $otherUnit->id, 'role' => UserRole::AdminIct]);

        IctRequest::create([
            'unit_id' => $otherUnit->id,
            'requester_id' => $otherUser->id,
            'subject' => 'Hardware - Switch',
            'request_category' => 'hardware',
            'priority' => 'normal',
            'status' => 'drafted',
            'needed_at' => now()->toDateString(),
            'justification' => 'Kebutuhan unit lain',
            'acknowledged_by_name' => 'Unit Lain Head',
            'acknowledged_by_title' => 'Head Lain',
            'approved_1_name' => 'GA Unit Lain',
            'approved_1_title' => 'GA Lain',
            'approved_2_name' => 'ICT Unit Lain',
            'approved_2_title' => 'ICT Lain',
        ]);

        IctRequest::create([
            'unit_id' => $unit->id,
            'requester_id' => $otherUser->id,
            'subject' => 'Hardware - Monitor',
            'request_category' => 'hardware',
            'priority' => 'normal',
            'status' => 'drafted',
            'needed_at' => now()->toDateString(),
            'justification' => 'Kebutuhan unit ini',
            'acknowledged_by_name' => 'Budi Head',
            'acknowledged_by_title' => 'Div. Head',
            'approved_1_name' => 'Santi GA',
            'approved_1_title' => 'Div. Head GA/FAT',
            'approved_2_name' => 'Rama ICT',
            'approved_2_title' => 'Div. Head ICT',
        ]);

        $response = $this->actingAs($user)->get(route('forms.ict-requests.create'));

        $response->assertOk();
        $response->assertSee('Nama Div. Head');
        $response->assertSee('value="Budi Head"', false);
        $response->assertSee('value="Div. Head"', false);
        $response->assertSee('value="Santi GA"', false);
        $response->assertSee('value="Div. Head GA/FAT"', false);
        $response->assertSee('value="Rama ICT"', false);
        $response->assertSee('value="Div. Head ICT"', false);
        $response->assertDontSee('Unit Lain Head');
    }

    public function test_ict_request_can_store_multiple_items_with_global_vendor_quotations(): void
    {
        Storage::fake('public');

        $unit = Unit::create(['code' => 'UNIT-01', 'name' => 'Unit 01', 'type' => 'unit', 'is_active' => true]);
        $user = User::factory()->create(['unit_id' => $unit->id, 'role' => UserRole::AdminIct]);

        $response = $this->actingAs($user)->post(route('forms.ict-requests.store'), [
            'request_category' => 'hardware',
            'priority' => 'normal',
            'quotation_mode' => 'global',
            'justification' => 'Kebutuhan operasional',
            'global_quotations' => [
                [
                    'vendor_name' => 'Vendor A',
                    'attachment' => UploadedFile::fake()->create('vendor-a.pdf', 300, 'application/pdf'),
                ],
                [
                    'vendor_name' => 'Vendor B',
                    'attachment' => UploadedFile::fake()->create('vendor-b.pdf', 250, 'application/pdf'),
                ],
                [
                    'vendor_name' => 'Vendor C',
                ],
            ],
            'items' => [
                [
                    'item_category' => 'Laptop/Notebook',
                    'item_name' => 'Laptop',
                    'brand_type' => 'ThinkPad',
                    'quantity' => 2,
                    'unit' => 'unit',
                    'estimated_price' => 15000000,
                    'item_notes' => 'Untuk tim operasional',
                    'photo_name' => 'laptopbaru',
                    'photo' => UploadedFile::fake()->image('laptop.jpg')->size(400),
                ],
                [
                    'item_category' => 'Jaringan',
                    'item_name' => 'Kabel LAN',
                    'brand_type' => 'CAT6',
                    'quantity' => 3,
                    'unit' => 'roll',
                    'estimated_price' => 750000,
                    'item_notes' => 'Cadangan instalasi',
                    'photo_name' => 'kabel',
                ],
            ],
        ]);

        $response->assertRedirect(route('forms.ict-requests.index'));

        $this->assertDatabaseCount('ict_requests', 1);
        $this->assertDatabaseCount('ict_request_items', 2);
        $this->assertDatabaseCount('ict_request_quotations', 2);

        $request = IctRequest::query()->with(['items', 'quotations'])->firstOrFail();

        $this->assertSame('Hardware - Laptop', $request->subject);
        $this->assertSame('global', $request->quotation_mode);
        $this->assertSame('Laptop/Notebook', $request->items[0]->item_category);
        $this->assertSame('unit', $request->items[0]->unit);
        $this->assertSame('laptopbaru', $request->items[0]->photo_name);
        $this->assertNotNull($request->items[0]->photo_path);
        $this->assertSame('roll', $request->items[1]->unit);
        $this->assertNull($request->items[1]->photo_path);
        $this->assertNull($request->quotations[0]->ict_request_item_id);
        $this->assertSame('Vendor A', $request->quotations[0]->vendor_name);

        Storage::disk('public')->assertExists($request->items[0]->photo_path);
        Storage::disk('public')->assertExists($request->quotations[0]->attachment_path);
        Storage::disk('public')->assertExists($request->quotations[1]->attachment_path);
    }

    public function test_ict_request_can_store_per_item_vendor_quotations(): void
    {
        Storage::fake('public');

        $unit = Unit::create(['code' => 'UNIT-01', 'name' => 'Unit 01', 'type' => 'unit', 'is_active' => true]);
        $user = User::factory()->create(['unit_id' => $unit->id, 'role' => UserRole::AdminIct]);

        $response = $this->actingAs($user)->post(route('forms.ict-requests.store'), [
            'request_category' => 'hardware',
            'priority' => 'urgent',
            'quotation_mode' => 'per_item',
            'justification' => 'Pengadaan mendesak',
            'items' => [
                [
                    'item_category' => 'Printer',
                    'item_name' => 'Printer Laser',
                    'brand_type' => 'HP',
                    'quantity' => 1,
                    'unit' => 'unit',
                    'estimated_price' => 5000000,
                    'quotations' => [
                        [
                            'vendor_name' => 'Vendor Print 1',
                            'attachment' => UploadedFile::fake()->create('print-1.pdf', 200, 'application/pdf'),
                        ],
                        [
                            'vendor_name' => 'Vendor Print 2',
                            'attachment' => UploadedFile::fake()->create('print-2.pdf', 180, 'application/pdf'),
                        ],
                        [
                            'vendor_name' => 'Vendor Print 3',
                        ],
                    ],
                ],
            ],
        ]);

        $response->assertRedirect(route('forms.ict-requests.index'));

        $request = IctRequest::query()->with(['items.quotations', 'quotations'])->firstOrFail();

        $this->assertSame('per_item', $request->quotation_mode);
        $this->assertCount(1, $request->items);
        $this->assertCount(2, $request->quotations);
        $this->assertSame($request->items[0]->id, $request->quotations[0]->ict_request_item_id);
        $this->assertSame('Vendor Print 1', $request->items[0]->quotations[0]->vendor_name);
        Storage::disk('public')->assertExists($request->items[0]->quotations[0]->attachment_path);
    }

    public function test_vendor_pdf_with_same_filename_reuses_existing_stored_file(): void
    {
        Storage::fake('public');

        $unit = Unit::create(['code' => 'UNIT-01', 'name' => 'Unit 01', 'type' => 'unit', 'is_active' => true]);
        $user = User::factory()->create(['unit_id' => $unit->id, 'role' => UserRole::AdminIct]);

        $firstResponse = $this->actingAs($user)->post(route('forms.ict-requests.store'), [
            'request_category' => 'hardware',
            'priority' => 'normal',
            'quotation_mode' => 'global',
            'justification' => 'Kebutuhan pertama',
            'global_quotations' => [
                [
                    'vendor_name' => 'Vendor A',
                    'attachment' => UploadedFile::fake()->create('harga-laptop.pdf', 300, 'application/pdf'),
                ],
                ['vendor_name' => 'Vendor B'],
                ['vendor_name' => 'Vendor C'],
            ],
            'items' => [
                [
                    'item_category' => 'Laptop/Notebook',
                    'item_name' => 'Laptop A',
                    'quantity' => 1,
                    'unit' => 'unit',
                ],
            ],
        ]);

        $firstResponse->assertRedirect(route('forms.ict-requests.index'));

        $firstRequest = IctRequest::query()->with('quotations')->firstOrFail();
        $firstPath = $firstRequest->quotations->firstOrFail()->attachment_path;

        $secondResponse = $this->actingAs($user)->post(route('forms.ict-requests.store'), [
            'request_category' => 'hardware',
            'priority' => 'normal',
            'quotation_mode' => 'global',
            'justification' => 'Kebutuhan kedua',
            'global_quotations' => [
                [
                    'vendor_name' => 'Vendor D',
                    'attachment' => UploadedFile::fake()->create('harga-laptop.pdf', 320, 'application/pdf'),
                ],
                ['vendor_name' => 'Vendor E'],
                ['vendor_name' => 'Vendor F'],
            ],
            'items' => [
                [
                    'item_category' => 'Laptop/Notebook',
                    'item_name' => 'Laptop B',
                    'quantity' => 1,
                    'unit' => 'unit',
                ],
            ],
        ]);

        $secondResponse->assertRedirect(route('forms.ict-requests.index'));

        $secondRequest = IctRequest::query()->latest('id')->with('quotations')->firstOrFail();
        $secondPath = $secondRequest->quotations->firstOrFail()->attachment_path;

        $this->assertSame($firstPath, $secondPath);
        $this->assertCount(1, Storage::disk('public')->files('ict-request-quotations'));
    }

    public function test_ict_request_can_be_updated_and_revision_number_increases(): void
    {
        Storage::fake('public');

        $unit = Unit::create(['code' => 'UNIT-01', 'name' => 'Unit 01', 'type' => 'unit', 'is_active' => true]);
        $user = User::factory()->create(['unit_id' => $unit->id, 'role' => UserRole::AdminIct]);

        $request = IctRequest::create([
            'unit_id' => $unit->id,
            'requester_id' => $user->id,
            'subject' => 'Hardware - Laptop',
            'request_category' => 'hardware',
            'priority' => 'normal',
            'status' => 'drafted',
            'needed_at' => now()->toDateString(),
            'quotation_mode' => 'global',
            'justification' => 'Kebutuhan awal',
            'revision_number' => 0,
        ]);

        $request->items()->create([
            'line_number' => 1,
            'item_name' => 'Laptop Lama',
            'brand_type' => 'ThinkPad',
            'unit' => 'unit',
            'quantity' => 1,
            'estimated_price' => 10000000,
            'notes' => 'Catatan lama',
        ]);

        $response = $this->actingAs($user)->put(route('forms.ict-requests.update', $request), [
            'request_category' => 'hardware',
            'priority' => 'urgent',
            'quotation_mode' => 'global',
            'justification' => 'Kebutuhan revisi',
            'acknowledged_by_name' => 'Div Head Baru',
            'acknowledged_by_title' => 'Div. Head',
            'approved_1_name' => 'GA Baru',
            'approved_1_title' => 'Div. Head GA/FAT',
            'approved_2_name' => 'ICT Baru',
            'approved_2_title' => 'Div. Head ICT',
            'items' => [
                [
                    'item_name' => 'Laptop Revisi',
                    'item_category' => 'Laptop/Notebook',
                    'brand_type' => 'EliteBook',
                    'quantity' => 2,
                    'unit' => 'unit',
                    'estimated_price' => 15000000,
                    'item_notes' => 'Catatan baru',
                    'photo_name' => 'laptoprev',
                ],
            ],
            'global_quotations' => [
                ['vendor_name' => 'Vendor A'],
                ['vendor_name' => 'Vendor B'],
                ['vendor_name' => 'Vendor C'],
            ],
        ]);

        $response->assertRedirect(route('forms.ict-requests.index'));

        $request->refresh();
        $request->load('items');

        $this->assertSame(1, $request->revision_number);
        $this->assertSame('urgent', $request->priority);
        $this->assertSame('Hardware - Laptop Revisi', $request->subject);
        $this->assertSame('Kebutuhan revisi', $request->justification);
        $this->assertSame('Div Head Baru', $request->acknowledged_by_name);
        $this->assertCount(1, $request->items);
        $this->assertSame('Laptop Revisi', $request->items[0]->item_name);
    }

    public function test_revision_history_attachment_remains_available_after_request_is_updated(): void
    {
        Storage::fake('public');

        $unit = Unit::create(['code' => 'UNIT-01', 'name' => 'Unit 01', 'type' => 'unit', 'is_active' => true]);
        $admin = User::factory()->create(['unit_id' => $unit->id, 'role' => UserRole::AdminIct]);
        $staff = User::factory()->create(['unit_id' => $unit->id, 'role' => UserRole::StaffIct]);

        $request = IctRequest::create([
            'unit_id' => $unit->id,
            'requester_id' => $admin->id,
            'subject' => 'Hardware - Monitor',
            'request_category' => 'hardware',
            'priority' => 'normal',
            'status' => 'drafted',
            'needed_at' => now()->toDateString(),
            'quotation_mode' => 'global',
            'justification' => 'Kebutuhan awal',
            'revision_number' => 0,
        ]);

        $request->items()->create([
            'line_number' => 1,
            'item_name' => 'Monitor',
            'quantity' => 1,
            'unit' => 'unit',
        ]);

        $this->actingAs($staff)
            ->post(route('approvals.ict.update', $request), [
                'action' => 'revise',
                'review_note' => 'Mohon perbaiki spesifikasi.',
                'revision_attachment' => UploadedFile::fake()->image('revisi-monitor.png', 1800, 1200),
            ])
            ->assertRedirect();

        $request->refresh();
        $history = $request->reviewHistories()->latest('id')->firstOrFail();

        $this->assertNotNull($request->revision_attachment_path);
        $this->assertNotNull($history->attachment_path);
        $this->assertNotSame($request->revision_attachment_path, $history->attachment_path);
        Storage::disk('public')->assertExists($history->attachment_path);

        $this->actingAs($admin)->put(route('forms.ict-requests.update', $request), [
            'request_category' => 'hardware',
            'priority' => 'normal',
            'quotation_mode' => 'global',
            'justification' => 'Kebutuhan revisi',
            'items' => [
                [
                    'item_name' => 'Monitor Baru',
                    'item_category' => 'Monitor',
                    'brand_type' => 'Lenovo',
                    'quantity' => 1,
                    'unit' => 'unit',
                    'estimated_price' => 2500000,
                    'item_notes' => 'Sudah direvisi',
                    'photo_name' => 'monitor-baru',
                ],
            ],
            'global_quotations' => [
                ['vendor_name' => 'Vendor A'],
                ['vendor_name' => 'Vendor B'],
                ['vendor_name' => 'Vendor C'],
            ],
        ])->assertRedirect(route('forms.ict-requests.index'));

        Storage::disk('public')->assertExists($history->attachment_path);
    }

    public function test_checked_by_asmen_request_cannot_be_edited_anymore(): void
    {
        $unit = Unit::create(['code' => 'UNIT-01', 'name' => 'Unit 01', 'type' => 'unit', 'is_active' => true]);
        $user = User::factory()->create(['unit_id' => $unit->id, 'role' => UserRole::AdminIct]);

        $request = IctRequest::create([
            'unit_id' => $unit->id,
            'requester_id' => $user->id,
            'subject' => 'Hardware - Monitor',
            'request_category' => 'hardware',
            'priority' => 'normal',
            'status' => 'checked_by_asmen',
            'needed_at' => now()->toDateString(),
            'quotation_mode' => 'global',
            'justification' => 'Locked after asmen',
        ]);

        $this->actingAs($user)
            ->get(route('forms.ict-requests.edit', $request))
            ->assertForbidden();
    }

    public function test_progress_ppnk_request_reuses_one_document_for_items_with_same_number(): void
    {
        Storage::fake('public');

        $unit = Unit::create(['code' => 'UNIT-01', 'name' => 'Unit 01', 'type' => 'unit', 'is_active' => true]);
        $user = User::factory()->create(['unit_id' => $unit->id, 'role' => UserRole::AdminIct]);

        $request = IctRequest::create([
            'unit_id' => $unit->id,
            'requester_id' => $user->id,
            'subject' => 'Hardware - Monitor',
            'request_category' => 'hardware',
            'priority' => 'normal',
            'status' => 'progress_ppnk',
            'needed_at' => now()->toDateString(),
            'quotation_mode' => 'global',
            'justification' => 'Lanjut PPNK',
            'final_signed_pdf_name' => 'signed.pdf',
            'final_signed_pdf_path' => 'ict-request-signed/signed.pdf',
        ]);

        $firstItem = $request->items()->create([
            'line_number' => 1,
            'item_name' => 'Monitor 1',
            'quantity' => 1,
            'unit' => 'unit',
        ]);

        $secondItem = $request->items()->create([
            'line_number' => 2,
            'item_name' => 'Monitor 2',
            'quantity' => 1,
            'unit' => 'unit',
        ]);

        $this->actingAs($user)
            ->post(route('forms.ict-requests.ppnk.store', $request), [
                'items' => [
                    [
                        'item_id' => $firstItem->id,
                        'ppnk_number' => 'PPNK-001/ICT/2026',
                        'ppnk_attachment' => UploadedFile::fake()->create('ppnk-001.pdf', 300, 'application/pdf'),
                    ],
                    [
                        'item_id' => $secondItem->id,
                        'ppnk_number' => 'PPNK-001/ICT/2026',
                    ],
                ],
            ])
            ->assertRedirect();

        $request->refresh();
        $request->load('items.ppnkDocument');

        $this->assertDatabaseCount('ict_request_ppnk_documents', 1);
        $document = IctRequestPpnkDocument::query()->firstOrFail();

        $this->assertSame('PPNK-001/ICT/2026', $document->ppnk_number);
        $this->assertSame($document->id, $request->items[0]->ppnk_document_id);
        $this->assertSame($document->id, $request->items[1]->ppnk_document_id);
        Storage::disk('public')->assertExists($document->attachment_path);
        $this->assertCount(1, Storage::disk('public')->files('ict-request-ppnk'));
    }

    public function test_ict_request_pdf_can_be_rendered_from_detail_view(): void
    {
        Storage::fake('public');

        $unit = Unit::create(['code' => 'UNIT-01', 'name' => 'Unit 01', 'type' => 'unit', 'is_active' => true]);
        $user = User::factory()->create(['unit_id' => $unit->id, 'role' => UserRole::UnitUser]);

        $request = IctRequest::create([
            'unit_id' => $unit->id,
            'requester_id' => $user->id,
            'subject' => 'Hardware - Laptop',
            'request_category' => 'hardware',
            'priority' => 'normal',
            'status' => 'drafted',
            'needed_at' => now()->toDateString(),
            'quotation_mode' => 'global',
            'justification' => 'Kebutuhan operasional',
            'drafted_by_name' => 'Ali',
            'drafted_by_title' => 'Staff ICT',
            'acknowledged_by_name' => 'Budi',
            'acknowledged_by_title' => 'Supervisor',
            'approved_1_name' => 'Cici',
            'approved_1_title' => 'Manager',
            'approved_2_name' => 'Dedi',
            'approved_2_title' => 'Manager ICT',
        ]);

        $request->items()->create([
            'line_number' => 1,
            'item_name' => 'Laptop',
            'brand_type' => 'ThinkPad',
            'unit' => 'unit',
            'quantity' => 2,
            'estimated_price' => 15000000,
            'notes' => 'Untuk tim operasional',
            'photo_name' => 'laptop',
            'photo_path' => UploadedFile::fake()->image('laptop.jpg')->store('ict-request-items', 'public'),
        ]);

        $this->actingAs($user)
            ->get(route('forms.ict-requests.pdf', $request))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}
