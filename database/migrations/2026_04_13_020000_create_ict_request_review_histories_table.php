<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ict_request_review_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ict_request_id')->constrained('ict_requests')->cascadeOnDelete();
            $table->string('action', 20);
            $table->text('note')->nullable();
            $table->string('attachment_name')->nullable();
            $table->string('attachment_path')->nullable();
            $table->unsignedBigInteger('attachment_size')->nullable();
            $table->string('attachment_mime')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ict_request_review_histories');
    }
};
