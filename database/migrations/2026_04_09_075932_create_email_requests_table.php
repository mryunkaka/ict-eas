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
        Schema::create('email_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete();
            $table->string('employee_name');
            $table->string('department_name');
            $table->string('job_title')->nullable();
            $table->string('requested_email')->unique();
            $table->string('access_level')->default('internal')->index();
            $table->text('justification');
            $table->string('status')->default('submitted')->index();
            $table->foreignId('manager_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('hrga_verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('ict_processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('manager_approved_at')->nullable();
            $table->timestamp('hrga_verified_at')->nullable();
            $table->timestamp('ict_processed_at')->nullable();
            $table->timestamps();

            $table->index(['unit_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_requests');
    }
};
