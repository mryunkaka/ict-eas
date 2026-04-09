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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->uuid('uuid')->unique();
            $table->string('asset_number')->nullable()->unique();
            $table->string('category')->index();
            $table->string('name');
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable()->index();
            $table->string('vendor')->nullable();
            $table->json('specification')->nullable();
            $table->string('location')->nullable()->index();
            $table->date('purchase_date')->nullable();
            $table->date('warranty_until')->nullable();
            $table->string('condition_status')->default('good')->index();
            $table->string('lifecycle_status')->default('active')->index();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['unit_id', 'category']);
            $table->index(['unit_id', 'lifecycle_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
