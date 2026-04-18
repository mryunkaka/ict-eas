<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Services\PublicDiskMaintenanceService;
use Illuminate\Http\RedirectResponse;
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

    public function index(Request $request, PublicDiskMaintenanceService $maintenance): View
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

        $stats = $maintenance->publicDiskStats();
        $orphanPaths = $maintenance->orphanPaths();

        return view('tools.sql-sync.index', [
            'databaseName' => $databaseName,
            'tables' => $tables,
            'stats' => $stats,
            'orphanCount' => count($orphanPaths),
            'orphanPreview' => array_slice($orphanPaths, 0, 50),
            'ghostscriptConfigured' => is_string(config('services.ghostscript.binary')) && config('services.ghostscript.binary') !== '',
            'pdfPhpRewriteEnabled' => (bool) config('services.pdf.php_rewrite.enabled', true),
        ]);
    }

    public function runSql(Request $request): RedirectResponse
    {
        abort_unless($request->user()->canManageUsers(), 403);

        $validated = $request->validate([
            'sql' => ['required', 'string', 'max:524288'],
        ]);

        $statements = $this->splitSqlStatements($validated['sql']);

        if ($statements === []) {
            return back()->withErrors(['sql' => 'Tidak ada pernyataan SQL yang valid.']);
        }

        foreach ($statements as $statement) {
            if ($this->isForbiddenStatement($statement)) {
                return back()->withErrors([
                    'sql' => 'Pernyataan ditolak demi keamanan: '.Str::limit($statement, 200),
                ]);
            }
        }

        foreach ($statements as $statement) {
            try {
                DB::unprepared($statement);
            } catch (\Throwable $e) {
                return back()->withErrors(['sql' => $e->getMessage()]);
            }
        }

        return back()->with('status', 'SQL berhasil dijalankan ('.count($statements).' pernyataan).');
    }

    public function cleanOrphans(Request $request, PublicDiskMaintenanceService $maintenance): RedirectResponse
    {
        abort_unless($request->user()->canManageUsers(), 403);

        $dryRun = (string) $request->input('dry_run', '1') !== '0';

        if (! $dryRun && ! $request->boolean('confirm_delete')) {
            return back()->withErrors(['clean' => 'Konfirmasi diperlukan untuk menghapus file orphan.']);
        }

        $result = $maintenance->deleteOrphans($dryRun);

        if ($dryRun) {
            return back()->with('orphan_report', [
                'dry_run' => true,
                'paths' => $result['paths'],
                'count' => count($result['paths']),
            ]);
        }

        return back()->with('status', "File orphan terhapus: {$result['deleted']} dari ".count($result['paths']).' terdeteksi.');
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
                            fn (string $column) => sprintf('`%s` = VALUES(`%s`)', $column, $column),
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
    private function splitSqlStatements(string $sql): array
    {
        $sql = preg_replace('/^\xEF\xBB\xBF/', '', $sql) ?? $sql;
        $sql = preg_replace('/\/\*[\s\S]*?\*\//', '', (string) $sql) ?? '';
        $lines = preg_split('/\R/', $sql) ?: [];
        $lines = array_map(function (string $line) {
            return preg_replace('/--.*$/', '', $line) ?? $line;
        }, $lines);

        $sql = implode("\n", $lines);

        $parts = preg_split('/;\s*(?=\R|$)/u', trim($sql)) ?: [];

        return collect($parts)
            ->map(fn ($s) => trim((string) $s))
            ->filter(fn (string $s) => $s !== '')
            ->values()
            ->all();
    }

    private function isForbiddenStatement(string $statement): bool
    {
        return (bool) preg_match(
            '/\b(?:DROP\s+DATABASE|CREATE\s+DATABASE|GRANT\b|REVOKE\b|INTO\s+OUTFILE|LOAD\s+DATA\b|SYSTEM\b)\b/i',
            $statement
        );
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
