<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\EmailRequest;
use App\Models\IctRequest;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApprovalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_ict_request_can_move_through_manual_sign_approval_stages(): void
    {
        Storage::fake('public');

        $unit = Unit::create(['code' => 'UNIT-01', 'name' => 'Unit 01', 'type' => 'unit', 'is_active' => true]);
        $admin = User::factory()->create(['unit_id' => $unit->id, 'role' => UserRole::AdminIct]);
        $staff = User::factory()->create(['unit_id' => $unit->id, 'role' => UserRole::StaffIct]);
        $asmen = User::factory()->create(['unit_id' => $unit->id, 'role' => UserRole::AsmenIct]);

        $request = IctRequest::create([
            'unit_id' => $unit->id,
            'requester_id' => $admin->id,
            'subject' => 'Laptop baru',
            'request_category' => 'hardware',
            'priority' => 'normal',
            'status' => 'drafted',
            'justification' => 'Kebutuhan operasional',
        ]);

        $this->actingAs($staff)
            ->post(route('approvals.ict.update', $request), ['action' => 'approve'])
            ->assertRedirect();

        $request->refresh();
        $this->assertSame('ttd_in_progress', $request->status);

        $this->actingAs($asmen)
            ->post(route('approvals.ict.update', $request), ['action' => 'approve'])
            ->assertRedirect();

        $request->refresh();
        $this->assertSame('checked_by_asmen', $request->status);

        $this->actingAs($admin)
            ->post(route('approvals.ict.update', $request), [
                'action' => 'upload_signed_pdf',
                'signed_pdf' => UploadedFile::fake()->create('signed.pdf', 300, 'application/pdf'),
            ])
            ->assertRedirect();

        $request->refresh();
        $this->assertSame('progress_ppnk', $request->status);
        $this->assertNotNull($request->final_signed_pdf_path);
        Storage::disk('public')->assertExists($request->final_signed_pdf_path);
    }

    public function test_ict_request_reject_requires_reason(): void
    {
        $unit = Unit::create(['code' => 'UNIT-04', 'name' => 'Unit 04', 'type' => 'unit', 'is_active' => true]);
        $admin = User::factory()->create(['unit_id' => $unit->id, 'role' => UserRole::AdminIct]);
        $staff = User::factory()->create(['unit_id' => $unit->id, 'role' => UserRole::StaffIct]);

        $request = IctRequest::create([
            'unit_id' => $unit->id,
            'requester_id' => $admin->id,
            'subject' => 'Monitor baru',
            'request_category' => 'hardware',
            'priority' => 'normal',
            'status' => 'drafted',
            'justification' => 'Kebutuhan operasional',
        ]);

        $this->from(route('approvals.index'))
            ->actingAs($staff)
            ->post(route('approvals.ict.update', $request), ['action' => 'reject'])
            ->assertRedirect(route('approvals.index'))
            ->assertSessionHasErrors('review_note');
    }

    public function test_ict_request_can_be_sent_back_for_revision_with_compressed_image_attachment(): void
    {
        Storage::fake('public');

        $unit = Unit::create(['code' => 'UNIT-05', 'name' => 'Unit 05', 'type' => 'unit', 'is_active' => true]);
        $admin = User::factory()->create(['unit_id' => $unit->id, 'role' => UserRole::AdminIct]);
        $staff = User::factory()->create(['unit_id' => $unit->id, 'role' => UserRole::StaffIct]);

        $request = IctRequest::create([
            'unit_id' => $unit->id,
            'requester_id' => $admin->id,
            'subject' => 'Access point baru',
            'request_category' => 'hardware',
            'priority' => 'normal',
            'status' => 'drafted',
            'justification' => 'Perlu perluasan jaringan',
        ]);

        $this->actingAs($staff)
            ->post(route('approvals.ict.update', $request), [
                'action' => 'revise',
                'review_note' => 'Tambahkan detail spesifikasi dan pembanding harga.',
                'revision_attachment' => UploadedFile::fake()->image('catatan-revisi.png', 2400, 1800),
            ])
            ->assertRedirect();

        $request->refresh();

        $this->assertSame('needs_revision', $request->status);
        $this->assertSame('Tambahkan detail spesifikasi dan pembanding harga.', $request->revision_note);
        $this->assertSame('image/jpeg', $request->revision_attachment_mime);
        $this->assertNotNull($request->revision_attachment_path);
        Storage::disk('public')->assertExists($request->revision_attachment_path);
        $this->assertSame('jpg', pathinfo((string) $request->revision_attachment_name, PATHINFO_EXTENSION));
        $this->assertDatabaseHas('ict_request_review_histories', [
            'ict_request_id' => $request->id,
            'action' => 'revise',
            'note' => 'Tambahkan detail spesifikasi dan pembanding harga.',
        ]);
    }

    public function test_ict_request_print_first_time_is_original_and_next_print_is_copy(): void
    {
        Storage::fake('public');

        $unit = Unit::create(['code' => 'UNIT-03', 'name' => 'Unit 03', 'type' => 'unit', 'is_active' => true]);
        $admin = User::factory()->create(['unit_id' => $unit->id, 'role' => UserRole::AdminIct]);

        $request = IctRequest::create([
            'unit_id' => $unit->id,
            'requester_id' => $admin->id,
            'subject' => 'Printer',
            'request_category' => 'hardware',
            'priority' => 'normal',
            'status' => 'checked_by_asmen',
            'justification' => 'Siap print',
            'print_count' => 0,
        ]);

        $request->items()->create([
            'line_number' => 1,
            'item_name' => 'Printer',
            'quantity' => 1,
        ]);

        $this->actingAs($admin)
            ->post(route('forms.ict-requests.print', $request))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $request->refresh();
        $this->assertSame(1, $request->print_count);

        $secondResponse = $this->actingAs($admin)
            ->post(route('forms.ict-requests.print', $request));

        $secondResponse->assertOk()->assertHeader('content-type', 'application/pdf');

        $request->refresh();
        $this->assertSame(2, $request->print_count);
    }

    public function test_email_request_can_complete_through_approval_stages(): void
    {
        $unit = Unit::create(['code' => 'UNIT-02', 'name' => 'Unit 02', 'type' => 'unit', 'is_active' => true]);
        $requester = User::factory()->create(['unit_id' => $unit->id, 'role' => UserRole::AdminIct]);
        $staff = User::factory()->create(['unit_id' => $unit->id, 'role' => UserRole::StaffIct]);
        $asmen = User::factory()->create(['unit_id' => $unit->id, 'role' => UserRole::AsmenIct]);
        $manager = User::factory()->create(['unit_id' => $unit->id, 'role' => UserRole::ManagerIct]);

        $request = EmailRequest::create([
            'unit_id' => $unit->id,
            'requester_id' => $requester->id,
            'employee_name' => 'User Unit',
            'department_name' => 'Finance',
            'requested_email' => 'user.unit@example.com',
            'access_level' => 'internal',
            'justification' => 'Akun kerja',
            'status' => 'submitted',
        ]);

        $this->actingAs($staff)->post(route('approvals.email.update', $request), ['action' => 'approve'])->assertRedirect();
        $this->actingAs($asmen)->post(route('approvals.email.update', $request), ['action' => 'approve'])->assertRedirect();
        $this->actingAs($manager)->post(route('approvals.email.update', $request), ['action' => 'approve'])->assertRedirect();

        $request->refresh();
        $this->assertSame('completed', $request->status);
    }
}
