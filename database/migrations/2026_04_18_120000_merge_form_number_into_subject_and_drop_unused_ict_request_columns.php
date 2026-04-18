<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ict_requests')) {
            return;
        }

        DB::table('ict_requests')->orderBy('id')->chunkById(200, function ($rows) {
            foreach ($rows as $row) {
                $subject = trim((string) ($row->subject ?? ''));
                if ($subject === '' && ! empty($row->form_number)) {
                    DB::table('ict_requests')->where('id', $row->id)->update([
                        'subject' => $row->form_number,
                    ]);
                }
            }
        });

        Schema::table('ict_requests', function (Blueprint $table) {
            if (Schema::hasColumn('ict_requests', 'form_number')) {
                $table->dropColumn('form_number');
            }

            foreach (['ga_approved_by', 'ict_approved_by', 'manager_approved_by'] as $col) {
                if (Schema::hasColumn('ict_requests', $col)) {
                    $table->dropConstrainedForeignId($col);
                }
            }

            foreach (['ga_approved_at', 'ict_approved_at', 'manager_approved_at'] as $col) {
                if (Schema::hasColumn('ict_requests', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('ict_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('ict_requests', 'form_number')) {
                $table->string('form_number')->nullable()->unique();
            }

            if (! Schema::hasColumn('ict_requests', 'manager_approved_by')) {
                $table->foreignId('manager_approved_by')->nullable()->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('ict_requests', 'ga_approved_by')) {
                $table->foreignId('ga_approved_by')->nullable()->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('ict_requests', 'ict_approved_by')) {
                $table->foreignId('ict_approved_by')->nullable()->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('ict_requests', 'manager_approved_at')) {
                $table->timestamp('manager_approved_at')->nullable();
            }
            if (! Schema::hasColumn('ict_requests', 'ga_approved_at')) {
                $table->timestamp('ga_approved_at')->nullable();
            }
            if (! Schema::hasColumn('ict_requests', 'ict_approved_at')) {
                $table->timestamp('ict_approved_at')->nullable();
            }
        });

        DB::table('ict_requests')->whereNotNull('subject')->update([
            'form_number' => DB::raw('subject'),
        ]);
    }
};
