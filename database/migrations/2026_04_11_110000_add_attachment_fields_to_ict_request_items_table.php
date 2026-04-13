<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ict_request_items', function (Blueprint $table) {
            $table->string('unit', 50)->nullable()->after('brand_type');
            $table->string('photo_name', 15)->nullable()->after('notes');
            $table->string('photo_path')->nullable()->after('photo_name');
            $table->unsignedInteger('photo_size')->nullable()->after('photo_path');
        });
    }

    public function down(): void
    {
        Schema::table('ict_request_items', function (Blueprint $table) {
            $table->dropColumn([
                'unit',
                'photo_name',
                'photo_path',
                'photo_size',
            ]);
        });
    }
};
