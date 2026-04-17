<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SqlSyncController extends Controller
{
    /**
     * @var array<int, string>
     */
    private const EXPORT_TABLES = [
        'units',
        'users',
        'ict_requests',
        'ict_request_quotations',
        'ict_request_ppnk_documents',
        'ict_request_ppm_documents',
        'ict_request_po_documents',
        'ict_request_items',
        'ict_request_review_histories',
        'asset_handovers',
        'assets',
        'asset_lifecycle_logs',
        'cctv_maintenance_logs',
        'email_requests',
        'repair_requests',
        'incident_reports',
        'inventory_items',
        'project_requests',
    ];

    public function index(Request $request): View
    {
        abort_unless($request->user()->canManageUsers(), 403);

        $connection = DB::connection();
        $databaseName = (string) Config::get('database.connections.'.Config::get('database.default').'.database', '');

        $tables = collect(self::EXPORT_TABLES)
            ->filter(fn (string $table) => $this->tableExists($table))
            ->map(function (string $table) use ($connection) {
                return [
                    'name' => $table,
                    'rows' => (int) $connection->table($table)->count(),
                ];
            });

        return view('tools.sql-sync.index', [
            'databaseName' => $databaseName,
            'tables' => $tables,
        ]);
    }

    public function download(Request $request): StreamedResponse
    {
        abort_unless($request->user()->canManageUsers(), 403);

        $connection = DB::connection();
        $pdo = $connection->getPdo();
        $databaseName = (string) Config::get('database.connections.'.Config::get('database.default').'.database', 'database');
        $tables = collect(self::EXPORT_TABLES)
            ->filter(fn (string $table) => $this->tableExists($table))
            ->values()
            ->all();

        $fileName = sprintf(
            '%s-data-sync-%s.sql',
            Str::slug($databaseName ?: 'database'),
            now()->format('Ymd-His')
        );

        return response()->streamDownload(function () use ($connection, $pdo, $databaseName, $tables) {
            echo "-- SQL data sync export\n";
            echo "-- Database: {$databaseName}\n";
            echo '-- Generated at: '.now()->toDateTimeString()."\n";
            echo "-- Import target: database lokal dengan schema yang sudah sama\n\n";
            echo "SET FOREIGN_KEY_CHECKS=0;\n";
            echo "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n\n";

            foreach ($tables as $table) {
                $columns = $this->getColumnNames($table);
                $primaryKey = $this->getPrimaryKey($table);

                echo "-- --------------------------------------------------------\n";
                echo "-- Table: `{$table}`\n";
                echo "-- --------------------------------------------------------\n\n";

                if ($columns === []) {
                    echo "-- Skipped: no columns detected.\n\n";
                    continue;
                }

                $updateColumns = array_values(array_filter(
                    $columns,
                    fn (string $column) => $column !== $primaryKey
                ));

                $quotedColumns = implode(', ', array_map(
                    fn (string $column) => "`{$column}`",
                    $columns
                ));

                $rowsFound = false;
                $query = $connection->table($table);

                if ($primaryKey !== null) {
                    $query->orderBy($primaryKey);
                }

                $query->chunk(200, function ($rows) use ($table, $columns, $quotedColumns, $updateColumns, $pdo, &$rowsFound) {
                    foreach ($rows as $row) {
                        $rowsFound = true;
                        $data = (array) $row;
                        $values = implode(', ', array_map(
                            fn (string $column) => $this->quoteValue($pdo, $data[$column] ?? null),
                            $columns
                        ));

                        $updates = implode(', ', array_map(
                            fn (string $column) => sprintf("`%s` = VALUES(`%s`)", $column, $column),
                            $updateColumns
                        ));

                        if ($updates === '') {
                            $updates = sprintf('`%s` = `%s`', $columns[0], $columns[0]);
                        }

                        echo sprintf(
                            "INSERT INTO `%s` (%s) VALUES (%s) ON DUPLICATE KEY UPDATE %s;\n",
                            $table,
                            $quotedColumns,
                            $values,
                            $updates
                        );
                    }
                });

                if (! $rowsFound) {
                    echo "-- No rows exported.\n";
                }

                echo "\n";
            }

            echo "SET FOREIGN_KEY_CHECKS=1;\n";
        }, $fileName, [
            'Content-Type' => 'application/sql; charset=UTF-8',
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function getColumnNames(string $table): array
    {
        return collect(DB::select("SHOW COLUMNS FROM `{$table}`"))
            ->pluck('Field')
            ->map(fn ($column) => (string) $column)
            ->all();
    }

    private function getPrimaryKey(string $table): ?string
    {
        $key = collect(DB::select("SHOW KEYS FROM `{$table}` WHERE Key_name = 'PRIMARY'"))
            ->sortBy('Seq_in_index')
            ->first();

        return $key ? (string) $key->Column_name : null;
    }

    private function quoteValue(\PDO $pdo, mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        return $pdo->quote((string) $value);
    }

    private function tableExists(string $table): bool
    {
        return DB::getSchemaBuilder()->hasTable($table);
    }
}
