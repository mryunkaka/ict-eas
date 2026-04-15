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
        Schema::create('asset_handovers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ict_request_id')->constrained('ict_requests')->cascadeOnDelete();
            $table->foreignId('ict_request_item_id')->constrained('ict_request_items')->cascadeOnDelete();
            $table->foreignId('asset_id')->nullable()->constrained('assets')->nullOnDelete();
            
            // Handover type: 'asset' or 'non_asset'
            $table->string('handover_type')->index();
            
            // For non-asset: just description and documents
            $table->text('description')->nullable();
            $table->string('serah_terima_path')->nullable();
            $table->string('serah_terima_name')->nullable();
            $table->unsignedBigInteger('serah_terima_size')->nullable();
            $table->string('serah_terima_mime')->nullable();
            
            $table->string('surat_jalan_path')->nullable();
            $table->string('surat_jalan_name')->nullable();
            $table->unsignedBigInteger('surat_jalan_size')->nullable();
            $table->string('surat_jalan_mime')->nullable();
            
            // For asset: detailed handover information
            $table->string('dept')->nullable();
            $table->string('model_specification')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('asset_number')->nullable();
            
            // Recipient information
            $table->string('recipient_name')->nullable();
            $table->string('recipient_position')->nullable();
            $table->string('supervisor_name')->nullable();
            $table->string('supervisor_position')->nullable();
            
            // Witness (default: ICT staff, but editable)
            $table->string('witness_name')->nullable();
            $table->string('witness_position')->nullable();
            
            // Deliverer (from HRGA)
            $table->string('deliverer_name')->nullable();
            $table->string('deliverer_position')->nullable();
            
            // Generated handover report
            $table->string('handover_report_path')->nullable();
            $table->string('handover_report_name')->nullable();
            $table->string('handover_report_generated_at')->nullable();
            
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['ict_request_id', 'handover_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_handovers');
    }
};
