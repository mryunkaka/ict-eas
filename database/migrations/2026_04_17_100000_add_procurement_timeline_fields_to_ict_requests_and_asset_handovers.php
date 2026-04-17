<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ict_requests', function (Blueprint $table) {
            $table->dateTime('audit_verified_at')->nullable()->after('asmen_checked_at');
            $table->dateTime('goods_arrived_at')->nullable()->after('audit_verified_at');
            $table->dateTime('goods_delivered_at')->nullable()->after('goods_arrived_at');
        });

        Schema::table('asset_handovers', function (Blueprint $table) {
            $table->dateTime('received_at')->nullable()->after('handover_type');
        });
    }

    public function down(): void
    {
        Schema::table('asset_handovers', function (Blueprint $table) {
            $table->dropColumn('received_at');
        });

        Schema::table('ict_requests', function (Blueprint $table) {
            $table->dropColumn([
                'audit_verified_at',
                'goods_arrived_at',
                'goods_delivered_at',
            ]);
        });
    }
};
