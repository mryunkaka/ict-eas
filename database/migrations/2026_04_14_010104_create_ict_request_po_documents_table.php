<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ict_request_po_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ict_request_id')->constrained('ict_requests')->cascadeOnDelete();
            $table->string('po_number')->nullable();
            $table->string('attachment_name')->nullable();
            $table->string('attachment_path')->nullable();
            $table->unsignedBigInteger('attachment_size')->nullable();
            $table->string('attachment_mime')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamps();
        });

        Schema::table('ict_request_items', function (Blueprint $table) {
            $table->foreignId('po_document_id')->nullable()->after('pr_number')->constrained('ict_request_po_documents')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ict_request_items', function (Blueprint $table) {
            $table->dropForeign(['po_document_id']);
            $table->dropColumn('po_document_id');
        });

        Schema::dropIfExists('ict_request_po_documents');
    }
};
