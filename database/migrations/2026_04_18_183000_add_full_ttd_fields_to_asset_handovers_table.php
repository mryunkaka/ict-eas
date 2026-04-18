<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asset_handovers', function (Blueprint $table): void {
            $table->dateTime('full_ttd_signed_at')->nullable()->after('received_at');
            $table->string('full_ttd_path')->nullable()->after('serah_terima_mime');
            $table->string('full_ttd_name')->nullable()->after('full_ttd_path');
            $table->unsignedBigInteger('full_ttd_size')->nullable()->after('full_ttd_name');
            $table->string('full_ttd_mime')->nullable()->after('full_ttd_size');
        });
    }

    public function down(): void
    {
        Schema::table('asset_handovers', function (Blueprint $table): void {
            $table->dropColumn([
                'full_ttd_signed_at',
                'full_ttd_path',
                'full_ttd_name',
                'full_ttd_size',
                'full_ttd_mime',
            ]);
        });
    }
};
