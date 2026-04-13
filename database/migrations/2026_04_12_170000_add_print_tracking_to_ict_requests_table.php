<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ict_requests', function (Blueprint $table) {
            $table->unsignedInteger('print_count')->default(0)->after('revision_number');
            $table->timestamp('last_printed_at')->nullable()->after('print_count');
        });
    }

    public function down(): void
    {
        Schema::table('ict_requests', function (Blueprint $table) {
            $table->dropColumn(['print_count', 'last_printed_at']);
        });
    }
};
