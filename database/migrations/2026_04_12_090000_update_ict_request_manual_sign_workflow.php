<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ict_requests', function (Blueprint $table) {
            $table->foreignId('staff_validated_by')->nullable()->after('additional_budget_reason')->constrained('users')->nullOnDelete();
            $table->timestamp('staff_validated_at')->nullable()->after('staff_validated_by');
            $table->foreignId('asmen_checked_by')->nullable()->after('staff_validated_at')->constrained('users')->nullOnDelete();
            $table->timestamp('asmen_checked_at')->nullable()->after('asmen_checked_by');
            $table->string('final_signed_pdf_name')->nullable()->after('manager_approved_at');
            $table->string('final_signed_pdf_path')->nullable()->after('final_signed_pdf_name');
            $table->foreignId('final_signed_pdf_uploaded_by')->nullable()->after('final_signed_pdf_path')->constrained('users')->nullOnDelete();
            $table->timestamp('final_signed_pdf_uploaded_at')->nullable()->after('final_signed_pdf_uploaded_by');
        });
    }

    public function down(): void
    {
        Schema::table('ict_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('final_signed_pdf_uploaded_by');
            $table->dropConstrainedForeignId('asmen_checked_by');
            $table->dropConstrainedForeignId('staff_validated_by');
            $table->dropColumn([
                'staff_validated_at',
                'asmen_checked_at',
                'final_signed_pdf_name',
                'final_signed_pdf_path',
                'final_signed_pdf_uploaded_at',
            ]);
        });
    }
};
