<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cctv_maintenance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_report_id')->constrained()->cascadeOnDelete();
            $table->foreignId('handled_by_id')->constrained('users')->cascadeOnDelete();
            $table->string('activity_type')->index();
            $table->text('description');
            $table->string('status_after')->default('on_progress')->index();
            $table->timestamp('performed_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cctv_maintenance_logs');
    }
};
