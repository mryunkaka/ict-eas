<x-app-layout>
    <div class="space-y-6">
        @if (session('status'))
            <x-alert>{{ session('status') }}</x-alert>
        @endif

        <x-card title="Approval Center" subtitle="Antrian approval per role untuk permintaan ICT dan email">
            <div class="grid gap-4 text-sm text-ink-700 md:grid-cols-3">
                <p>Unit Admin menyetujui tahap awal.</p>
                <p>HRGA memverifikasi kelengkapan dan kebutuhan bisnis.</p>
                <p>ICT Admin menutup proses menjadi approved atau completed.</p>
            </div>
        </x-card>

        <div class="grid gap-6 xl:grid-cols-2">
            <x-card title="Permintaan ICT" subtitle="Flow: submitted -> manager -> HRGA -> ICT">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-ink-50 text-left text-ink-500">
                            <tr>
                                <th class="px-4 py-3">Subjek</th>
                                <th class="px-4 py-3">Pemohon</th>
                                <th class="px-4 py-3">Unit</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink-100">
                            @forelse ($ictRequests as $item)
                                <tr>
                                    <td class="px-4 py-3">{{ $item->subject }}</td>
                                    <td class="px-4 py-3">{{ $item->requester?->name }}</td>
                                    <td class="px-4 py-3">{{ $item->unit?->name }}</td>
                                    <td class="px-4 py-3"><x-badge>{{ strtoupper($item->status) }}</x-badge></td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-2">
                                            <form method="POST" action="{{ route('approvals.ict.update', $item) }}">
                                                @csrf
                                                <input type="hidden" name="action" value="approve">
                                                <x-button type="submit">Approve</x-button>
                                            </form>
                                            <form method="POST" action="{{ route('approvals.ict.update', $item) }}">
                                                @csrf
                                                <input type="hidden" name="action" value="reject">
                                                <x-button type="submit" variant="danger">Reject</x-button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-4 py-6 text-center text-ink-500">Tidak ada antrian approval ICT untuk role ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>

            <x-card title="Permohonan Email" subtitle="Flow: submitted -> manager -> HRGA -> ICT">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-ink-50 text-left text-ink-500">
                            <tr>
                                <th class="px-4 py-3">Pemohon</th>
                                <th class="px-4 py-3">Email</th>
                                <th class="px-4 py-3">Unit</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink-100">
                            @forelse ($emailRequests as $item)
                                <tr>
                                    <td class="px-4 py-3">{{ $item->employee_name }}</td>
                                    <td class="px-4 py-3">{{ $item->requested_email }}</td>
                                    <td class="px-4 py-3">{{ $item->unit?->name }}</td>
                                    <td class="px-4 py-3"><x-badge variant="warning">{{ strtoupper($item->status) }}</x-badge></td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-2">
                                            <form method="POST" action="{{ route('approvals.email.update', $item) }}">
                                                @csrf
                                                <input type="hidden" name="action" value="approve">
                                                <x-button type="submit">Approve</x-button>
                                            </form>
                                            <form method="POST" action="{{ route('approvals.email.update', $item) }}">
                                                @csrf
                                                <input type="hidden" name="action" value="reject">
                                                <x-button type="submit" variant="danger">Reject</x-button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-4 py-6 text-center text-ink-500">Tidak ada antrian approval email untuk role ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>
    </div>
</x-app-layout>
