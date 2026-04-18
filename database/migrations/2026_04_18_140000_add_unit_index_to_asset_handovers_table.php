<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('asset_handovers')) {
            return;
        }

        Schema::table('asset_handovers', function (Blueprint $table) {
            if (! Schema::hasColumn('asset_handovers', 'unit_index')) {
                $table->unsignedSmallInteger('unit_index')->nullable()->after('ict_request_item_id');
            }
        });

        $rows = DB::table('asset_handovers')
            ->orderBy('ict_request_item_id')
            ->orderBy('id')
            ->get(['id', 'ict_request_item_id']);

        $nextIndex = [];
        foreach ($rows as $row) {
            $itemId = (int) $row->ict_request_item_id;
            $i = $nextIndex[$itemId] ?? 0;
            DB::table('asset_handovers')->where('id', $row->id)->update(['unit_index' => $i]);
            $nextIndex[$itemId] = $i + 1;
        }

        $driver = Schema::getConnection()->getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE `asset_handovers` MODIFY `unit_index` SMALLINT UNSIGNED NOT NULL DEFAULT 0');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE asset_handovers ALTER COLUMN unit_index SET DEFAULT 0');
            DB::statement('ALTER TABLE asset_handovers ALTER COLUMN unit_index SET NOT NULL');
        } else {
            Schema::table('asset_handovers', function (Blueprint $table) {
                $table->unsignedSmallInteger('unit_index')->default(0)->nullable(false)->change();
            });
        }

        Schema::table('asset_handovers', function (Blueprint $table) {
            $table->unique(['ict_request_item_id', 'unit_index'], 'asset_handovers_item_unit_unique');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('asset_handovers')) {
            return;
        }

        Schema::table('asset_handovers', function (Blueprint $table) {
            $table->dropUnique('asset_handovers_item_unit_unique');
        });

        Schema::table('asset_handovers', function (Blueprint $table) {
            $table->dropColumn('unit_index');
        });
    }
};
