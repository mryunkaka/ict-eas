<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\EmailRequest;
use App\Models\IctRequest;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApprovalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_ict_request_can_move_through_approval_stages(): void
    {
        $unit = Unit::create(['code' => 'UNIT-01', 'name' => 'Unit 01', 'type' => 'unit', 'is_active' => true]);
        $requester = User::factory()->create(['unit_id' => $unit->id, 'role' => UserRole::UnitUser]);
        $unitAdmin = User::factory()->create(['unit_id' => $unit->id, 'role' => UserRole::UnitAdmin]);
        $hrga = User::factory()->create(['unit_id' => $unit->id, 'role' => UserRole::HrgaApprover]);
        $ict = User::factory()->create(['unit_id' => $unit->id, 'role' => UserRole::IctAdmin]);

        $request = IctRequest::create([
            'unit_id' => $unit->id,
            'requester_id' => $requester->id,
            'subject' => 'Laptop baru',
            'request_category' => 'hardware',
            'priority' => 'normal',
            'status' => 'submitted',
            'justification' => 'Kebutuhan operasional',
        ]);

        $this->actingAs($unitAdmin)
            ->post(route('approvals.ict.update', $request), ['action' => 'approve'])
            ->assertRedirect();

        $request->refresh();
        $this->assertSame('manager_approved', $request->status);

        $this->actingAs($hrga)
            ->post(route('approvals.ict.update', $request), ['action' => 'approve'])
            ->assertRedirect();

        $request->refresh();
        $this->assertSame('ga_approved', $request->status);

        $this->actingAs($ict)
            ->post(route('approvals.ict.update', $request), ['action' => 'approve'])
            ->assertRedirect();

        $request->refresh();
        $this->assertSame('ict_approved', $request->status);
    }

    public function test_email_request_can_complete_through_approval_stages(): void
    {
        $unit = Unit::create(['code' => 'UNIT-02', 'name' => 'Unit 02', 'type' => 'unit', 'is_active' => true]);
        $requester = User::factory()->create(['unit_id' => $unit->id, 'role' => UserRole::UnitUser]);
        $unitAdmin = User::factory()->create(['unit_id' => $unit->id, 'role' => UserRole::UnitAdmin]);
        $hrga = User::factory()->create(['unit_id' => $unit->id, 'role' => UserRole::HrgaApprover]);
        $ict = User::factory()->create(['unit_id' => $unit->id, 'role' => UserRole::IctAdmin]);

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

        $this->actingAs($unitAdmin)->post(route('approvals.email.update', $request), ['action' => 'approve'])->assertRedirect();
        $this->actingAs($hrga)->post(route('approvals.email.update', $request), ['action' => 'approve'])->assertRedirect();
        $this->actingAs($ict)->post(route('approvals.email.update', $request), ['action' => 'approve'])->assertRedirect();

        $request->refresh();
        $this->assertSame('completed', $request->status);
    }
}
