<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_requests', function (Blueprint $table): void {
            $table->string('requested_email')->nullable()->change();
            $table->string('diketahui_dept_head_name')->nullable()->after('justification');
            $table->string('diketahui_dept_head_title')->nullable()->after('diketahui_dept_head_name');
            $table->string('diketahui_div_head_name')->nullable()->after('diketahui_dept_head_title');
            $table->string('diketahui_div_head_title')->nullable()->after('diketahui_div_head_name');
            $table->string('disetujui_hrga_head_name')->nullable()->after('diketahui_div_head_title');
            $table->string('disetujui_hrga_head_title')->nullable()->after('disetujui_hrga_head_name');
            $table->string('pelaksana_ict_head_name')->nullable()->after('disetujui_hrga_head_title');
            $table->string('pelaksana_ict_head_title')->nullable()->after('pelaksana_ict_head_name');
        });
    }

    public function down(): void
    {
        Schema::table('email_requests', function (Blueprint $table): void {
            $table->dropColumn([
                'diketahui_dept_head_name',
                'diketahui_dept_head_title',
                'diketahui_div_head_name',
                'diketahui_div_head_title',
                'disetujui_hrga_head_name',
                'disetujui_hrga_head_title',
                'pelaksana_ict_head_name',
                'pelaksana_ict_head_title',
            ]);
            $table->string('requested_email')->nullable(false)->change();
        });
    }
};
