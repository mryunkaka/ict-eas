<x-app-layout>
    <div class="space-y-6">
        @if (session('status'))
            <x-alert>{{ session('status') }}</x-alert>
        @endif

        <x-card title="{{ $incident->title }}" subtitle="Detail berita acara dan log maintenance CCTV">
            <div class="grid gap-4 text-sm text-ink-700 md:grid-cols-2">
                <p><strong>Jenis:</strong> {{ strtoupper($incident->incident_type) }}</p>
                <p><strong>Status:</strong> {{ strtoupper($incident->status) }}</p>
                <p><strong>Pelapor:</strong> {{ $incident->reporter?->name }}</p>
                <p><strong>Tanggal:</strong> {{ $incident->occurred_at?->format('d M Y H:i') }}</p>
                <p class="md:col-span-2"><strong>Deskripsi:</strong> {{ $incident->description }}</p>
                <p class="md:col-span-2"><strong>Follow Up:</strong> {{ $incident->follow_up ?: '-' }}</p>
            </div>
        </x-card>

        @if ($incident->incident_type === 'cctv_outage' && auth()->user()->isIctAdmin())
            <x-card title="Tambah Log Maintenance CCTV" subtitle="Dipakai untuk tracking diagnosa, repair, dan verifikasi recorder">
                <form method="POST" action="{{ route('forms.incidents.maintenance.store', $incident) }}" class="grid gap-4 md:grid-cols-2">
                    @csrf
                    <x-select
                        name="activity_type"
                        label="Jenis Aktivitas"
                        :options="[
                            'initial_response' => 'Initial Response',
                            'diagnostic' => 'Diagnostic',
                            'repair' => 'Repair',
                            'verification' => 'Verification',
                            'reopen' => 'Reopen',
                        ]"
                    />
                    <x-select
                        name="status_after"
                        label="Status Setelah Action"
                        :options="[
                            'open' => 'Open',
                            'on_progress' => 'On Progress',
                            'resolved' => 'Resolved',
                        ]"
                    />
                    <x-input name="performed_at" type="datetime-local" label="Waktu Action" />
                    <div class="md:col-span-2">
                        <x-textarea name="description" label="Deskripsi Action" rows="3" />
                    </div>
                    <div class="md:col-span-2">
                        <x-button type="submit">Tambah Log</x-button>
                    </div>
                </form>
            </x-card>
        @endif

        <x-card title="Log Maintenance" subtitle="Riwayat tindak lanjut incident CCTV">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-ink-50 text-left text-ink-500">
                        <tr>
                            <th class="px-4 py-3">Waktu</th>
                            <th class="px-4 py-3">Aktivitas</th>
                            <th class="px-4 py-3">Deskripsi</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">PIC</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100">
                        @forelse ($incident->maintenanceLogs as $log)
                            <tr>
                                <td class="px-4 py-3">{{ $log->performed_at?->format('d M Y H:i') }}</td>
                                <td class="px-4 py-3">{{ strtoupper($log->activity_type) }}</td>
                                <td class="px-4 py-3">{{ $log->description }}</td>
                                <td class="px-4 py-3">{{ strtoupper($log->status_after) }}</td>
                                <td class="px-4 py-3">{{ $log->handler?->name }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-6 text-center text-ink-500">Belum ada log maintenance.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>
</x-app-layout>
