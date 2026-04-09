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
        Schema::create('ict_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete();
            $table->string('form_number')->nullable()->unique();
            $table->string('subject');
            $table->string('request_category')->index();
            $table->string('priority')->default('normal')->index();
            $table->string('status')->default('submitted')->index();
            $table->date('needed_at')->nullable();
            $table->text('justification');
            $table->text('additional_budget_reason')->nullable();
            $table->foreignId('manager_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('ga_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('ict_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('manager_approved_at')->nullable();
            $table->timestamp('ga_approved_at')->nullable();
            $table->timestamp('ict_approved_at')->nullable();
            $table->timestamps();

            $table->index(['unit_id', 'status']);
            $table->index(['requester_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ict_requests');
    }
};
