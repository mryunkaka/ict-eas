<x-app-layout>
    <div class="space-y-6">
        @if (session('status'))
            <x-alert>{{ session('status') }}</x-alert>
        @endif
        <x-card title="Pengajuan Project ICT">
            <x-button :href="route('forms.projects.create')">Buat Pengajuan</x-button>
        </x-card>
        <x-table>
            <thead class="bg-ink-50 text-left text-ink-500">
                <tr>
                    <th class="px-4 py-3">Judul</th>
                    <th class="px-4 py-3">Unit</th>
                    <th class="px-4 py-3">Prioritas</th>
                    <th class="px-4 py-3">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-100">
                @forelse ($projects as $project)
                    <tr>
                        <td class="px-4 py-3">{{ $project->title }}</td>
                        <td class="px-4 py-3">{{ $project->unit?->name }}</td>
                        <td class="px-4 py-3">{{ strtoupper($project->priority) }}</td>
                        <td class="px-4 py-3"><x-badge>{{ strtoupper($project->status) }}</x-badge></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-ink-500">Belum ada data.</td></tr>
                @endforelse
            </tbody>
        </x-table>
        {{ $projects->links() }}
    </div>
</x-app-layout>
