<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Asset;
use App\Models\IncidentReport;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminToolsTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_manage_user(): void
    {
        $unit = Unit::create(['code' => 'UNIT-10', 'name' => 'Unit 10', 'type' => 'unit', 'is_active' => true]);
        $superAdmin = User::factory()->create(['unit_id' => $unit->id, 'role' => UserRole::SuperAdmin]);

        $this->actingAs($superAdmin)
            ->post(route('tools.users.store'), [
                'unit_id' => $unit->id,
                'name' => 'New User',
                'email' => 'new.user@example.com',
                'role' => UserRole::StaffIct->value,
                'password' => 'password123',
                'is_active' => 1,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', ['email' => 'new.user@example.com']);
    }

    public function test_super_admin_can_open_sql_sync_tool(): void
    {
        $unit = Unit::create(['code' => 'UNIT-SQL', 'name' => 'Unit SQL', 'type' => 'unit', 'is_active' => true]);
        $superAdmin = User::factory()->create(['unit_id' => $unit->id, 'role' => UserRole::SuperAdmin]);

        $this->actingAs($superAdmin)
            ->get(route('tools.sql-sync.index'))
            ->assertOk();
    }

    public function test_non_super_admin_cannot_open_sql_sync_tool(): void
    {
        $unit = Unit::create(['code' => 'UNIT-SQL2', 'name' => 'Unit SQL2', 'type' => 'unit', 'is_active' => true]);
        $admin = User::factory()->create(['unit_id' => $unit->id, 'role' => UserRole::AdminIct]);

        $this->actingAs($admin)
            ->get(route('tools.sql-sync.index'))
            ->assertForbidden();
    }

    public function test_ict_admin_can_update_asset_lifecycle(): void
    {
        $unit = Unit::create(['code' => 'UNIT-11', 'name' => 'Unit 11', 'type' => 'unit', 'is_active' => true]);
        $ict = User::factory()->create(['unit_id' => $unit->id, 'role' => UserRole::AdminIct]);
        $asset = Asset::create([
            'unit_id' => $unit->id,
            'uuid' => (string) Str::uuid(),
            'category' => 'device',
            'name' => 'Laptop Operasional',
            'lifecycle_status' => 'active',
        ]);

        $this->actingAs($ict)
            ->post(route('forms.assets.lifecycle.update', $asset), [
                'action_type' => 'disposal',
                'notes' => 'Perangkat rusak total',
            ])
            ->assertRedirect();

        $asset->refresh();
        $this->assertSame('disposed', $asset->lifecycle_status);
        $this->assertDatabaseHas('asset_lifecycle_logs', [
            'asset_id' => $asset->id,
            'action_type' => 'disposal',
        ]);
    }

    public function test_ict_admin_can_add_cctv_maintenance_log(): void
    {
        $unit = Unit::create(['code' => 'UNIT-12', 'name' => 'Unit 12', 'type' => 'unit', 'is_active' => true]);
        $ict = User::factory()->create(['unit_id' => $unit->id, 'role' => UserRole::AdminIct]);
        $reporter = User::factory()->create(['unit_id' => $unit->id, 'role' => UserRole::StaffIct]);

        $incident = IncidentReport::create([
            'unit_id' => $unit->id,
            'reported_by_id' => $reporter->id,
            'incident_type' => 'cctv_outage',
            'title' => 'CCTV Timbangan Down',
            'description' => 'Recorder offline',
            'status' => 'open',
            'occurred_at' => now(),
        ]);

        $this->actingAs($ict)
            ->post(route('forms.incidents.maintenance.store', $incident), [
                'activity_type' => 'repair',
                'description' => 'Ganti power supply recorder',
                'status_after' => 'resolved',
                'performed_at' => now()->toDateTimeString(),
            ])
            ->assertRedirect();

        $incident->refresh();
        $this->assertSame('resolved', $incident->status);
        $this->assertDatabaseHas('cctv_maintenance_logs', [
            'incident_report_id' => $incident->id,
            'activity_type' => 'repair',
        ]);
    }
}
