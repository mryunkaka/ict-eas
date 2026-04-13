<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\IctRequest;
use App\Models\IctRequestPpnkDocument;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicStorageCleanupCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_orphaned_public_files_are_deleted_but_referenced_files_are_kept(): void
    {
        Storage::fake('public');

        $unit = Unit::create(['code' => 'UNIT-01', 'name' => 'Unit 01', 'type' => 'unit', 'is_active' => true]);
        $user = User::factory()->create(['unit_id' => $unit->id, 'role' => UserRole::AdminIct]);

        $referencedPath = 'ict-request-revisions/referenced.jpg';
        $referencedPpnkPath = 'ict-request-ppnk/referenced.pdf';
        $orphanPath = 'ict-request-revisions/orphan.jpg';
        $orphanPpnkPath = 'ict-request-ppnk/orphan.pdf';

        Storage::disk('public')->put($referencedPath, 'keep-me');
        Storage::disk('public')->put($referencedPpnkPath, 'keep-me-too');
        Storage::disk('public')->put($orphanPath, 'delete-me');
        Storage::disk('public')->put($orphanPpnkPath, 'delete-me-too');
        Storage::disk('public')->put('.gitignore', '');

        $request = IctRequest::create([
            'unit_id' => $unit->id,
            'requester_id' => $user->id,
            'subject' => 'Hardware - Monitor',
            'request_category' => 'hardware',
            'priority' => 'normal',
            'status' => 'needs_revision',
            'needed_at' => now()->toDateString(),
            'justification' => 'Test cleanup',
            'revision_attachment_path' => $referencedPath,
        ]);

        IctRequestPpnkDocument::create([
            'ict_request_id' => $request->id,
            'ppnk_number' => 'PPNK-001/ICT/2026',
            'attachment_name' => 'referenced.pdf',
            'attachment_path' => $referencedPpnkPath,
            'attachment_mime' => 'application/pdf',
        ]);

        Artisan::call('storage:clean-orphaned-public-files');

        Storage::disk('public')->assertExists($referencedPath);
        Storage::disk('public')->assertExists($referencedPpnkPath);
        Storage::disk('public')->assertMissing($orphanPath);
        Storage::disk('public')->assertMissing($orphanPpnkPath);
        Storage::disk('public')->assertExists('.gitignore');
    }
}
