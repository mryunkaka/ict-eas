<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ict_requests', function (Blueprint $table) {
            $table->unsignedInteger('revision_number')->default(0)->after('form_number');
        });
    }

    public function down(): void
    {
        Schema::table('ict_requests', function (Blueprint $table) {
            $table->dropColumn('revision_number');
        });
    }
};
