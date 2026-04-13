<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ict_request_quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ict_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ict_request_item_id')->nullable()->constrained()->cascadeOnDelete();
            $table->unsignedInteger('line_number')->default(1);
            $table->string('vendor_name')->nullable();
            $table->string('attachment_name')->nullable();
            $table->string('attachment_path')->nullable();
            $table->unsignedInteger('attachment_size')->nullable();
            $table->string('attachment_mime', 100)->nullable();
            $table->timestamps();

            $table->index(['ict_request_id', 'ict_request_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ict_request_quotations');
    }
};
