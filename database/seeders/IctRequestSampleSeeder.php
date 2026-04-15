<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\IctRequest;
use App\Models\IctRequestItem;
use App\Models\IctRequestPpnkDocument;
use App\Models\IctRequestQuotation;
use App\Models\IctRequestReviewHistory;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class IctRequestSampleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Membuat sample ICT Request sampai tahap Verifikasi Audit...');

        // Get or create units
        $easUnit = Unit::firstOrCreate(
            ['code' => 'EAS-HO'],
            ['name' => 'EAS Head Office', 'type' => 'eas', 'is_head_office' => true, 'is_active' => true]
        );

        $unit1 = Unit::firstOrCreate(
            ['code' => 'UNIT-IT'],
            ['name' => 'Unit IT & Development', 'type' => 'unit', 'is_head_office' => false, 'is_active' => true]
        );

        $unit2 = Unit::firstOrCreate(
            ['code' => 'UNIT-FIN'],
            ['name' => 'Unit Finance & Accounting', 'type' => 'unit', 'is_head_office' => false, 'is_active' => true]
        );

        // Get or create users
        $adminIct = User::firstOrCreate(
            ['email' => 'admin.ict@easgroup.co.id'],
            [
                'unit_id' => $easUnit->id,
                'name' => 'Admin ICT',
                'password' => bcrypt('password'),
                'role' => UserRole::AdminIct,
                'job_title' => 'Admin ICT',
                'is_active' => true,
            ]
        );

        $requester1 = User::firstOrCreate(
            ['email' => 'john.doe@easgroup.co.id'],
            [
                'unit_id' => $unit1->id,
                'name' => 'John Doe',
                'password' => bcrypt('password'),
                'role' => UserRole::UnitUser,
                'job_title' => 'Senior Developer',
                'is_active' => true,
            ]
        );

        $requester2 = User::firstOrCreate(
            ['email' => 'jane.smith@easgroup.co.id'],
            [
                'unit_id' => $unit2->id,
                'name' => 'Jane Smith',
                'password' => bcrypt('password'),
                'role' => UserRole::UnitUser,
                'job_title' => 'Finance Manager',
                'is_active' => true,
            ]
        );

        $this->command->info('✅ Users siap');

        // Sample ICT Request 1: Hardware - Laptop & Monitor
        $this->command->info('📝 Membuat ICT Request #1: Hardware untuk Unit IT...');
        $request1 = $this->createHardwareRequest($requester1, $unit1, $adminIct);

        // Sample ICT Request 2: Software License
        $this->command->info('📝 Membuat ICT Request #2: Software untuk Finance...');
        $request2 = $this->createSoftwareRequest($requester2, $unit2, $adminIct);

        // Sample ICT Request 3: Accessories
        $this->command->info('📝 Membuat ICT Request #3: Accessories untuk Unit IT...');
        $request3 = $this->createAccessoriesRequest($requester1, $unit1, $adminIct);

        $this->command->info('✅ Semua sample ICT Request berhasil dibuat!');
        $this->command->info('📊 Total: 3 ICT Requests dengan total 9 items');
        $this->command->info(' Status: Progress Verifikasi Audit (siap untuk diverifikasi)');
    }

    protected function createHardwareRequest(User $requester, Unit $unit, User $adminIct): IctRequest
    {
        $request = IctRequest::create([
            'unit_id' => $unit->id,
            'requester_id' => $requester->id,
            'subject' => 'Hardware - Laptop & Monitor untuk Tim Development',
            'request_category' => 'hardware',
            'priority' => 'high',
            'status' => 'progress_verifikasi_audit',
            'needed_at' => now()->addDays(30)->toDateString(),
            'quotation_mode' => 'global',
            'is_pta_request' => false,
            'justification' => 'Dibutuhkan untuk 2 developer baru yang bergabung di Q2 2026. Spesifikasi minimal: Core i7, RAM 16GB, SSD 512GB.',
            'final_signed_pdf_name' => 'Signed_Form_Hardware_001.pdf',
            'final_signed_pdf_path' => $this->createSamplePdf('Signed_Form_Hardware_001.pdf', 'Hardware Request - Unit IT'),
            'print_count' => 1,
            'last_printed_at' => now()->subDays(5),
            'drafted_by_name' => $adminIct->name,
            'drafted_by_title' => $adminIct->job_title,
            'staff_validated_by' => $adminIct->id,
            'staff_validated_at' => now()->subDays(4),
            'asmen_checked_by' => $adminIct->id,
            'asmen_checked_at' => now()->subDays(3),
        ]);

        // Items
        $item1 = $request->items()->create([
            'line_number' => 1,
            'item_category' => 'hardware',
            'item_name' => 'Laptop Dell Latitude 5540',
            'brand_type' => 'Dell',
            'unit' => 'unit',
            'quantity' => 2,
            'estimated_price' => 18500000,
            'notes' => 'Spesifikasi: Intel Core i7-1365U, 16GB RAM, 512GB SSD, 15.6" FHD',
            'photo_name' => 'laptop-dell.jpg',
            'photo_path' => $this->createSampleImage('laptop-dell.jpg', 'Laptop Dell Latitude 5540'),
            'photo_size' => 45678,
        ]);

        $item2 = $request->items()->create([
            'line_number' => 2,
            'item_category' => 'hardware',
            'item_name' => 'Monitor Dell UltraSharp 27"',
            'brand_type' => 'Dell U2723QE',
            'unit' => 'unit',
            'quantity' => 2,
            'estimated_price' => 8500000,
            'notes' => 'Monitor 4K USB-C Hub, IPS Black, 98% DCI-P3',
            'photo_name' => 'monitor.jpg',
            'photo_path' => $this->createSampleImage('monitor.jpg', 'Monitor Dell UltraSharp'),
            'photo_size' => 52341,
        ]);

        // Global Quotations
        $this->createQuotation($request, null, 'PT. Teknologi Maju', 'Quotation_TekMaju.pdf');
        $this->createQuotation($request, null, 'CV. Digital Solusi', 'Quotation_DigSol.pdf');

        // PPNK Documents
        $this->createPpnkDocument($request, $item1, 'PPNK-HW-001/2026', 'PPNK_Laptop_Dell.pdf');
        $this->createPpnkDocument($request, $item2, 'PPNK-HW-002/2026', 'PPNK_Monitor_Dell.pdf');

        // Review History
        $this->createReviewHistory($request, $adminIct, 'approved', 'Dokumen lengkap, lanjut ke PPNK');

        $this->command->info('   ✅ Hardware Request created: 2 items (Laptop + Monitor)');
        return $request;
    }

    protected function createSoftwareRequest(User $requester, Unit $unit, User $adminIct): IctRequest
    {
        $request = IctRequest::create([
            'unit_id' => $unit->id,
            'requester_id' => $requester->id,
            'subject' => 'Software License - Adobe Creative Cloud & Microsoft 365',
            'request_category' => 'software',
            'priority' => 'normal',
            'status' => 'progress_verifikasi_audit',
            'needed_at' => now()->addDays(45)->toDateString(),
            'quotation_mode' => 'per_item',
            'is_pta_request' => true,
            'justification' => 'Tim design membutuhkan Adobe CC untuk project branding Q2. Microsoft 365 untuk 5 staff baru.',
            'final_signed_pdf_name' => 'Signed_Form_Software_002.pdf',
            'final_signed_pdf_path' => $this->createSamplePdf('Signed_Form_Software_002.pdf', 'Software License Request'),
            'print_count' => 1,
            'last_printed_at' => now()->subDays(6),
            'drafted_by_name' => $adminIct->name,
            'drafted_by_title' => $adminIct->job_title,
            'staff_validated_by' => $adminIct->id,
            'staff_validated_at' => now()->subDays(5),
            'asmen_checked_by' => $adminIct->id,
            'asmen_checked_at' => now()->subDays(4),
        ]);

        // Items
        $item1 = $request->items()->create([
            'line_number' => 1,
            'item_category' => 'software',
            'item_name' => 'Adobe Creative Cloud - All Apps',
            'brand_type' => 'Adobe',
            'unit' => 'license',
            'quantity' => 3,
            'estimated_price' => 8500000,
            'notes' => 'Annual subscription, All Apps plan. Untuk tim Design & Marketing.',
            'photo_name' => 'adobe-cc.jpg',
            'photo_path' => $this->createSampleImage('adobe-cc.jpg', 'Adobe Creative Cloud License'),
            'photo_size' => 38921,
        ]);

        $item2 = $request->items()->create([
            'line_number' => 2,
            'item_category' => 'software',
            'item_name' => 'Microsoft 365 Business Standard',
            'brand_type' => 'Microsoft',
            'unit' => 'license',
            'quantity' => 5,
            'estimated_price' => 2100000,
            'notes' => 'Per user/year, includes Teams, SharePoint, 1TB OneDrive',
            'photo_name' => 'ms365.jpg',
            'photo_path' => $this->createSampleImage('ms365.jpg', 'Microsoft 365 License'),
            'photo_size' => 41234,
        ]);

        // Per-item Quotations
        $this->createQuotation($request, $item1, 'PT. Adobe Indonesia', 'Quotation_Adobe.pdf');
        $this->createQuotation($request, $item1, 'PT. Software Partner', 'Quotation_SoftwarePartner.pdf');
        $this->createQuotation($request, $item2, 'PT. Microsoft Partner', 'Quotation_MS365.pdf');

        // PPNK Documents
        $this->createPpnkDocument($request, $item1, 'PPNK-SW-001/2026', 'PPNK_Adobe_CC.pdf');
        $this->createPpnkDocument($request, $item2, 'PPNK-SW-002/2026', 'PPNK_MS365.pdf');

        // Review History
        $this->createReviewHistory($request, $adminIct, 'approved', 'Budget approved, lanjut ke PPNK');

        $this->command->info('   ✅ Software Request created: 2 items (Adobe CC + MS365)');
        return $request;
    }

    protected function createAccessoriesRequest(User $requester, Unit $unit, User $adminIct): IctRequest
    {
        $request = IctRequest::create([
            'unit_id' => $unit->id,
            'requester_id' => $requester->id,
            'subject' => 'Accessories - Keyboard, Mouse & Headset untuk Tim IT',
            'request_category' => 'accessories',
            'priority' => 'low',
            'status' => 'progress_verifikasi_audit',
            'needed_at' => now()->addDays(15)->toDateString(),
            'quotation_mode' => 'global',
            'is_pta_request' => false,
            'justification' => 'Penggantian accessories yang sudah rusak untuk 5 staff IT support.',
            'final_signed_pdf_name' => 'Signed_Form_Accessories_003.pdf',
            'final_signed_pdf_path' => $this->createSamplePdf('Signed_Form_Accessories_003.pdf', 'Accessories Request'),
            'print_count' => 1,
            'last_printed_at' => now()->subDays(3),
            'drafted_by_name' => $adminIct->name,
            'drafted_by_title' => $adminIct->job_title,
            'staff_validated_by' => $adminIct->id,
            'staff_validated_at' => now()->subDays(2),
            'asmen_checked_by' => $adminIct->id,
            'asmen_checked_at' => now()->subDays(1),
        ]);

        // Items
        $item1 = $request->items()->create([
            'line_number' => 1,
            'item_category' => 'accessories',
            'item_name' => 'Keyboard Logitech MX Keys',
            'brand_type' => 'Logitech',
            'unit' => 'unit',
            'quantity' => 5,
            'estimated_price' => 1500000,
            'notes' => 'Wireless, backlit, multi-device',
            'photo_name' => 'keyboard.jpg',
            'photo_path' => $this->createSampleImage('keyboard.jpg', 'Keyboard Logitech MX Keys'),
            'photo_size' => 34567,
        ]);

        $item2 = $request->items()->create([
            'line_number' => 2,
            'item_category' => 'accessories',
            'item_name' => 'Mouse Logitech MX Master 3S',
            'brand_type' => 'Logitech',
            'unit' => 'unit',
            'quantity' => 5,
            'estimated_price' => 1300000,
            'notes' => 'Wireless, ergonomic, 8K DPI',
            'photo_name' => 'mouse.jpg',
            'photo_path' => $this->createSampleImage('mouse.jpg', 'Mouse Logitech MX Master 3S'),
            'photo_size' => 36789,
        ]);

        $item3 = $request->items()->create([
            'line_number' => 3,
            'item_category' => 'accessories',
            'item_name' => 'Headset Logitech Zone Vibe 100',
            'brand_type' => 'Logitech',
            'unit' => 'unit',
            'quantity' => 5,
            'estimated_price' => 1800000,
            'notes' => 'Wireless, noise cancelling, Teams certified',
            'photo_name' => 'headset.jpg',
            'photo_path' => $this->createSampleImage('headset.jpg', 'Headset Logitech Zone Vibe 100'),
            'photo_size' => 39012,
        ]);

        // Global Quotations
        $this->createQuotation($request, null, 'PT. Peripheral Store', 'Quotation_Peripheral.pdf');
        $this->createQuotation($request, null, 'CV. IT Accessories', 'Quotation_ITAcc.pdf');

        // PPNK Documents (items 1 & 2 share same PPNK number)
        $this->createPpnkDocument($request, $item1, 'PPNK-ACC-001/2026', 'PPNK_Keyboard_Mouse.pdf');
        $this->createPpnkDocument($request, $item2, 'PPNK-ACC-001/2026', 'PPNK_Keyboard_Mouse.pdf');
        $this->createPpnkDocument($request, $item3, 'PPNK-ACC-002/2026', 'PPNK_Headset.pdf');

        // Review History
        $this->createReviewHistory($request, $adminIct, 'approved', 'Approved untuk procurement');

        $this->command->info('   ✅ Accessories Request created: 3 items (Keyboard + Mouse + Headset)');
        return $request;
    }

    protected function createQuotation(IctRequest $request, ?IctRequestItem $item, string $vendorName, string $fileName): void
    {
        $path = $this->createSamplePdf($fileName, "Quotation from {$vendorName}");

        IctRequestQuotation::create([
            'ict_request_id' => $request->id,
            'ict_request_item_id' => $item?->id,
            'vendor_name' => $vendorName,
            'attachment_name' => $fileName,
            'attachment_path' => $path,
            'attachment_size' => rand(50000, 150000),
            'attachment_mime' => 'application/pdf',
        ]);
    }

    protected function createPpnkDocument(IctRequest $request, IctRequestItem $item, string $ppnkNumber, string $fileName): void
    {
        // Check if document with same number already exists
        $existingDoc = IctRequestPpnkDocument::where('ict_request_id', $request->id)
            ->where('ppnk_number', $ppnkNumber)
            ->first();

        if ($existingDoc) {
            // Reuse existing document
            $item->update(['ppnk_document_id' => $existingDoc->id]);
            return;
        }

        $path = $this->createSamplePdf($fileName, "PPNK Document - {$ppnkNumber}");

        $document = IctRequestPpnkDocument::create([
            'ict_request_id' => $request->id,
            'ppnk_number' => $ppnkNumber,
            'attachment_name' => $fileName,
            'attachment_path' => $path,
            'attachment_size' => rand(80000, 200000),
            'attachment_mime' => 'application/pdf',
            'uploaded_by' => $request->requester_id,
            'uploaded_at' => now(),
        ]);

        $item->update(['ppnk_document_id' => $document->id]);
    }

    protected function createReviewHistory(IctRequest $request, User $user, string $action, string $note): void
    {
        IctRequestReviewHistory::create([
            'ict_request_id' => $request->id,
            'reviewed_by' => $user->id,
            'action' => $action,
            'note' => $note,
            'reviewed_at' => now()->subDays(rand(1, 5)),
        ]);
    }

    protected function createSamplePdf(string $fileName, string $content): string
    {
        $directory = match (true) {
            str_contains($fileName, 'Signed') => 'ict-request-signed',
            str_contains($fileName, 'PPNK') => 'ict-request-ppnk',
            str_contains($fileName, 'PPM') => 'ict-request-ppm',
            str_contains($fileName, 'Quotation') => 'ict-request-quotations',
            default => 'ict-request-items',
        };

        $path = "{$directory}/{$fileName}";

        // Generate a simple PDF content (base64 encoded minimal PDF)
        $pdfContent = $this->generateMinimalPdf($content);

        Storage::disk('public')->put($path, $pdfContent);

        return $path;
    }

    protected function createSampleImage(string $fileName, string $content): string
    {
        $path = "ict-request-items/{$fileName}";

        // Generate a simple placeholder image (1x1 pixel PNG)
        $imageContent = $this->generateMinimalPng($content);

        Storage::disk('public')->put($path, $imageContent);

        return $path;
    }

    protected function generateMinimalPdf(string $content): string
    {
        // Create a minimal valid PDF with the content
        $pdf = "%PDF-1.4\n";
        $pdf .= "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
        $pdf .= "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
        $pdf .= "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>\nendobj\n";
        $pdf .= "4 0 obj\n<< /Length 200 >>\nstream\nBT\n/F1 24 Tf\n50 700 Td\n(" . addslashes($content) . ") Tj\nET\nendstream\nendobj\n";
        $pdf .= "5 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n";
        $pdf .= "xref\n0 6\n0000000000 65535 f \n0000000009 00000 n \n0000000058 00000 n \n0000000115 00000 n \n0000000266 00000 n \n0000000517 00000 n \n";
        $pdf .= "trailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n590\n%%EOF";

        return $pdf;
    }

    protected function generateMinimalPng(string $content): string
    {
        // Create a simple 200x50 PNG image with text
        $width = 200;
        $height = 50;

        // Create a simple PNG (this is a minimal valid PNG)
        // For simplicity, we'll create a small colored rectangle
        $image = imagecreatetruecolor($width, $height);

        // Background color
        $bg = imagecolorallocate($image, 240, 240, 240);
        imagefill($image, 0, 0, $bg);

        // Border
        $border = imagecolorallocate($image, 100, 100, 100);
        imagerectangle($image, 0, 0, $width - 1, $height - 1, $border);

        // Text color
        $textColor = imagecolorallocate($image, 50, 50, 50);

        // Add text (simplified - just a few characters due to space)
        $displayText = substr($content, 0, 20);
        imagestring($image, 3, 10, 18, $displayText, $textColor);

        // Save to buffer
        ob_start();
        imagepng($image);
        $imageContent = ob_get_clean();

        imagedestroy($image);

        return $imageContent;
    }
}
