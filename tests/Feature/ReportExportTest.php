<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\IctRequest;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_page_and_exports_are_available(): void
    {
        $unit = Unit::create(['code' => 'UNIT-20', 'name' => 'Unit 20', 'type' => 'unit', 'is_active' => true]);
        $user = User::factory()->create(['unit_id' => $unit->id, 'role' => UserRole::AdminIct]);

        IctRequest::create([
            'unit_id' => $unit->id,
            'requester_id' => $user->id,
            'subject' => 'Printer baru',
            'request_category' => 'hardware',
            'priority' => 'normal',
            'status' => 'drafted',
            'justification' => 'Kebutuhan administrasi',
        ]);

        $this->actingAs($user)
            ->get(route('reports.index', ['module' => 'ict_requests']))
            ->assertOk()
            ->assertSee('Printer baru');

        $this->actingAs($user)
            ->get(route('reports.export.excel', ['module' => 'ict_requests']))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('reports.export.pdf', ['module' => 'ict_requests']))
            ->assertOk()
            ->assertHeader('content-disposition');
    }
}
