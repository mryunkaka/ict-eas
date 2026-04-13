<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ict_request_ppnk_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ict_request_id')->constrained('ict_requests')->cascadeOnDelete();
            $table->string('ppnk_number');
            $table->string('attachment_name');
            $table->string('attachment_path');
            $table->unsignedBigInteger('attachment_size')->nullable();
            $table->string('attachment_mime')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamps();

            $table->unique(['ict_request_id', 'ppnk_number']);
        });

        Schema::table('ict_request_items', function (Blueprint $table) {
            $table->foreignId('ppnk_document_id')
                ->nullable()
                ->after('photo_size')
                ->constrained('ict_request_ppnk_documents')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ict_request_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('ppnk_document_id');
        });

        Schema::dropIfExists('ict_request_ppnk_documents');
    }
};
