<x-app-layout>
    <div class="space-y-6">
        @if (session('status'))
            <x-alert>{{ session('status') }}</x-alert>
        @endif

        <x-card title="Permohonan Email Account" subtitle="Sesuai SOP email: approval atasan, verifikasi HRGA, lalu proses ICT">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                <form method="GET" class="grid gap-4 md:grid-cols-[minmax(0,1fr)_180px_auto]">
                    <x-input name="search" label="Cari Data" :value="$search" />
                    <label class="block space-y-2">
                        <span class="text-sm font-medium text-ink-700">Tampilkan</span>
                        <select name="per_page" class="w-full rounded-2xl border border-ink-200 bg-white px-4 py-3 text-sm text-ink-900 outline-none transition focus:border-brand-500">
                            @foreach ([10, 20, 30, 50, 100] as $option)
                                <option value="{{ $option }}" @selected($perPage === $option)>{{ $option }} data</option>
                            @endforeach
                        </select>
                    </label>
                    <div class="flex items-end gap-2">
                        <x-button type="submit"><x-heroicon-o-magnifying-glass class="mr-2 h-4 w-4" />Cari</x-button>
                        <x-button :href="route('forms.email-requests.index')" variant="secondary"><x-heroicon-o-arrow-path class="mr-2 h-4 w-4" />Reset</x-button>
                    </div>
                </form>

                <x-button :href="route('forms.email-requests.create')"><x-heroicon-o-plus class="mr-2 h-4 w-4" />Buat Permohonan</x-button>
            </div>
        </x-card>

        <x-table>
            <thead class="bg-ink-50 text-left text-ink-500">
                <tr>
                    <th class="px-4 py-3"><x-sort-link column="employee_name" label="Pemohon" :sort="$sort" :direction="$direction" /></th>
                    <th class="px-4 py-3"><x-sort-link column="requested_email" label="Email" :sort="$sort" :direction="$direction" /></th>
                    <th class="px-4 py-3">Akses</th>
                    <th class="px-4 py-3"><x-sort-link column="status" label="Status" :sort="$sort" :direction="$direction" /></th>
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
