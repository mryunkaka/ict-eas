@php
    $reviewHistoryMap = $ictRequests->getCollection()
        ->mapWithKeys(function ($item) {
            $histories = $item->reviewHistories
                ->map(fn ($history) => [
                    'id' => $history->id,
                    'action' => $history->action,
                    'action_label' => $history->action === 'reject' ? 'Reject' : 'Revisi',
                    'note' => $history->note,
                    'attachment_name' => $history->attachment_name,
                    'attachment_url' => ($history->attachment_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($history->attachment_path))
                        ? \Illuminate\Support\Facades\Storage::disk('public')->url($history->attachment_path)
                        : null,
                    'reviewed_by' => $history->reviewer?->name,
                    'reviewed_at' => optional($history->reviewed_at)->format('d M Y H:i'),
                ])
                ->values()
                ->all();

            return [$item->id => $histories];
        });
@endphp

<x-app-layout>
    <div
        x-data="approvalCenter(@js($reviewHistoryMap))"
        class="space-y-6"
    >
        @if (session('status'))
            <x-alert>{{ session('status') }}</x-alert>
        @endif

        <x-card title="Approval Center" subtitle="Antrian validasi ICT sesuai role, lalu form dicetak manual setelah lolos validasi">
            <div class="grid gap-4 text-sm text-ink-700 md:grid-cols-3">
                <p>Admin ICT membuat berkas dan dapat mencetak form setelah validasi selesai.</p>
                <p>Staff ICT memvalidasi berkas terlebih dahulu.</p>
                <p>Asmen ICT memvalidasi tahap akhir sebelum form siap dicetak.</p>
            </div>
        </x-card>

        <div class="grid gap-6 xl:grid-cols-2">
            <x-card title="Permintaan ICT" subtitle="Flow: drafted -> validated staff -> validated asmen -> print manual">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-ink-50 text-left text-ink-500">
                            <tr>
                                <th class="px-4 py-3">Subjek</th>
                                <th class="px-4 py-3">Pemohon</th>
                                <th class="px-4 py-3">Unit</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Aksi</th>
                                <th class="px-4 py-3">Validasi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink-100">
                            @forelse ($ictRequests as $item)
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="font-semibold text-ink-900">{{ $item->subject }}</div>
                                        @if ($item->reviewHistories->isNotEmpty())
                                            <button
                                                type="button"
                                                class="mt-2 inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-800 transition hover:bg-amber-100"
                                                x-on:click="openHistoryModal('{{ $item->id }}', @js($item->subject))"
                                            >
                                                <x-heroicon-o-clock class="h-4 w-4" />
                                                <span>Riwayat Revisi {{ $item->reviewHistories->where('action', 'revise')->count() }}x</span>
                                            </button>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">{{ $item->requester?->name }}</td>
                                    <td class="px-4 py-3">{{ $item->unit?->name }}</td>
                                    <td class="px-4 py-3"><x-badge>{{ $item->statusLabel() }}</x-badge></td>
                                    <td class="px-4 py-3">
                                        @if ($item->status === 'checked_by_asmen' && auth()->user()->isIctAdmin())
                                            <div class="ui-action-row">
                                                <x-button :href="route('forms.ict-requests.pdf', $item)" variant="action-pdf" target="_blank" title="Cek PDF">
                                                    <x-heroicon-o-document-text class="ui-action-icon" />
                                                    <span class="sr-only">Lihat PDF</span>
                                                </x-button>
                                                <form method="POST" action="{{ route('approvals.ict.update', $item) }}" enctype="multipart/form-data" class="shrink-0">
                                                    @csrf
                                                    <input type="hidden" name="action" value="upload_signed_pdf">
                                                    <input
                                                        id="signed-pdf-{{ $item->id }}"
                                                        type="file"
                                                        name="signed_pdf"
                                                        accept="application/pdf"
                                                        class="sr-only"
                                                        required
                                                        onchange="if (this.files.length) { this.form.submit(); }"
                                                    />
                                                    <label for="signed-pdf-{{ $item->id }}" class="ui-action-button ui-action-button--upload cursor-pointer" title="Upload PDF final">
                                                        <x-heroicon-o-arrow-up-tray class="ui-action-icon" />
                                                        <span class="sr-only">Upload PDF final</span>
                                                    </label>
                                                </form>
                                                <x-button
                                                    type="button"
                                                    variant="action-review"
                                                    title="Review: reject atau revisi"
                                                    data-review-url="{{ route('approvals.ict.update', $item) }}"
                                                    data-review-subject="{{ $item->subject }}"
                                                    data-review-action="revise"
                                                    x-on:click.prevent.stop="openReviewModal($el.dataset.reviewUrl, $el.dataset.reviewSubject, $el.dataset.reviewAction)"
                                                >
                                                    <x-heroicon-o-pencil-square class="ui-action-icon" />
                                                    <span class="sr-only">Review</span>
                                                </x-button>
                                            </div>
                                        @else
                                            <div class="ui-action-row">
                                                <x-button :href="route('forms.ict-requests.pdf', $item)" variant="action-pdf" target="_blank" title="Cek PDF">
                                                    <x-heroicon-o-document-text class="ui-action-icon" />
                                                    <span class="sr-only">Lihat PDF</span>
                                                </x-button>
                                                <x-button
                                                    type="button"
                                                    variant="action-review"
                                                    title="Review: reject atau revisi"
                                                    data-review-url="{{ route('approvals.ict.update', $item) }}"
                                                    data-review-subject="{{ $item->subject }}"
                                                    data-review-action="revise"
                                                    x-on:click.prevent.stop="openReviewModal($el.dataset.reviewUrl, $el.dataset.reviewSubject, $el.dataset.reviewAction)"
                                                >
                                                    <x-heroicon-o-pencil-square class="ui-action-icon" />
                                                    <span class="sr-only">Review</span>
                                                </x-button>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if ($item->status === 'checked_by_asmen' && auth()->user()->isIctAdmin())
                                            <span class="text-xs text-ink-400">Sudah validasi</span>
                                        @else
                                            <form method="POST" action="{{ route('approvals.ict.update', $item) }}"
                                                  x-on:submit="if (!confirm('Apakah Anda yakin ingin memvalidasi form ICT ini?')) { $event.preventDefault(); }">
                                                @csrf
                                                <input type="hidden" name="action" value="approve">
                                                <x-button type="submit" variant="action-approve" title="{{ $item->status === 'drafted' ? 'Confirm Validate Staff ICT' : 'Confirm Validate Asmen ICT' }}">
                                                    <x-heroicon-o-check class="ui-action-icon" />
                                                    <span class="sr-only">{{ $item->status === 'drafted' ? 'Validasi Staff ICT' : 'Validasi Asmen ICT' }}</span>
                                                </x-button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-4 py-6 text-center text-ink-500">Tidak ada antrian approval ICT untuk role ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $ictRequests->links() }}
                </div>
            </x-card>

            <x-card title="Permohonan Email" subtitle="Flow email internal tetap diproses manual oleh role ICT yang berwenang">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-ink-50 text-left text-ink-500">
                            <tr>
                                <th class="px-4 py-3">Pemohon</th>
                                <th class="px-4 py-3">Email</th>
                                <th class="px-4 py-3">Unit</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Aksi</th>
                                <th class="px-4 py-3">Validasi</th>
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
                                        <span class="text-xs text-ink-400">-</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="ui-action-row">
                                            <form method="POST" action="{{ route('approvals.email.update', $item) }}"
                                                  x-on:submit="if (!confirm('Apakah Anda yakin ingin menyetujui permohonan email ini?')) { $event.preventDefault(); }">
                                                @csrf
                                                <input type="hidden" name="action" value="approve">
                                                <x-button type="submit" variant="action-approve" title="Approve">
                                                    <x-heroicon-o-check class="ui-action-icon" />
                                                    <span class="sr-only">Approve</span>
                                                </x-button>
                                            </form>
                                            <form method="POST" action="{{ route('approvals.email.update', $item) }}"
                                                  x-on:submit="if (!confirm('Apakah Anda yakin ingin menolak permohonan email ini?')) { $event.preventDefault(); }">
                                                @csrf
                                                <input type="hidden" name="action" value="reject">
                                                <x-button type="submit" variant="action-danger" title="Reject">
                                                    <x-heroicon-o-x-mark class="ui-action-icon" />
                                                    <span class="sr-only">Reject</span>
                                                </x-button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-4 py-6 text-center text-ink-500">Tidak ada antrian approval email untuk role ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $emailRequests->links() }}
                </div>
            </x-card>
        </div>

        <div
            x-show="reviewModalOpen"
            x-cloak
            x-transition.opacity.duration.200ms
            x-on:keydown.escape.window="closeReviewModal()"
            class="fixed inset-0 z-50 flex items-center justify-center bg-ink-900/50 p-4"
        >
            <div class="w-full max-w-2xl rounded-3xl bg-white p-6 shadow-2xl">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="font-display text-xl font-semibold text-ink-900">Review Permintaan ICT</h2>
                        <p class="mt-1 text-sm text-ink-500" x-text="reviewSubject"></p>
                    </div>
                    <button type="button" x-on:click="closeReviewModal()" class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-ink-200 text-ink-600 transition hover:bg-ink-50">
                        <x-heroicon-o-x-mark class="h-5 w-5" />
                    </button>
                </div>

                <form x-ref="reviewForm" method="POST" x-bind:action="reviewUrl" enctype="multipart/form-data" class="mt-6 space-y-4">
                    @csrf

                    <label class="block space-y-2">
                        <span class="text-sm font-medium text-ink-700">Aksi Review</span>
                        <select
                            name="action"
                            x-model="reviewAction"
                            class="w-full rounded-2xl border border-ink-200 bg-white px-4 py-3 text-sm text-ink-900 outline-none transition focus:border-brand-500"
                        >
                            <option value="revise">Revisi</option>
                            <option value="reject">Reject</option>
                        </select>
                    </label>

                    <label class="block space-y-2">
                        <span class="text-sm font-medium text-ink-700" x-text="reviewAction === 'reject' ? 'Alasan Reject' : 'Catatan Revisi'"></span>
                        <textarea
                            name="review_note"
                            rows="5"
                            class="w-full rounded-2xl border border-ink-200 bg-white px-4 py-3 text-sm text-ink-900 outline-none transition placeholder:text-ink-500 focus:border-brand-500"
                            x-bind:placeholder="reviewAction === 'reject' ? 'Jelaskan alasan permintaan ini ditolak' : 'Jelaskan bagian yang harus direvisi'"
                        ></textarea>
                    </label>

                    <div class="space-y-2">
                        <label class="block space-y-2">
                            <span class="text-sm font-medium text-ink-700">Lampiran Opsional</span>
                            <input
                                type="file"
                                name="revision_attachment"
                                accept=".jpg,.jpeg,.png,.webp"
                                class="w-full rounded-2xl border border-ink-200 bg-white px-4 py-3 text-sm text-ink-900 outline-none transition file:mr-4 file:rounded-xl file:border-0 file:bg-ink-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-ink-700 hover:file:bg-ink-200 focus:border-brand-500"
                            />
                        </label>
                        <p class="text-xs text-ink-500">Opsional. Lampiran hanya untuk foto/gambar coretan revisi (`jpg`, `jpeg`, `png`, `webp`) bila diperlukan. Jika tidak upload lampiran, cukup isi catatan revisi. Gambar yang diupload akan dikompres otomatis seperti foto barang pada form ICT.</p>
                    </div>

                    <div class="flex flex-wrap justify-end gap-3">
                        <x-button type="button" variant="secondary" x-on:click="closeReviewModal()">Batal</x-button>
                        <x-button type="submit" x-text="reviewAction === 'reject' ? 'Simpan Reject' : 'Kirim Revisi'"></x-button>
                    </div>
                </form>
            </div>
        </div>

        <div
            x-show="historyModalOpen"
            x-cloak
            x-transition.opacity.duration.200ms
            x-on:keydown.escape.window="closeHistoryModal()"
            class="fixed inset-0 z-50 flex items-center justify-center bg-ink-900/50 p-4"
        >
            <div class="w-full max-w-3xl rounded-3xl bg-white p-6 shadow-2xl">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="font-display text-xl font-semibold text-ink-900">Riwayat Revisi Permintaan ICT</h2>
                        <p class="mt-1 text-sm text-ink-500" x-text="historySubject"></p>
                    </div>
                    <button type="button" x-on:click="closeHistoryModal()" class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-ink-200 text-ink-600 transition hover:bg-ink-50">
                        <x-heroicon-o-x-mark class="h-5 w-5" />
                    </button>
                </div>

                <div class="mt-6 max-h-[70vh] space-y-4 overflow-y-auto pr-1">
                    <template x-if="currentHistoryItems.length === 0">
                        <div class="rounded-2xl border border-ink-100 bg-ink-50 px-4 py-5 text-sm text-ink-500">
                            Belum ada riwayat revisi sebelumnya.
                        </div>
                    </template>

                    <template x-for="history in currentHistoryItems" :key="history.id">
                        <div class="rounded-3xl border border-ink-100 p-5">
                            <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                <div>
                                    <div class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold"
                                         :class="history.action === 'reject' ? 'bg-danger-100 text-danger-500' : 'bg-amber-100 text-amber-800'">
                                        <span x-text="history.action_label"></span>
                                    </div>
                                    <div class="mt-3 text-sm text-ink-500">
                                        <span x-text="history.reviewed_by || 'System'"></span>
                                        <span x-show="history.reviewed_at"> • </span>
                                        <span x-text="history.reviewed_at || '-'"></span>
                                    </div>
                                </div>

                                <template x-if="history.attachment_url">
                                    <a :href="history.attachment_url" target="_blank" class="inline-flex items-center gap-2 rounded-2xl border border-amber-300 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-800 transition hover:bg-amber-100">
                                        <x-heroicon-o-paper-clip class="h-4 w-4" />
                                        <span x-text="history.attachment_name || 'Lampiran revisi'"></span>
                                    </a>
                                </template>
                            </div>

                            <div class="mt-4 text-sm text-ink-800" x-text="history.note || 'Tanpa catatan.'"></div>

                            <template x-if="history.attachment_url">
                                <a :href="history.attachment_url" target="_blank" class="mt-4 block">
                                    <img :src="history.attachment_url" :alt="history.attachment_name || 'Lampiran revisi'" class="max-h-52 rounded-2xl border border-amber-200 object-cover shadow-sm" />
                                </a>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
