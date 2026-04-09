<x-app-layout>
    <div class="space-y-6">
        @if (session('status'))
            <x-alert>{{ session('status') }}</x-alert>
        @endif
        <x-card title="Permohonan Perbaikan ICT">
            <x-button :href="route('forms.repairs.create')">Buat Permohonan</x-button>
        </x-card>
        <x-table>
            <thead class="bg-ink-50 text-left text-ink-500">
                <tr>
                    <th class="px-4 py-3">Masalah</th>
                    <th class="px-4 py-3">Asset</th>
                    <th class="px-4 py-3">Prioritas</th>
                    <th class="px-4 py-3">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-100">
                @forelse ($requests as $request)
                    <tr>
                        <td class="px-4 py-3">{{ $request->problem_summary }}</td>
                        <td class="px-4 py-3">{{ $request->asset?->name ?? '-' }}</td>
                        <td class="px-4 py-3">{{ strtoupper($request->priority) }}</td>
                        <td class="px-4 py-3"><x-badge>{{ strtoupper($request->status) }}</x-badge></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-ink-500">Belum ada data.</td></tr>
                @endforelse
            </tbody>
        </x-table>
        {{ $requests->links() }}
    </div>
</x-app-layout>
