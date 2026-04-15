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
        Schema::table('ict_request_items', function (Blueprint $table) {
            $table->unsignedInteger('takeout_qty')->nullable()->after('quantity');
            $table->index('takeout_qty');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ict_request_items', function (Blueprint $table) {
            $table->dropIndex(['takeout_qty']);
            $table->dropColumn('takeout_qty');
        });
    }
};
