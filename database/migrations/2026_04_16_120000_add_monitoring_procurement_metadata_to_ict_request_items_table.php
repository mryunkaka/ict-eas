<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ict_request_items', function (Blueprint $table) {
            $table->dateTime('ppnk_uploaded_at')->nullable()->after('ppnk_document_id');
            $table->string('ppnk_number')->nullable()->after('ppnk_uploaded_at');
            $table->dateTime('ppm_uploaded_at')->nullable()->after('ppm_document_id');
            $table->string('ppm_name')->nullable()->after('ppm_uploaded_at');
            $table->string('ppm_number')->nullable()->after('ppm_name');
            $table->dateTime('po_uploaded_at')->nullable()->after('po_document_id');
            $table->string('po_number')->nullable()->after('po_uploaded_at');
        });
    }

    public function down(): void
    {
        Schema::table('ict_request_items', function (Blueprint $table) {
            $table->dropColumn([
                'ppnk_uploaded_at',
                'ppnk_number',
                'ppm_uploaded_at',
                'ppm_name',
                'ppm_number',
                'po_uploaded_at',
                'po_number',
            ]);
        });
    }
};
