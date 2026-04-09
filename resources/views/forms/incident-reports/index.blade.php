<x-app-layout>
    <div class="space-y-6">
        @if (session('status'))
            <x-alert>{{ session('status') }}</x-alert>
        @endif
        <x-card title="Berita Acara Kerusakan / Insiden ICT" subtitle="Dipakai juga untuk kejadian CCTV down sesuai SOP-ICT-05">
            <x-button :href="route('forms.incidents.create')">Buat Berita Acara</x-button>
        </x-card>
        <x-table>
            <thead class="bg-ink-50 text-left text-ink-500">
                <tr>
                    <th class="px-4 py-3">Judul</th>
                    <th class="px-4 py-3">Jenis</th>
                    <th class="px-4 py-3">Tanggal</th>
                    <th class="px-4 py-3">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-100">
                @forelse ($reports as $report)
                    <tr>
                        <td class="px-4 py-3">{{ $report->title }}</td>
                        <td class="px-4 py-3">{{ strtoupper($report->incident_type) }}</td>
                        <td class="px-4 py-3">{{ $report->occurred_at?->format('d M Y') }}</td>
                        <td class="px-4 py-3"><x-badge variant="warning">{{ strtoupper($report->status) }}</x-badge></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-ink-500">Belum ada data.</td></tr>
                @endforelse
            </tbody>
        </x-table>
        {{ $reports->links() }}
    </div>
</x-app-layout>
