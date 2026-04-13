<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ict_requests', function (Blueprint $table) {
            $table->boolean('is_pta_request')->default(false)->after('needed_at');
            $table->text('pta_budget_not_listed_reason')->nullable()->after('additional_budget_reason');
            $table->text('pta_additional_budget_reason')->nullable()->after('pta_budget_not_listed_reason');
            $table->string('drafted_by_name')->nullable()->after('pta_additional_budget_reason');
            $table->string('drafted_by_title')->nullable()->after('drafted_by_name');
            $table->string('acknowledged_by_name')->nullable()->after('drafted_by_title');
            $table->string('acknowledged_by_title')->nullable()->after('acknowledged_by_name');
            $table->string('approved_1_name')->nullable()->after('acknowledged_by_title');
            $table->string('approved_1_title')->nullable()->after('approved_1_name');
            $table->string('approved_2_name')->nullable()->after('approved_1_title');
            $table->string('approved_2_title')->nullable()->after('approved_2_name');
            $table->string('approved_3_name')->nullable()->after('approved_2_title');
            $table->string('approved_3_title')->nullable()->after('approved_3_name');
            $table->string('approved_4_name')->nullable()->after('approved_3_title');
            $table->string('approved_4_title')->nullable()->after('approved_4_name');
        });
    }

    public function down(): void
    {
        Schema::table('ict_requests', function (Blueprint $table) {
            $table->dropColumn([
                'is_pta_request',
                'pta_budget_not_listed_reason',
                'pta_additional_budget_reason',
                'drafted_by_name',
                'drafted_by_title',
                'acknowledged_by_name',
                'acknowledged_by_title',
                'approved_1_name',
                'approved_1_title',
                'approved_2_name',
                'approved_2_title',
                'approved_3_name',
                'approved_3_title',
                'approved_4_name',
                'approved_4_title',
            ]);
        });
    }
};
