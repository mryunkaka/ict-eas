@php
    $requestDetails = $requests->getCollection()->map(function ($request) {
        $globalQuotations = $request->quotations
            ->whereNull('ict_request_item_id')
            ->values()
            ->map(fn ($quotation) => [
                'vendor_name' => $quotation->vendor_name,
                'attachment_name' => $quotation->attachment_name,
                'attachment_url' => $quotation->attachment_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($quotation->attachment_path) : null,
                'is_image' => str_starts_with((string) $quotation->attachment_mime, 'image/'),
            ])
            ->all();

        return [
            'id' => $request->id,
            'subject' => $request->subject,
            'revision_number' => (int) $request->revision_number,
            'print_count' => (int) $request->print_count,
            'unit' => $request->unit?->name,
            'requester' => $request->requester?->name,
            'generated_pdf_url' => route('forms.ict-requests.pdf', $request),
            'copy_pdf_url' => route('forms.ict-requests.pdf', ['ictRequest' => $request, 'copy' => 1]),
            'print_url' => route('forms.ict-requests.print', $request),
            'edit_url' => route('forms.ict-requests.edit', $request),
            'upload_signed_url' => route('approvals.ict.update', $request),
            'upload_ppnk_url' => route('forms.ict-requests.ppnk.store', $request),
            'priority' => strtoupper($request->priority),
            'raw_status' => $request->status,
            'status' => $request->statusLabel(),
            'can_print' => $request->status === 'checked_by_asmen' && (auth()->user()->isIctAdmin() || auth()->user()->isStaffIct()),
            'requires_signature_upload' => $request->status === 'checked_by_asmen' && (int) $request->print_count > 0 && ! $request->final_signed_pdf_path,
            'can_upload_signed_pdf' => $request->status === 'checked_by_asmen' && (int) $request->print_count > 0 && auth()->user()->isIctAdmin() && ! $request->final_signed_pdf_path,
            'can_manage_ppnk' => $request->status === 'progress_ppnk' && auth()->user()->isIctAdmin(),
            'is_locked_after_asmen' => in_array($request->status, ['checked_by_asmen', 'progress_ppnk', 'completed'], true),
            'quotation_mode' => $request->quotation_mode,
            'created_at' => optional($request->created_at)->format('d M Y H:i'),
            'final_signed_pdf_name' => $request->final_signed_pdf_name,
            'final_signed_pdf_url' => $request->final_signed_pdf_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($request->final_signed_pdf_path) : null,
            'rejected_reason' => $request->rejected_reason,
            'revision_note' => $request->revision_note,
            'revision_attachment_name' => $request->revision_attachment_name,
            'revision_attachment_url' => $request->revision_attachment_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($request->revision_attachment_path) : null,
            'total_estimated_price' => $request->items->sum(fn ($item) => ((float) ($item->estimated_price ?? 0)) * ((int) ($item->quantity ?? 0))),
            'items' => $request->items->map(fn ($item) => [
                'id' => $item->id,
                'item_category' => $item->item_category,
                'item_name' => $item->item_name,
                'brand_type' => $item->brand_type,
                'quantity' => $item->quantity,
                'unit' => $item->unit,
                'estimated_price' => $item->estimated_price,
                'total_estimated_price' => ((float) ($item->estimated_price ?? 0)) * ((int) ($item->quantity ?? 0)),
                'notes' => $item->notes,
                'photo_name' => $item->photo_name,
                'photo_url' => $item->photo_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($item->photo_path) : null,
                'ppnk_number' => $item->ppnkDocument?->ppnk_number,
                'ppnk_attachment_name' => $item->ppnkDocument?->attachment_name,
                'ppnk_attachment_url' => $item->ppnkDocument?->attachment_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($item->ppnkDocument->attachment_path) : null,
                'ppnk_attachment_is_image' => str_starts_with((string) $item->ppnkDocument?->attachment_mime, 'image/'),
                'quotations' => $item->quotations->map(fn ($quotation) => [
                    'vendor_name' => $quotation->vendor_name,
                    'attachment_name' => $quotation->attachment_name,
                    'attachment_url' => $quotation->attachment_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($quotation->attachment_path) : null,
                    'is_image' => str_starts_with((string) $quotation->attachment_mime, 'image/'),
                ])->all(),
            ])->all(),
            'global_quotations' => $globalQuotations,
        ];
    })->values();
@endphp

<x-app-layout>
    <div
        x-data="{
            selectedIds: [],
            selectAllMatching: false,
            detailMap: @js($requestDetails->keyBy('id')),
            openDetailId: null,
            printTarget: null,
            ppnkTarget: null,
            openPrintModal(id) {
                const targetId = String(id);
                if ((this.detailMap[targetId]?.print_count ?? 0) > 0) {
                    this.printTarget = targetId;
                    this.submitPrint();
                    return;
                }
                this.printTarget = targetId;
            },
            closePrintModal() {
                this.printTarget = null;
            },
            submitPrint() {
                if (!this.printTarget) return;
                this.$refs.printForm.action = this.detailMap[this.printTarget]?.print_url;
                this.$refs.printForm.submit();
                this.closePrintModal();
            },
            openPpnkModal(id) {
                const targetId = String(id);
                this.ppnkTarget = targetId;
                this.$nextTick(() => {
                    if (!this.$refs.ppnkForm) return;
                    this.$refs.ppnkForm.action = this.detailMap[targetId]?.upload_ppnk_url ?? '';
                });
            },
            closePpnkModal() {
                this.ppnkTarget = null;
                if (this.$refs.ppnkForm) {
                    this.$refs.ppnkForm.reset();
                }
            },
            get allSelectedOnPage() {
                const pageIds = @js($requests->pluck('id')->map(fn ($id) => (string) $id)->values());
                return pageIds.length > 0 && pageIds.every((id) => this.selectedIds.includes(id));
            },
            togglePageSelection(event) {
                const pageIds = @js($requests->pluck('id')->map(fn ($id) => (string) $id)->values());
                if (event.target.checked) {
                    this.selectedIds = Array.from(new Set([...this.selectedIds, ...pageIds]));
                } else {
                    this.selectedIds = this.selectedIds.filter((id) => !pageIds.includes(id));
                    this.selectAllMatching = false;
                }
            },
            toggleSelectAllMatching() {
                this.selectAllMatching = !this.selectAllMatching;
            },
            openDetail(id) {
                this.openDetailId = String(id);
            },
            closeDetail() {
                this.openDetailId = null;
            },
            formatCurrency(value) {
                if (value === null || value === undefined || value === '') return '-';
                return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value);
            },
            defaultPpnkNumber(index) {
                return this.detailMap[this.ppnkTarget]?.items?.[index]?.ppnk_number ?? '';
            }
        }"
        class="space-y-6"
    >
        @if (session('status'))
            <x-alert>{{ session('status') }}</x-alert>
        @endif

        <x-card title="Permintaan Fasilitas ICT" subtitle="Admin ICT membuat berkas, approval manual berjenjang, PDF TTD lengkap diunggah ulang, lalu dilanjutkan data PPNK per barang">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                <form method="GET" class="grid gap-4 md:grid-cols-[minmax(0,1fr)_180px_160px_auto]">
                    <label class="block space-y-2">
                        <span class="text-sm font-medium text-ink-700">Pencarian</span>
                        <input name="search" value="{{ $filters['search'] }}" type="text" placeholder="Cari subjek, unit, pemohon, status" class="w-full rounded-2xl border border-ink-200 bg-white px-4 py-3 text-sm text-ink-900 outline-none transition placeholder:text-ink-500 focus:border-brand-500" />
                    </label>

                    <label class="block space-y-2">
                        <span class="text-sm font-medium text-ink-700">Tampilkan</span>
                        <select name="per_page" class="w-full rounded-2xl border border-ink-200 bg-white px-4 py-3 text-sm text-ink-900 outline-none transition focus:border-brand-500">
                            @foreach ([10, 20, 30, 50, 100] as $option)
                                <option value="{{ $option }}" @selected($perPage === $option)>{{ $option }} data</option>
                            @endforeach
                        </select>
                    </label>

                    <div class="flex items-end gap-2">
                        <x-button type="submit">
                            <x-heroicon-o-magnifying-glass class="mr-2 h-4 w-4" />
                            Cari
                        </x-button>
                        <x-button :href="route('forms.ict-requests.index')" variant="secondary">
                            <x-heroicon-o-arrow-path class="mr-2 h-4 w-4" />
                            Reset
                        </x-button>
                    </div>
                </form>

                <div class="flex flex-wrap gap-3">
                    <x-button :href="route('forms.ict-requests.export', request()->query())" variant="secondary">
                        <x-heroicon-o-arrow-down-tray class="mr-2 h-4 w-4" />
                        Export Excel
                    </x-button>
                    @if (auth()->user()->canCreateIctRequest())
                        <x-button :href="route('forms.ict-requests.create')">
                            <x-heroicon-o-plus class="mr-2 h-4 w-4" />
                            Buat Permintaan
                        </x-button>
                    @endif
                    <x-button :href="route('dashboard')" variant="secondary">Kembali</x-button>
                </div>
            </div>
        </x-card>

        <x-card title="Daftar Permintaan" :subtitle="'Total '.$requests->total().' data'">
            @if (auth()->user()->canCreateIctRequest())
                <form method="POST" action="{{ route('forms.ict-requests.bulk-destroy') }}" class="space-y-4" x-on:submit="if (!selectAllMatching && selectedIds.length === 0) { $event.preventDefault(); } else if (!confirm('Hapus data yang dipilih?')) { $event.preventDefault(); }">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="search" value="{{ $filters['search'] }}">
                    <input type="hidden" name="per_page" value="{{ $perPage }}">
                    <input type="hidden" name="sort" value="{{ $sort }}">
                    <input type="hidden" name="direction" value="{{ $direction }}">
                    <input type="hidden" name="select_all_matching" :value="selectAllMatching ? 1 : 0">

                    <template x-for="id in selectedIds" :key="id">
                        <input type="hidden" name="selected_ids[]" :value="id">
                    </template>

                    <div class="flex flex-col gap-3 rounded-2xl border border-ink-100 bg-ink-50/80 p-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="space-y-2 text-sm text-ink-600">
                            <label class="inline-flex items-center gap-3">
                                <input type="checkbox" :checked="allSelectedOnPage" x-on:change="togglePageSelection($event)" class="rounded border-ink-300 text-ink-900 focus:ring-ink-400" />
                                <span>Pilih semua data di halaman ini</span>
                            </label>

                            <div class="flex flex-wrap items-center gap-3">
                                <span x-text="`${selectedIds.length} data dipilih di halaman ini`"></span>
                                <button type="button" x-on:click="toggleSelectAllMatching()" class="inline-flex items-center gap-2 rounded-2xl border border-ink-200 bg-white px-3 py-2 text-xs font-semibold text-ink-700 transition hover:bg-ink-100">
                                    <x-heroicon-o-squares-plus class="h-4 w-4" />
                                    <span x-text="selectAllMatching ? 'Batalkan pilih semua hasil filter' : 'Pilih semua hasil filter di database'"></span>
                                </button>
                            </div>

                            <p x-show="selectAllMatching" class="text-xs text-brand-700">
                                Semua data hasil filter akan dipilih, termasuk data yang tidak tampil di halaman ini.
                            </p>
                        </div>

                        <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-danger-500 px-4 py-3 text-sm font-semibold text-white transition hover:opacity-95">
                            <x-heroicon-o-trash class="mr-2 h-4 w-4" />
                            Bulk Delete
                        </button>
                    </div>
                </form>
            @endif

            <x-table>
                <thead class="bg-ink-50 text-left text-ink-500">
                    <tr>
                        <th class="px-4 py-3">
                            <span class="sr-only">Select</span>
                        </th>
                        <th class="px-4 py-3">
                            <x-sort-link column="subject" label="Subjek" :sort="$sort" :direction="$direction" />
                        </th>
                        <th class="px-4 py-3">Unit</th>
                        <th class="px-4 py-3">Pemohon</th>
                        <th class="px-4 py-3">
                            <x-sort-link column="priority" label="Prioritas" :sort="$sort" :direction="$direction" />
                        </th>
                        <th class="px-4 py-3">
                            <x-sort-link column="status" label="Status" :sort="$sort" :direction="$direction" />
                        </th>
                        <th class="px-4 py-3">
                            <x-sort-link column="created_at" label="Tanggal" :sort="$sort" :direction="$direction" />
                        </th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-100">
                    @forelse ($requests as $request)
                        <tr class="align-top">
                            <td class="px-4 py-3">
                                <input type="checkbox" value="{{ $request->id }}" x-model="selectedIds" class="rounded border-ink-300 text-ink-900 focus:ring-ink-400" />
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-semibold text-ink-900">{{ $request->subject }}</div>
                                <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-ink-500">
                                    <span>{{ strtoupper($request->quotation_mode) }}</span>
                                    @if ($request->revision_number > 0)
                                        <span class="rounded-full bg-amber-100 px-2 py-1 font-semibold text-amber-700">Rev-{{ $request->revision_number }}</span>
                                    @endif
                                </div>
                                @if ($request->rejected_reason || $request->revision_note)
                                    <div class="mt-3 space-y-2 rounded-2xl border border-amber-200 bg-amber-50/80 p-3 text-xs text-ink-700">
                                        @if ($request->rejected_reason)
                                            <div>
                                                <span class="font-semibold text-danger-600">Reject:</span>
                                                {{ \Illuminate\Support\Str::limit($request->rejected_reason, 180) }}
                                            </div>
                                        @endif
                                        @if ($request->revision_note)
                                            <div>
                                                <span class="font-semibold text-amber-700">Revisi:</span>
                                                {{ \Illuminate\Support\Str::limit($request->revision_note, 180) }}
                                            </div>
                                        @endif
                                    </div>
                                @endif
                                @if ($request->revision_attachment_path)
                                    <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($request->revision_attachment_path) }}" target="_blank" class="mt-3 block">
                                        <img
                                            src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($request->revision_attachment_path) }}"
                                            alt="{{ $request->revision_attachment_name ?: 'Lampiran revisi' }}"
                                            class="h-20 w-28 rounded-2xl border border-amber-200 object-cover shadow-sm"
                                        />
                                    </a>
                                @endif
                            </td>
                            <td class="px-4 py-3">{{ $request->unit?->name }}</td>
                            <td class="px-4 py-3">{{ $request->requester?->name }}</td>
                            <td class="px-4 py-3"><x-badge variant="{{ $request->priority === 'urgent' ? 'warning' : 'default' }}">{{ strtoupper($request->priority) }}</x-badge></td>
                            <td class="px-4 py-3"><x-badge variant="{{ in_array($request->status, ['progress_ppnk'], true) || ($request->status === 'checked_by_asmen' && (int) $request->print_count > 0 && ! $request->final_signed_pdf_path) ? 'warning' : 'success' }}">{{ $request->statusLabel() }}</x-badge></td>
                            <td class="px-4 py-3">{{ $request->created_at?->format('d M Y') }}</td>
                            <td class="px-4 py-3">
                                <div class="ui-action-row justify-end">
                                    <x-button type="button" variant="action-neutral" x-on:click="openDetail('{{ $request->id }}')" title="Lihat detail">
                                        <x-heroicon-o-eye class="ui-action-icon" />
                                    </x-button>

                                    @if (auth()->user()->canCreateIctRequest() && !in_array($request->status, ['checked_by_asmen', 'progress_ppnk', 'completed'], true))
                                        <x-button :href="route('forms.ict-requests.edit', $request)" variant="action-neutral" title="Edit">
                                            <x-heroicon-o-pencil-square class="ui-action-icon" />
                                        </x-button>
                                    @endif

                                    @if (($request->status === 'checked_by_asmen') && (auth()->user()->isIctAdmin() || auth()->user()->isStaffIct()))
                                        <x-button type="button" variant="action-neutral" x-on:click="openPrintModal('{{ $request->id }}')" title="{{ (int) $request->print_count > 0 ? 'Print Ulang' : 'Print' }}">
                                            <x-heroicon-o-printer class="ui-action-icon" />
                                        </x-button>
                                    @endif

                                    @if ($request->status === 'checked_by_asmen' && (int) $request->print_count > 0 && auth()->user()->isIctAdmin() && ! $request->final_signed_pdf_path)
                                        <form method="POST" action="{{ route('approvals.ict.update', $request) }}" enctype="multipart/form-data" class="shrink-0">
                                            @csrf
                                            <input type="hidden" name="action" value="upload_signed_pdf">
                                            <input
                                                id="signed-list-pdf-{{ $request->id }}"
                                                type="file"
                                                name="signed_pdf"
                                                accept="application/pdf"
                                                class="sr-only"
                                                required
                                                onchange="if (this.files.length) { this.form.submit(); }"
                                            />
                                            <label for="signed-list-pdf-{{ $request->id }}" class="ui-action-button ui-action-button--upload cursor-pointer" title="Upload Form ICT Full TTD">
                                                <x-heroicon-o-arrow-up-tray class="ui-action-icon" />
                                                <span class="sr-only">Upload Form ICT Full TTD</span>
                                            </label>
                                        </form>
                                    @endif

                                    @if ($request->status === 'progress_ppnk' && auth()->user()->isIctAdmin())
                                        <x-button type="button" variant="action-review" x-on:click="openPpnkModal('{{ $request->id }}')" title="Upload Data PPNK/PPK">
                                            <x-heroicon-o-clipboard-document-list class="ui-action-icon" />
                                        </x-button>
                                    @endif

                                    @if (auth()->user()->canCreateIctRequest() && !in_array($request->status, ['checked_by_asmen', 'progress_ppnk', 'completed'], true))
                                        <form method="POST" action="{{ route('forms.ict-requests.bulk-destroy') }}" onsubmit="return confirm('Hapus permintaan ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="selected_ids[]" value="{{ $request->id }}">
                                            <x-button type="submit" variant="action-danger" title="Hapus">
                                                <x-heroicon-o-trash class="ui-action-icon" />
                                            </x-button>
                                        </form>
                                    @endif

                                    @if (auth()->user()->canPermanentDeleteIctRequest())
                                        <form method="POST" action="{{ route('forms.ict-requests.permanent-destroy', $request) }}" onsubmit="return confirm('Hapus data ini secara permanen? Tindakan ini tidak dapat dibatalkan.')">
                                            @csrf
                                            @method('DELETE')
                                            <x-button type="submit" variant="action-danger" title="Hapus Permanen">
                                                <x-heroicon-o-trash class="ui-action-icon" />
                                            </x-button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-4 py-6 text-center text-ink-500">Belum ada data.</td></tr>
                    @endforelse
                </tbody>
            </x-table>

            <div class="mt-6">
                {{ $requests->links() }}
            </div>
        </x-card>

        <div
            x-show="openDetailId"
            x-cloak
            x-transition.opacity.duration.200ms
            x-on:keydown.escape.window="closeDetail()"
            class="fixed inset-0 z-50 flex items-center justify-center bg-ink-900/50 p-4"
        >
            <div class="max-h-[90vh] w-full max-w-5xl overflow-hidden rounded-3xl bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-ink-100 px-6 py-4">
                    <div>
                        <h2 class="font-display text-xl font-semibold text-ink-900">Detail Form ICT</h2>
                        <p class="text-sm text-ink-500" x-text="`${detailMap[openDetailId]?.subject ?? ''} - ${detailMap[openDetailId]?.requester ?? '-'}`"></p>
                    </div>
                    <div class="flex items-center gap-2">
                        <a :href="detailMap[openDetailId]?.copy_pdf_url" target="_blank" class="inline-flex items-center gap-2 rounded-2xl border border-ink-200 px-4 py-2 text-sm font-semibold text-ink-700 transition hover:bg-ink-50">
                            <x-heroicon-o-document-text class="h-4 w-4" />
                            <span>View PDF Copy</span>
                        </a>
                        <template x-if="detailMap[openDetailId]?.can_print">
                            <button type="button" x-on:click="openPrintModal(openDetailId)" class="inline-flex items-center gap-2 rounded-2xl border border-ink-200 px-4 py-2 text-sm font-semibold text-ink-700 transition hover:bg-ink-50">
                                <x-heroicon-o-printer class="h-4 w-4" />
                                <span>Print</span>
                            </button>
                        </template>
                        <button type="button" x-on:click="closeDetail()" class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-ink-200 text-ink-600 transition hover:bg-ink-50">
                            <x-heroicon-o-x-mark class="h-5 w-5" />
                        </button>
                    </div>
                </div>

                <div class="max-h-[calc(90vh-80px)] space-y-6 overflow-y-auto px-6 py-5">
                    <div class="grid gap-4 md:grid-cols-5">
                        <div class="rounded-2xl border border-ink-100 bg-ink-50/60 p-4">
                            <div class="text-xs uppercase tracking-wide text-ink-400">Total Harga Permintaan</div>
                            <div class="mt-2 font-semibold text-ink-900" x-text="formatCurrency(detailMap[openDetailId]?.total_estimated_price)"></div>
                        </div>
                        <div class="rounded-2xl border border-ink-100 bg-ink-50/60 p-4">
                            <div class="text-xs uppercase tracking-wide text-ink-400">Unit</div>
                            <div class="mt-2 font-semibold text-ink-900" x-text="detailMap[openDetailId]?.unit ?? '-'"></div>
                        </div>
                        <div class="rounded-2xl border border-ink-100 bg-ink-50/60 p-4">
                            <div class="text-xs uppercase tracking-wide text-ink-400">Prioritas</div>
                            <div class="mt-2 font-semibold text-ink-900" x-text="detailMap[openDetailId]?.priority ?? '-'"></div>
                        </div>
                        <div class="rounded-2xl border border-ink-100 bg-ink-50/60 p-4">
                            <div class="text-xs uppercase tracking-wide text-ink-400">Status</div>
                            <div class="mt-2 font-semibold text-ink-900" x-text="detailMap[openDetailId]?.status ?? '-'"></div>
                        </div>
                        <div class="rounded-2xl border border-ink-100 bg-ink-50/60 p-4">
                            <div class="text-xs uppercase tracking-wide text-ink-400">Revisi</div>
                            <div class="mt-2 font-semibold text-ink-900" x-text="`Rev-${detailMap[openDetailId]?.revision_number ?? 0}`"></div>
                        </div>
                    </div>

                    <div x-show="detailMap[openDetailId]?.revision_note || detailMap[openDetailId]?.revision_attachment_url || detailMap[openDetailId]?.rejected_reason" x-cloak class="rounded-3xl border border-amber-200 bg-amber-50/80 p-5">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div class="space-y-3">
                                <div>
                                    <h3 class="font-display text-lg font-semibold text-ink-900">Feedback Approval</h3>
                                    <p class="text-sm text-ink-500">Catatan reject atau revisi terakhir dari approver ICT.</p>
                                </div>
                                <template x-if="detailMap[openDetailId]?.rejected_reason">
                                    <div>
                                        <div class="text-xs uppercase tracking-wide text-ink-400">Alasan Reject</div>
                                        <div class="mt-2 text-sm text-ink-800" x-text="detailMap[openDetailId]?.rejected_reason"></div>
                                    </div>
                                </template>
                                <template x-if="detailMap[openDetailId]?.revision_note">
                                    <div>
                                        <div class="text-xs uppercase tracking-wide text-ink-400">Catatan Revisi</div>
                                        <div class="mt-2 text-sm text-ink-800" x-text="detailMap[openDetailId]?.revision_note"></div>
                                    </div>
                                </template>
                            </div>

                            <template x-if="detailMap[openDetailId]?.revision_attachment_url">
                                <a :href="detailMap[openDetailId]?.revision_attachment_url" target="_blank" class="inline-flex items-center gap-2 rounded-2xl border border-amber-300 bg-white px-4 py-3 text-sm font-semibold text-amber-800 transition hover:bg-amber-100">
                                    <x-heroicon-o-paper-clip class="h-4 w-4" />
                                    <span x-text="detailMap[openDetailId]?.revision_attachment_name || 'Download Lampiran Revisi'"></span>
                                </a>
                            </template>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-ink-100 p-5">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="font-display text-lg font-semibold text-ink-900">Penawaran Global</h3>
                            <span class="text-xs uppercase tracking-wide text-ink-400" x-text="detailMap[openDetailId]?.quotation_mode === 'global' ? 'Aktif' : 'Tidak aktif'"></span>
                        </div>
                        <div class="space-y-3" x-show="detailMap[openDetailId]?.global_quotations?.length">
                            <template x-for="(quotation, index) in detailMap[openDetailId]?.global_quotations ?? []" :key="`global-${index}`">
                                <div class="rounded-2xl border border-ink-100 bg-ink-50/50 p-4">
                                    <div class="font-semibold text-ink-900" x-text="quotation.vendor_name || `Vendor ${index + 1}`"></div>
                                    <template x-if="quotation.attachment_url">
                                        <div class="mt-3 space-y-3">
                                            <a :href="quotation.attachment_url" target="_blank" class="inline-flex items-center gap-2 text-sm font-semibold text-brand-700 hover:text-brand-800">
                                                <x-heroicon-o-paper-clip class="h-4 w-4" />
                                                <span x-text="quotation.attachment_name || 'Buka lampiran'"></span>
                                            </a>
                                            <template x-if="quotation.is_image">
                                                <img :src="quotation.attachment_url" alt="" class="max-h-56 rounded-2xl border border-ink-100 object-cover" />
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                        <p x-show="!(detailMap[openDetailId]?.global_quotations?.length)" class="text-sm text-ink-500">Tidak ada penawaran global.</p>
                    </div>

                    <div class="rounded-3xl border border-ink-100 p-5">
                        <div class="mb-3 flex items-center gap-2 text-sm font-semibold text-ink-900">
                            <x-heroicon-o-document-arrow-up class="h-4 w-4" />
                            PDF Form ICT
                        </div>
                        <a :href="detailMap[openDetailId]?.copy_pdf_url" target="_blank" class="inline-flex items-center gap-2 text-sm font-semibold text-brand-700 hover:text-brand-800">
                            <x-heroicon-o-arrow-top-right-on-square class="h-4 w-4" />
                            <span>Lihat PDF copy hitam putih</span>
                        </a>
                        <div class="mt-4 border-t border-ink-100 pt-4">
                            <div class="mb-2 text-sm font-semibold text-ink-900">PDF Form ICT Full TTD</div>
                        <template x-if="detailMap[openDetailId]?.final_signed_pdf_url">
                            <a :href="detailMap[openDetailId]?.final_signed_pdf_url" target="_blank" class="inline-flex items-center gap-2 text-sm font-semibold text-brand-700 hover:text-brand-800">
                                <x-heroicon-o-arrow-top-right-on-square class="h-4 w-4" />
                                <span x-text="detailMap[openDetailId]?.final_signed_pdf_name || 'Buka PDF final'"></span>
                            </a>
                        </template>
                        <p x-show="!detailMap[openDetailId]?.final_signed_pdf_url" class="text-sm text-ink-500">Belum ada upload PDF TTD lengkap.</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <template x-for="(item, index) in detailMap[openDetailId]?.items ?? []" :key="`item-${index}`">
                            <div class="rounded-3xl border border-ink-100 p-5">
                                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                                    <div>
                                        <div class="text-xs uppercase tracking-wide text-ink-400">Kategori</div>
                                        <div class="mt-2 font-semibold text-ink-900" x-text="item.item_category || '-'"></div>
                                    </div>
                                    <div>
                                        <div class="text-xs uppercase tracking-wide text-ink-400">Nama Barang</div>
                                        <div class="mt-2 font-semibold text-ink-900" x-text="item.item_name || '-'"></div>
                                    </div>
                                    <div>
                                        <div class="text-xs uppercase tracking-wide text-ink-400">Merk / Tipe</div>
                                        <div class="mt-2 font-semibold text-ink-900" x-text="item.brand_type || '-'"></div>
                                    </div>
                                    <div>
                                        <div class="text-xs uppercase tracking-wide text-ink-400">Jumlah</div>
                                        <div class="mt-2 font-semibold text-ink-900" x-text="`${item.quantity || 0} ${item.unit || ''}`"></div>
                                    </div>
                                    <div>
                                        <div class="text-xs uppercase tracking-wide text-ink-400">Estimasi Harga</div>
                                        <div class="mt-2 font-semibold text-ink-900" x-text="formatCurrency(item.estimated_price)"></div>
                                    </div>
                                    <div>
                                        <div class="text-xs uppercase tracking-wide text-ink-400">Total Harga Barang</div>
                                        <div class="mt-2 font-semibold text-ink-900" x-text="formatCurrency(item.total_estimated_price)"></div>
                                    </div>
                                    <div>
                                        <div class="text-xs uppercase tracking-wide text-ink-400">Nomor PPNK / PPK</div>
                                        <div class="mt-2 font-semibold text-ink-900" x-text="item.ppnk_number || '-'"></div>
                                    </div>
                                    <div class="md:col-span-2 xl:col-span-3">
                                        <div class="text-xs uppercase tracking-wide text-ink-400">Keterangan</div>
                                        <div class="mt-2 text-sm text-ink-700" x-text="item.notes || '-'"></div>
                                    </div>
                                </div>

                                <div class="mt-5 grid gap-4 lg:grid-cols-[320px_minmax(0,1fr)]">
                                    <div class="rounded-2xl border border-ink-100 bg-ink-50/50 p-4">
                                        <div class="mb-3 flex items-center gap-2 text-sm font-semibold text-ink-900">
                                            <x-heroicon-o-photo class="h-4 w-4" />
                                            Foto Barang
                                        </div>
                                        <template x-if="item.photo_url">
                                            <div class="space-y-3">
                                                <img :src="item.photo_url" :alt="item.photo_name || item.item_name" class="max-h-64 w-full rounded-2xl border border-ink-100 object-cover" />
                                                <div class="text-sm text-ink-600" x-text="item.photo_name || item.item_name"></div>
                                            </div>
                                        </template>
                                        <p x-show="!item.photo_url" class="text-sm text-ink-500">Tidak ada foto.</p>
                                    </div>

                                    <div class="rounded-2xl border border-ink-100 bg-ink-50/50 p-4">
                                        <div class="mb-3 flex items-center gap-2 text-sm font-semibold text-ink-900">
                                            <x-heroicon-o-paper-clip class="h-4 w-4" />
                                            Penawaran Per Barang
                                        </div>
                                        <div class="space-y-3" x-show="item.quotations?.length">
                                            <template x-for="(quotation, quotationIndex) in item.quotations" :key="`item-quotation-${quotationIndex}`">
                                                <div class="rounded-2xl border border-ink-100 bg-white p-4">
                                                    <div class="font-semibold text-ink-900" x-text="quotation.vendor_name || `Vendor ${quotationIndex + 1}`"></div>
                                                    <template x-if="quotation.attachment_url">
                                                        <div class="mt-3 space-y-3">
                                                            <a :href="quotation.attachment_url" target="_blank" class="inline-flex items-center gap-2 text-sm font-semibold text-brand-700 hover:text-brand-800">
                                                                <x-heroicon-o-arrow-top-right-on-square class="h-4 w-4" />
                                                                <span x-text="quotation.attachment_name || 'Buka lampiran'"></span>
                                                            </a>
                                                            <template x-if="quotation.is_image">
                                                                <img :src="quotation.attachment_url" alt="" class="max-h-40 rounded-2xl border border-ink-100 object-cover" />
                                                            </template>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>
                                        </div>
                                        <p x-show="!(item.quotations?.length)" class="text-sm text-ink-500">Tidak ada penawaran per barang.</p>
                                    </div>
                                </div>

                                <div class="mt-4 rounded-2xl border border-ink-100 bg-ink-50/50 p-4">
                                    <div class="mb-3 flex items-center gap-2 text-sm font-semibold text-ink-900">
                                        <x-heroicon-o-clipboard-document-list class="h-4 w-4" />
                                        Dokumen PPNK / PPK
                                    </div>
                                    <template x-if="item.ppnk_attachment_url">
                                        <div class="space-y-3">
                                            <a :href="item.ppnk_attachment_url" target="_blank" class="inline-flex items-center gap-2 text-sm font-semibold text-brand-700 hover:text-brand-800">
                                                <x-heroicon-o-arrow-top-right-on-square class="h-4 w-4" />
                                                <span x-text="item.ppnk_attachment_name || 'Buka dokumen PPNK / PPK'"></span>
                                            </a>
                                            <template x-if="item.ppnk_attachment_is_image">
                                                <img :src="item.ppnk_attachment_url" alt="" class="max-h-40 rounded-2xl border border-ink-100 object-cover" />
                                            </template>
                                        </div>
                                    </template>
                                    <p x-show="!item.ppnk_attachment_url" class="text-sm text-ink-500">Belum ada dokumen PPNK / PPK untuk barang ini.</p>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <div
            x-show="ppnkTarget"
            x-cloak
            x-transition.opacity.duration.200ms
            x-on:keydown.escape.window="closePpnkModal()"
            class="fixed inset-0 z-[70] flex items-center justify-center bg-ink-900/50 p-4"
        >
            <div class="flex max-h-[90vh] w-full max-w-6xl flex-col overflow-hidden rounded-3xl bg-white shadow-2xl">
                <div class="flex items-start justify-between gap-4">
                    <div class="p-6 pb-0">
                        <h3 class="font-display text-xl font-semibold text-ink-900">Upload Data PPNK / PPK</h3>
                        <p class="mt-1 text-sm text-ink-500" x-text="detailMap[ppnkTarget]?.subject || ''"></p>
                        <p class="mt-2 text-xs text-ink-500">Isi nomor per barang. Jika nomor sama, cukup upload file pada salah satu baris dengan nomor yang sama.</p>
                    </div>
                    <button type="button" x-on:click="closePpnkModal()" class="m-6 inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl border border-ink-200 text-ink-600 transition hover:bg-ink-50">
                        <x-heroicon-o-x-mark class="h-5 w-5" />
                    </button>
                </div>

                <form x-ref="ppnkForm" method="POST" enctype="multipart/form-data" class="flex min-h-0 flex-1 flex-col">
                    @csrf

                    <div class="px-6 pb-4">
                        <div class="hidden overflow-x-auto rounded-3xl border border-ink-100 md:block">
                        <table class="min-w-full text-sm">
                            <thead class="bg-ink-50 text-left text-ink-500">
                                <tr>
                                    <th class="px-4 py-3">Nama Barang</th>
                                    <th class="px-4 py-3">Nomor PPNK / PPK</th>
                                    <th class="px-4 py-3">Upload Berkas</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-ink-100">
                                <template x-for="(item, index) in detailMap[ppnkTarget]?.items ?? []" :key="`ppnk-${item.id}`">
                                    <tr class="align-top">
                                        <td class="px-4 py-3">
                                            <input type="hidden" :name="`items[${index}][item_id]`" :value="item.id" />
                                            <div class="font-semibold text-ink-900" x-text="item.item_name"></div>
                                            <div class="mt-1 text-xs text-ink-500" x-text="item.brand_type || item.item_category || '-'"></div>
                                            <template x-if="item.ppnk_attachment_name">
                                                <div class="mt-2 text-xs text-amber-700">
                                                    File saat ini:
                                                    <a :href="item.ppnk_attachment_url" target="_blank" class="font-semibold hover:underline" x-text="item.ppnk_attachment_name"></a>
                                                </div>
                                            </template>
                                        </td>
                                        <td class="px-4 py-3">
                                            <input
                                                type="text"
                                                :name="`items[${index}][ppnk_number]`"
                                                :value="defaultPpnkNumber(index)"
                                                placeholder="Contoh: PPNK-001/ICT/2026"
                                                class="w-full rounded-2xl border border-ink-200 bg-white px-4 py-3 text-sm text-ink-900 outline-none transition focus:border-brand-500"
                                            />
                                        </td>
                                        <td class="px-4 py-3">
                                            <input
                                                type="file"
                                                :name="`items[${index}][ppnk_attachment]`"
                                                accept=".pdf,.jpg,.jpeg,.png,.webp"
                                                class="w-full rounded-2xl border border-ink-200 bg-white px-4 py-3 text-sm text-ink-900 outline-none transition file:mr-4 file:rounded-xl file:border-0 file:bg-ink-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-ink-700 hover:file:bg-ink-200 focus:border-brand-500"
                                            />
                                            <p class="mt-2 text-xs text-ink-500">Jika nomor PPNK/PPK sama dengan barang lain, cukup upload file pada salah satu baris dengan nomor yang sama.</p>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                        </div>

                        <div class="space-y-4 md:hidden">
                            <template x-for="(item, index) in detailMap[ppnkTarget]?.items ?? []" :key="`ppnk-mobile-${item.id}`">
                                <div class="rounded-3xl border border-ink-100 p-4">
                                    <input type="hidden" :name="`items[${index}][item_id]`" :value="item.id" />
                                    <div class="font-semibold text-ink-900" x-text="item.item_name"></div>
                                    <div class="mt-1 text-xs text-ink-500" x-text="item.brand_type || item.item_category || '-'"></div>
                                    <label class="mt-4 block space-y-2">
                                        <span class="text-xs font-semibold uppercase tracking-wide text-ink-400">Nomor PPNK / PPK</span>
                                        <input
                                            type="text"
                                            :name="`items[${index}][ppnk_number]`"
                                            :value="defaultPpnkNumber(index)"
                                            placeholder="Contoh: PPNK-001/ICT/2026"
                                            class="w-full rounded-2xl border border-ink-200 bg-white px-4 py-3 text-sm text-ink-900 outline-none transition focus:border-brand-500"
                                        />
                                    </label>
                                    <label class="mt-4 block space-y-2">
                                        <span class="text-xs font-semibold uppercase tracking-wide text-ink-400">Upload Berkas</span>
                                        <input
                                            type="file"
                                            :name="`items[${index}][ppnk_attachment]`"
                                            accept=".pdf,.jpg,.jpeg,.png,.webp"
                                            class="w-full rounded-2xl border border-ink-200 bg-white px-4 py-3 text-sm text-ink-900 outline-none transition file:mr-4 file:rounded-xl file:border-0 file:bg-ink-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-ink-700 hover:file:bg-ink-200 focus:border-brand-500"
                                        />
                                    </label>
                                    <template x-if="item.ppnk_attachment_name">
                                        <div class="mt-3 text-xs text-amber-700">
                                            File saat ini:
                                            <a :href="item.ppnk_attachment_url" target="_blank" class="font-semibold hover:underline" x-text="item.ppnk_attachment_name"></a>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="border-t border-ink-100 px-6 py-4">
                        <div class="flex flex-col gap-3 text-xs text-ink-500 sm:flex-row sm:items-center sm:justify-between">
                        <p>Berkas dapat berupa PDF atau gambar. Nomor yang sama akan memakai satu dokumen yang sama di database dan storage.</p>
                        <div class="flex justify-end gap-3">
                            <x-button type="button" variant="secondary" x-on:click="closePpnkModal()">Batal</x-button>
                            <x-button type="submit">Simpan PPNK / PPK</x-button>
                        </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <form x-ref="printForm" method="POST" target="_blank" class="hidden">
            @csrf
        </form>

        <div
            x-show="printTarget"
            x-cloak
            x-transition.opacity.duration.200ms
            class="fixed inset-0 z-[60] flex items-center justify-center bg-ink-900/50 p-4"
        >
            <div class="w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl">
                <h3 class="font-display text-lg font-semibold text-ink-900">Konfirmasi Print</h3>
                <p class="mt-3 text-sm text-ink-600">
                    Pastikan data form sudah benar. Cetak pertama akan membuka PDF asli. Cetak berikutnya otomatis menjadi hitam putih dengan watermark <strong>DOCUMENT COPY</strong>.
                </p>
                <div class="mt-5 flex justify-end gap-3">
                    <x-button type="button" variant="secondary" x-on:click="closePrintModal()">Batal</x-button>
                    <x-button type="button" x-on:click="submitPrint()">Print</x-button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
