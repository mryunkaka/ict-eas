<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ict_requests', function (Blueprint $table) {
            $table->text('rejected_reason')->nullable()->after('final_signed_pdf_uploaded_at');
            $table->foreignId('rejected_by')->nullable()->after('rejected_reason')->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable()->after('rejected_by');
            $table->text('revision_note')->nullable()->after('rejected_at');
            $table->string('revision_attachment_name')->nullable()->after('revision_note');
            $table->string('revision_attachment_path')->nullable()->after('revision_attachment_name');
            $table->unsignedBigInteger('revision_attachment_size')->nullable()->after('revision_attachment_path');
            $table->string('revision_attachment_mime')->nullable()->after('revision_attachment_size');
            $table->foreignId('revision_requested_by')->nullable()->after('revision_attachment_mime')->constrained('users')->nullOnDelete();
            $table->timestamp('revision_requested_at')->nullable()->after('revision_requested_by');
        });
    }

    public function down(): void
    {
        Schema::table('ict_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('revision_requested_by');
            $table->dropColumn([
                'revision_requested_at',
                'revision_attachment_mime',
                'revision_attachment_size',
                'revision_attachment_path',
                'revision_attachment_name',
                'revision_note',
            ]);
            $table->dropConstrainedForeignId('rejected_by');
            $table->dropColumn([
                'rejected_at',
                'rejected_reason',
            ]);
        });
    }
};
