<x-app-layout>
    <div class="space-y-6">
        <x-card>
            <form method="POST" action="{{ route('tools.ping.check') }}" class="grid gap-4 md:grid-cols-4">
                @csrf
                <x-input name="host" label="Host" :value="old('host', $result['host'] ?? null)" placeholder="example.internal" />
                <x-input name="port" label="Port" type="number" :value="old('port', $result['port'] ?? 80)" />
                <x-input name="timeout" label="Timeout (detik)" type="number" :value="old('timeout', $result['timeout'] ?? 3)" />
                <div class="flex items-end">
                    <x-button type="submit">Check</x-button>
                </div>
            </form>
        </x-card>

        @isset($result)
            <x-card title="Hasil Ping" subtitle="{{ $result['host'] }}:{{ $result['port'] }}">
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="space-y-2 text-sm text-ink-700">
                        <p>Status: <strong>{{ $result['reachable'] ? 'Reachable' : 'Unreachable' }}</strong></p>
                        <p>Latency: <strong>{{ $result['latency_ms'] }} ms</strong></p>
                        <p>Resolved IP: <strong>{{ $result['resolved_ip'] ?? '-' }}</strong></p>
                        <p>Code: <strong>{{ $result['error_code'] ?? 0 }}</strong></p>
                    </div>
                    <x-alert :variant="$result['reachable'] ? 'success' : 'warning'">{{ $result['message'] }}</x-alert>
                </div>
            </x-card>
        @endisset
    </div>
</x-app-layout>
