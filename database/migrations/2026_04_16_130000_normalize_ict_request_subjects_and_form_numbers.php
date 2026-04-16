<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $requests = DB::table('ict_requests')
            ->join('units', 'units.id', '=', 'ict_requests.unit_id')
            ->select([
                'ict_requests.id',
                'ict_requests.unit_id',
                'units.code as unit_code',
            ])
            ->orderBy('ict_requests.unit_id')
            ->orderBy('ict_requests.created_at')
            ->orderBy('ict_requests.id')
            ->get();

        $sequencesByUnit = [];

        foreach ($requests as $request) {
            $unitId = (int) $request->unit_id;
            $sequencesByUnit[$unitId] = ($sequencesByUnit[$unitId] ?? 0) + 1;
            $identifier = sprintf(
                '%s-FORM ICT-%03d',
                Str::upper(trim((string) ($request->unit_code ?: 'UNIT'))),
                $sequencesByUnit[$unitId]
            );

            DB::table('ict_requests')
                ->where('id', $request->id)
                ->update([
                    'form_number' => $identifier,
                    'subject' => $identifier,
                ]);
        }
    }

    public function down(): void
    {
    }
};
