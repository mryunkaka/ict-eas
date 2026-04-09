<x-app-layout>
    <div class="space-y-6">
        @if (session('status'))
            <x-alert>{{ session('status') }}</x-alert>
        @endif

        <x-card title="Permohonan Email Account" subtitle="Sesuai SOP email: approval atasan, verifikasi HRGA, lalu proses ICT">
            <x-button :href="route('forms.email-requests.create')">Buat Permohonan</x-button>
        </x-card>

        <x-table>
            <thead class="bg-ink-50 text-left text-ink-500">
                <tr>
                    <th class="px-4 py-3">Pemohon</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3">Akses</th>
                    <th class="px-4 py-3">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-100">
                @forelse ($requests as $request)
                    <tr>
                        <td class="px-4 py-3">{{ $request->employee_name }}</td>
                        <td class="px-4 py-3">{{ $request->requested_email }}</td>
                        <td class="px-4 py-3">{{ strtoupper($request->access_level) }}</td>
                        <td class="px-4 py-3"><x-badge variant="warning">{{ strtoupper($request->status) }}</x-badge></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-ink-500">Belum ada data.</td></tr>
                @endforelse
            </tbody>
        </x-table>

        {{ $requests->links() }}
    </div>
</x-app-layout>
