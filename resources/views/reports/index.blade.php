<x-app-layout>
    <div class="space-y-6">
        <x-card>
            <form method="GET" class="grid gap-4 md:grid-cols-4 xl:grid-cols-6">
                <x-select
                    name="module"
                    label="Modul"
                    :value="$module"
                    :options="[
                        'ict_requests' => 'Permintaan ICT',
                        'email_requests' => 'Permohonan Email',
                        'repair_requests' => 'Perbaikan ICT',
                        'incident_reports' => 'Insiden / BA',
                        'project_requests' => 'Project Request',
                    ]"
                />
                <x-input name="status" label="Status" :value="$status" />
                <x-input name="from" type="date" label="Dari Tanggal" :value="request('from')" />
                <x-input name="until" type="date" label="Sampai Tanggal" :value="request('until')" />
                <div class="flex items-end">
                    <x-button type="submit">Filter</x-button>
                </div>
                <div class="flex items-end gap-3">
                    <x-button :href="route('reports.monitoring-pp')" variant="secondary">Monitoring PP</x-button>
                    <x-button :href="route('reports.export.excel', request()->query())" variant="secondary">Excel</x-button>
                    <x-button :href="route('reports.export.pdf', request()->query())" variant="secondary">PDF</x-button>
                </div>
            </form>
        </x-card>

        <x-card title="Ringkasan" subtitle="{{ $summary['moduleLabel'] }}">
            <div class="text-3xl font-bold text-ink-900">{{ $summary['total'] }}</div>
            <p class="mt-2 text-sm text-ink-500">Total data sesuai filter saat ini.</p>
        </x-card>

        <x-card title="Data Report" subtitle="Hasil rekap siap diexport per modul">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-ink-50 text-left text-ink-500">
                        <tr>
                            @foreach ($headings as $heading)
                                <th class="px-4 py-3">{{ $heading }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100">
                        @forelse ($rows as $row)
                            <tr>
                                @foreach ($row as $cell)
                                    <td class="px-4 py-3">{{ $cell }}</td>
                                @endforeach
                            </tr>
                        @empty
                            <tr><td colspan="{{ count($headings) }}" class="px-4 py-6 text-center text-ink-500">Tidak ada data sesuai filter.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>
</x-app-layout>
