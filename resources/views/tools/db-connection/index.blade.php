<x-app-layout>
    <div class="space-y-6">
        <x-card title="Tes Koneksi Database" subtitle="Membaca konfigurasi database aktif dari Laravel dan mencoba membuka koneksi real-time.">
            <div class="grid gap-4 md:grid-cols-2">
                <div class="space-y-2 text-sm text-ink-700">
                    <p>Connection: <strong>{{ $result['connection'] }}</strong></p>
                    <p>Driver: <strong>{{ $result['driver'] }}</strong></p>
                    <p>Host: <strong>{{ $result['host'] }}</strong></p>
                    <p>Port: <strong>{{ $result['port'] }}</strong></p>
                    <p>Database: <strong>{{ $result['database'] }}</strong></p>
                    <p>Username: <strong>{{ $result['username'] }}</strong></p>
                    <p>Password: <strong>{{ $result['password_masked'] }}</strong></p>
                </div>

                <div class="space-y-3">
                    <x-alert :variant="$result['status'] ? 'success' : 'warning'">
                        {{ $result['message'] }}
                    </x-alert>

                    <div class="space-y-2 text-sm text-ink-700">
                        <p>Status: <strong>{{ $result['status'] ? 'Connected' : 'Failed' }}</strong></p>
                        <p>Server Version: <strong>{{ $result['server_version'] ?? '-' }}</strong></p>
                        <p>Current Database: <strong>{{ $result['current_database'] ?? '-' }}</strong></p>
                    </div>
                </div>
            </div>

            <div class="mt-6">
                <x-button :href="route('tools.db-connection.index')" variant="secondary">
                    Refresh Check
                </x-button>
            </div>
        </x-card>
    </div>
</x-app-layout>
