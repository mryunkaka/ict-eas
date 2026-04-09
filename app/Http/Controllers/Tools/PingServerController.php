<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PingServerController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()->isIctAdmin(), 403);

        return view('tools.ping.index');
    }

    public function check(Request $request): View
    {
        abort_unless($request->user()->isIctAdmin(), 403);

        $validated = $request->validate([
            'host' => ['required', 'string', 'max:255'],
            'port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'timeout' => ['nullable', 'integer', 'min:1', 'max:10'],
        ]);

        $host = $validated['host'];
        $port = (int) ($validated['port'] ?? 80);
        $timeout = (int) ($validated['timeout'] ?? 3);
        $resolvedIp = gethostbyname($host);
        $startedAt = microtime(true);
        $socket = @fsockopen($host, $port, $errorNumber, $errorMessage, $timeout);
        $latencyMs = (int) round((microtime(true) - $startedAt) * 1000);

        if ($socket) {
            fclose($socket);
        }

        return view('tools.ping.index', [
            'result' => [
                'host' => $host,
                'port' => $port,
                'timeout' => $timeout,
                'resolved_ip' => $resolvedIp !== $host ? $resolvedIp : null,
                'reachable' => (bool) $socket,
                'latency_ms' => $latencyMs,
                'message' => $socket ? 'Host dapat dijangkau.' : ($errorMessage ?: 'Host tidak dapat dijangkau.'),
                'error_code' => $errorNumber ?? null,
            ],
        ]);
    }
}
