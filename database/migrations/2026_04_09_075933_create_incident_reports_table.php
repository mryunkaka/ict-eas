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
        Schema::create('incident_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reported_by_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('asset_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('repair_request_id')->nullable()->constrained()->nullOnDelete();
            $table->string('incident_type')->index();
            $table->string('title');
            $table->text('description');
            $table->text('follow_up')->nullable();
            $table->boolean('repairable')->nullable();
            $table->string('status')->default('open')->index();
            $table->timestamp('occurred_at');
            $table->foreignId('known_by_manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('known_by_ict_head_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['unit_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incident_reports');
    }
};
