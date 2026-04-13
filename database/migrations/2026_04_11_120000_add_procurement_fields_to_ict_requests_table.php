<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ict_requests', function (Blueprint $table) {
            $table->string('quotation_mode', 20)->default('global')->after('needed_at');
        });
    }

    public function down(): void
    {
        Schema::table('ict_requests', function (Blueprint $table) {
            $table->dropColumn('quotation_mode');
        });
    }
};
