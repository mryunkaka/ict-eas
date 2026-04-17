<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class DbConnectionController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()->canManageUsers(), 403);

        $connectionName = Config::get('database.default');
        $connectionConfig = Config::get("database.connections.{$connectionName}", []);

        $result = [
            'connection' => $connectionName,
            'driver' => $connectionConfig['driver'] ?? '-',
            'host' => $connectionConfig['host'] ?? '-',
            'port' => $connectionConfig['port'] ?? '-',
            'database' => $connectionConfig['database'] ?? '-',
            'username' => $connectionConfig['username'] ?? '-',
            'password_masked' => $this->maskSecret((string) ($connectionConfig['password'] ?? '')),
            'status' => false,
            'message' => 'Belum dilakukan pengecekan.',
            'server_version' => null,
            'current_database' => null,
        ];

        try {
            $pdo = DB::connection($connectionName)->getPdo();
            $currentDatabase = DB::connection($connectionName)->selectOne('SELECT DATABASE() AS name');

            $result['status'] = true;
            $result['message'] = 'Koneksi database berhasil.';
            $result['server_version'] = $pdo->getAttribute(\PDO::ATTR_SERVER_VERSION);
            $result['current_database'] = $currentDatabase->name ?? null;
        } catch (Throwable $exception) {
            $result['message'] = $exception->getMessage();
        }

        return view('tools.db-connection.index', [
            'result' => $result,
        ]);
    }

    private function maskSecret(string $secret): string
    {
        if ($secret === '') {
            return '-';
        }

        if (strlen($secret) <= 4) {
            return str_repeat('*', strlen($secret));
        }

        return substr($secret, 0, 2).str_repeat('*', max(strlen($secret) - 4, 1)).substr($secret, -2);
    }
}
