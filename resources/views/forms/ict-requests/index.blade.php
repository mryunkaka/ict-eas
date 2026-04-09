<x-app-layout>
    <div class="space-y-6">
        @if (session('status'))
            <x-alert>{{ session('status') }}</x-alert>
        @endif

        <x-card title="Permintaan Fasilitas ICT" subtitle="Mengikuti format FMR-ICT-01 dan approval lintas fungsi">
            <div class="flex flex-wrap gap-3">
                <x-button :href="route('forms.ict-requests.create')">Buat Permintaan</x-button>
                <x-button :href="route('dashboard')" variant="secondary">Kembali</x-button>
            </div>
        </x-card>

        <x-table>
            <thead class="bg-ink-50 text-left text-ink-500">
                <tr>
                    <th class="px-4 py-3">Subjek</th>
                    <th class="px-4 py-3">Unit</th>
                    <th class="px-4 py-3">Prioritas</th>
                    <th class="px-4 py-3">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-100">
                @forelse ($requests as $request)
                    <tr>
                        <td class="px-4 py-3">{{ $request->subject }}</td>
                        <td class="px-4 py-3">{{ $request->unit?->name }}</td>
                        <td class="px-4 py-3"><x-badge variant="{{ $request->priority === 'urgent' ? 'warning' : 'default' }}">{{ strtoupper($request->priority) }}</x-badge></td>
                        <td class="px-4 py-3"><x-badge variant="success">{{ strtoupper($request->status) }}</x-badge></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-ink-500">Belum ada data.</td></tr>
                @endforelse
            </tbody>
        </x-table>

        {{ $requests->links() }}
    </div>
</x-app-layout>
