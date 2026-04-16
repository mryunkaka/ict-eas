@php
    $categoryOptions = collect($itemCategories)->mapWithKeys(fn ($category) => [$category => $category])->all();
@endphp

<x-app-layout>
    <div class="space-y-6">
        <x-card :title="$formMode === 'edit' ? 'Edit Form Permintaan Fasilitas ICT' : 'Form Permintaan Fasilitas ICT'" subtitle="Draft tersimpan otomatis di browser sampai disubmit atau dihapus">
            <form
                method="POST"
                action="{{ $formAction }}"
                enctype="multipart/form-data"
                x-data="ictRequestForm({
                    formKey: @js($formKey),
                    ptaEnabled: @js($initialPtaEnabled),
                    quotationMode: @js($initialQuotationMode),
                    initialItems: @js($initialItems),
                    initialGlobalQuotations: @js($initialGlobalQuotations),
                })"
                x-on:submit="clearOnSubmit()"
                class="space-y-5"
            >
                @csrf
                @if ($formMode === 'edit')
                    @method('PUT')
                @endif
                <input type="hidden" name="is_pta_request" x-bind:value="ptaEnabled ? 1 : 0">
                <input type="hidden" name="quotation_mode" x-bind:value="quotationMode">

                @if ($formMode === 'edit' && ($ictRequest?->revision_note || $ictRequest?->revision_attachment_path || $ictRequest?->rejected_reason))
                    <div class="rounded-3xl border border-amber-200 bg-amber-50/80 p-5">
                        <div class="space-y-3">
                            <div>
                                <h3 class="font-display text-lg font-semibold text-ink-900">Feedback Approval Terakhir</h3>
                                <p class="mt-1 text-sm text-ink-500">Gunakan catatan ini sebagai acuan sebelum mengirim ulang form.</p>
                            </div>

                            @if ($ictRequest?->rejected_reason)
                                <div>
                                    <div class="text-xs uppercase tracking-wide text-ink-400">Alasan Reject</div>
                                    <div class="mt-2 text-sm text-ink-800">{{ $ictRequest->rejected_reason }}</div>
                                </div>
                            @endif

                            @if ($ictRequest?->revision_note)
                                <div>
                                    <div class="text-xs uppercase tracking-wide text-ink-400">Catatan Revisi</div>
                                    <div class="mt-2 text-sm text-ink-800">{{ $ictRequest->revision_note }}</div>
                                </div>
                            @endif

                            @if ($ictRequest?->revision_attachment_path)
                                <div>
                                    <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($ictRequest->revision_attachment_path) }}" target="_blank" class="inline-flex items-center gap-2 rounded-2xl border border-amber-300 bg-white px-4 py-3 text-sm font-semibold text-amber-800 transition hover:bg-amber-100">
                                        <x-heroicon-o-paper-clip class="h-4 w-4" />
                                        <span>{{ $ictRequest->revision_attachment_name ?: 'Download Lampiran Revisi' }}</span>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Info Pengguna, Dept, Tanggal, dan Subject --}}
                <div class="rounded-3xl border border-ink-100 bg-ink-50/50 p-4">
                    <div class="mb-3">
                        <h3 class="font-display text-sm font-semibold text-ink-700">Informasi Pemohon</h3>
                        <p class="text-xs text-ink-500 mt-0.5">Nama pengguna, departemen, tanggal, dan subjek dapat disesuaikan manual.</p>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                        <x-input name="requester_name" label="Pengguna" :value="$requesterName" />
                        <x-input name="department_name" label="Departemen" :value="$departmentName" />
                        <x-input name="needed_at" type="date" label="Tanggal" :value="$neededAt" />
                        <div class="space-y-1.5 md:col-span-2 xl:col-span-1">
                            <label for="subject" class="text-sm font-medium text-ink-700">
                                Subjek Form
                                <span class="ml-1 text-xs font-normal text-ink-400">(auto-generate, bisa diedit)</span>
                            </label>
                            <input
                                id="subject"
                                type="text"
                                name="subject"
                                value="{{ old('subject', $defaultSubject) }}"
                                class="w-full rounded-2xl border border-ink-200 bg-white px-4 py-3 text-sm text-ink-900 outline-none transition placeholder:text-ink-500 focus:border-brand-500"
                                placeholder="e.g. UNIT-FORM ICT-001"
                            >
                        </div>
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <x-select
                        name="request_category"
                        label="Kategori Form"
                        :options="['hardware' => 'Hardware', 'software' => 'Software', 'accessories' => 'Accessories']"
                        :value="old('request_category', $ictRequest?->request_category ?? 'hardware')"
                    />
                    <x-select name="priority" label="Urgensi" :options="['urgent' => 'Urgent', 'normal' => 'Normal']" :value="old('priority', $ictRequest?->priority ?? 'normal')" />
                </div>

                <x-textarea name="justification" label="Alasan Kebutuhan" rows="4" :value="old('justification', $ictRequest?->justification)" />

                <x-card title="Persetujuan Form ICT" subtitle="Otomatis mengikuti data approval terakhir dari unit yang sedang login, dan bisa disesuaikan bila ada perubahan">
                    <div class="grid gap-4 md:grid-cols-2">
                        <x-input name="drafted_by_name" label="Dibuat Oleh - Nama" :value="$approvalProfile?->drafted_by_name" />
                        <x-input name="drafted_by_title" label="Dibuat Oleh - Jabatan" :value="$approvalProfile?->drafted_by_title" />
                        <x-input name="acknowledged_by_name" label="Nama Div. Head" :value="$approvalProfile?->acknowledged_by_name" />
                        <x-input name="acknowledged_by_title" label="Jabatan Div. Head" :value="$approvalProfile?->acknowledged_by_title" />
                        <x-input name="approved_1_name" label="Nama Div. Head GA / FAT" :value="$approvalProfile?->approved_1_name" />
                        <x-input name="approved_1_title" label="Jabatan Div. Head GA / FAT" :value="$approvalProfile?->approved_1_title" />
                        <x-input name="approved_2_name" label="Nama Div. Head ICT" :value="$approvalProfile?->approved_2_name" />
                        <x-input name="approved_2_title" label="Jabatan Div. Head ICT" :value="$approvalProfile?->approved_2_title" />
                    </div>
                </x-card>

                <div class="rounded-3xl border border-ink-100 bg-ink-50/70 p-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="font-display text-lg font-semibold text-ink-900">Permohonan Pembuatan PTA</h3>
                            <p class="mt-1 text-sm text-ink-500">Aktifkan jika permintaan ini memerlukan lampiran tambahan anggaran dan blok persetujuan.</p>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            <button
                                type="button"
                                x-on:click="ptaEnabled = false"
                                x-bind:class="ptaEnabled ? 'bg-white text-ink-700 ring-1 ring-ink-200' : 'bg-ink-900 text-white'"
                                class="inline-flex items-center justify-center rounded-2xl px-4 py-3 text-sm font-semibold transition"
                            >
                                Tanpa PTA
                            </button>
                            <button
                                type="button"
                                x-on:click="ptaEnabled = true"
                                x-bind:class="ptaEnabled ? 'bg-brand-500 text-white' : 'bg-white text-ink-700 ring-1 ring-ink-200'"
                                class="inline-flex items-center justify-center rounded-2xl px-4 py-3 text-sm font-semibold transition"
                            >
                                Permohonan Pembuatan PTA
                            </button>
                        </div>
                    </div>
                </div>

                <div x-show="ptaEnabled" x-cloak class="space-y-5">
                    <div class="grid gap-4 md:grid-cols-2">
                        <x-textarea
                            name="additional_budget_reason"
                            label="Alasan Tambahan Anggaran"
                            rows="4"
                            :value="$ptaProfile?->additional_budget_reason"
                        />
                        <x-textarea
                            name="pta_budget_not_listed_reason"
                            label="Anggaran tidak dicantumkan ditahun ini, karena :"
                            rows="4"
                            :value="$ptaProfile?->pta_budget_not_listed_reason"
                        />
                    </div>

                    <x-textarea
                        name="pta_additional_budget_reason"
                        label="Tambahan anggaran diadakan karena :"
                        rows="4"
                        :value="$ptaProfile?->pta_additional_budget_reason"
                    />

                    <x-card title="Blok Penandatangan PTA" subtitle="Nilai terakhir akan otomatis terisi untuk pengajuan berikutnya sampai ada perubahan baru">
                        <div class="grid gap-4 md:grid-cols-2">
                            <x-input name="approved_3_name" label="Disetujui Oleh 3 - Nama" :value="$ptaProfile?->approved_3_name" />
                            <x-input name="approved_3_title" label="Disetujui Oleh 3 - Jabatan" :value="$ptaProfile?->approved_3_title" />
                            <x-input name="approved_4_name" label="Disetujui Oleh 4 - Nama" :value="$ptaProfile?->approved_4_name" />
                            <x-input name="approved_4_title" label="Disetujui Oleh 4 - Jabatan" :value="$ptaProfile?->approved_4_title" />
                        </div>
                    </x-card>
                </div>

                <x-card title="Penawaran Vendor" subtitle="Pilih apakah penawaran berlaku untuk seluruh form atau per barang. Setiap blok menyediakan 3 vendor.">
                    <div class="flex flex-wrap gap-3">
                        <button
                            type="button"
                            x-on:click="quotationMode = 'global'"
                            x-bind:class="quotationMode === 'global' ? 'bg-ink-900 text-white' : 'bg-white text-ink-700 ring-1 ring-ink-200'"
                            class="inline-flex items-center justify-center rounded-2xl px-4 py-3 text-sm font-semibold transition"
                        >
                            Global 1 Form
                        </button>
                        <button
                            type="button"
                            x-on:click="quotationMode = 'per_item'"
                            x-bind:class="quotationMode === 'per_item' ? 'bg-brand-500 text-white' : 'bg-white text-ink-700 ring-1 ring-ink-200'"
                            class="inline-flex items-center justify-center rounded-2xl px-4 py-3 text-sm font-semibold transition"
                        >
                            Per Barang
                        </button>
                    </div>

                    <div x-show="quotationMode === 'global'" x-cloak class="mt-5 rounded-3xl border border-ink-100 bg-white p-4 shadow-sm">
                        <div class="mb-4">
                            <h3 class="font-display text-base font-semibold text-ink-900">Penawaran Global</h3>
                            <p class="text-sm text-ink-500">Lampiran vendor ini berlaku untuk seluruh pengajuan ICT.</p>
                        </div>

                        <div class="space-y-4">
                            <template x-for="(quotation, quotationIndex) in globalQuotations" :key="`global-${quotationIndex}`">
                                <div class="grid gap-4 md:grid-cols-2">
                                    <label class="block space-y-2">
                                        <span class="text-sm font-medium text-ink-700" x-text="`Vendor ${quotationIndex + 1}`"></span>
                                        <input x-model="quotation.vendor_name" x-bind:name="`global_quotations[${quotationIndex}][vendor_name]`" type="text" class="w-full rounded-2xl border border-ink-200 bg-white px-4 py-3 text-sm text-ink-900 outline-none transition placeholder:text-ink-500 focus:border-brand-500" />
                                    </label>

                                                <label class="block space-y-2">
                                                    <span class="text-sm font-medium text-ink-700">Attach File</span>
                                                    <input x-bind:name="`global_quotations[${quotationIndex}][current_attachment_name]`" x-model="quotation.current_attachment_name" type="hidden">
                                                    <input x-bind:name="`global_quotations[${quotationIndex}][current_attachment_path]`" x-model="quotation.current_attachment_path" type="hidden">
                                                    <input x-bind:name="`global_quotations[${quotationIndex}][current_attachment_mime]`" x-model="quotation.current_attachment_mime" type="hidden">
                                                    <input x-bind:name="`global_quotations[${quotationIndex}][attachment]`" x-on:change="handleQuotationChange($event, quotation)" type="file" accept=".pdf,application/pdf" class="w-full rounded-2xl border border-ink-200 bg-white px-4 py-3 text-sm text-ink-900 outline-none transition file:mr-4 file:rounded-xl file:border-0 file:bg-ink-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-ink-700 hover:file:bg-ink-200 focus:border-brand-500" />
                                        <div class="text-xs text-ink-500">
                                            <p>Format: PDF saja. Nama file yang sama akan memakai file yang sudah ada.</p>
                                            <p x-show="quotation.attachment_label" x-text="quotation.attachment_label"></p>
                                            <p x-show="quotation.attachment_size_label" x-text="quotation.attachment_size_label"></p>
                                        </div>
                                    </label>
                                </div>
                            </template>
                        </div>
                    </div>
                </x-card>

                <x-card title="Detail Barang" subtitle="Bisa tambah lebih dari satu barang. Foto besar akan dikompres otomatis sebelum upload.">
                    @error('items')
                        <p class="text-sm text-danger-500">{{ $message }}</p>
                    @enderror

                    <div class="space-y-4">
                        <template x-for="(item, index) in items" :key="item._id">
                            <div class="rounded-3xl border border-ink-100 bg-white p-4 shadow-sm">
                                <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <h3 class="font-display text-base font-semibold text-ink-900" x-text="`Barang ${index + 1}`"></h3>
                                        <p class="text-sm text-ink-500">Isi detail barang dan lampiran pendukung jika diperlukan.</p>
                                    </div>
                                    <button
                                        type="button"
                                        x-on:click="removeItem(index)"
                                        class="inline-flex items-center justify-center rounded-2xl border border-danger-200 px-4 py-2 text-sm font-semibold text-danger-500 transition hover:bg-danger-50"
                                    >
                                        Hapus Barang
                                    </button>
                                </div>

                                <div class="grid gap-4 md:grid-cols-3">
                                    <input x-bind:name="`items[${index}][id]`" x-model="item.id" type="hidden">
                                    <label class="block space-y-2">
                                        <span class="text-sm font-medium text-ink-700">Kategori Barang</span>
                                        <select x-model="item.item_category" x-bind:name="`items[${index}][item_category]`" class="w-full rounded-2xl border border-ink-200 bg-white px-4 py-3 text-sm text-ink-900 outline-none transition focus:border-brand-500">
                                            <option value="">Pilih kategori</option>
                                            @foreach ($categoryOptions as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </label>

                                    <label class="block space-y-2 md:col-span-2">
                                        <span class="text-sm font-medium text-ink-700">Nama Barang</span>
                                        <input x-model="item.item_name" x-bind:name="`items[${index}][item_name]`" type="text" class="w-full rounded-2xl border border-ink-200 bg-white px-4 py-3 text-sm text-ink-900 outline-none transition placeholder:text-ink-500 focus:border-brand-500" />
                                        @error('items.0.item_name')
                                            <span x-show="index === 0" class="text-sm text-danger-500">{{ $message }}</span>
                                        @enderror
                                    </label>
                                </div>

                                <div class="mt-4 grid gap-4 md:grid-cols-2">
                                    <label class="block space-y-2">
                                        <span class="text-sm font-medium text-ink-700">Merk / Tipe</span>
                                        <input x-model="item.brand_type" x-bind:name="`items[${index}][brand_type]`" type="text" class="w-full rounded-2xl border border-ink-200 bg-white px-4 py-3 text-sm text-ink-900 outline-none transition placeholder:text-ink-500 focus:border-brand-500" />
                                    </label>

                                    <label class="block space-y-2">
                                        <span class="text-sm font-medium text-ink-700">Estimasi Harga</span>
                                        <input x-model="item.estimated_price" x-bind:name="`items[${index}][estimated_price]`" type="number" min="0" class="w-full rounded-2xl border border-ink-200 bg-white px-4 py-3 text-sm text-ink-900 outline-none transition placeholder:text-ink-500 focus:border-brand-500" />
                                    </label>
                                </div>

                                <div class="mt-4 grid gap-4 md:grid-cols-3">
                                    <label class="block space-y-2">
                                        <span class="text-sm font-medium text-ink-700">Jumlah</span>
                                        <input x-model="item.quantity" x-bind:name="`items[${index}][quantity]`" type="number" min="1" class="w-full rounded-2xl border border-ink-200 bg-white px-4 py-3 text-sm text-ink-900 outline-none transition placeholder:text-ink-500 focus:border-brand-500" />
                                    </label>

                                    <label class="block space-y-2">
                                        <span class="text-sm font-medium text-ink-700">Satuan</span>
                                        <input x-model="item.unit" x-bind:name="`items[${index}][unit]`" type="text" placeholder="pcs/unit/roll" class="w-full rounded-2xl border border-ink-200 bg-white px-4 py-3 text-sm text-ink-900 outline-none transition placeholder:text-ink-500 focus:border-brand-500" />
                                    </label>

                                    <label class="block space-y-2">
                                        <span class="text-sm font-medium text-ink-700">Nama Foto</span>
                                        <input x-model="item.photo_name" x-bind:name="`items[${index}][photo_name]`" type="text" maxlength="15" placeholder="Maksimal 15 huruf" class="w-full rounded-2xl border border-ink-200 bg-white px-4 py-3 text-sm text-ink-900 outline-none transition placeholder:text-ink-500 focus:border-brand-500" />
                                        <span class="text-xs text-ink-500">Maksimal 15 karakter.</span>
                                    </label>
                                </div>

                                <div class="mt-4 grid gap-4 md:grid-cols-2">
                                    <label class="block space-y-2">
                                        <span class="text-sm font-medium text-ink-700">Foto Barang</span>
                                        <input x-bind:name="`items[${index}][current_photo_name]`" x-model="item.current_photo_name" type="hidden">
                                        <input x-bind:name="`items[${index}][current_photo_path]`" x-model="item.current_photo_path" type="hidden">
                                        <input x-bind:name="`items[${index}][photo]`" x-on:change="handlePhotoChange($event, index)" type="file" accept="image/*" class="w-full rounded-2xl border border-ink-200 bg-white px-4 py-3 text-sm text-ink-900 outline-none transition file:mr-4 file:rounded-xl file:border-0 file:bg-ink-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-ink-700 hover:file:bg-ink-200 focus:border-brand-500" />
                                        <div class="text-xs text-ink-500">
                                            <p>Foto akan dikompres otomatis bila ukuran besar, target akhir di bawah 500 KB.</p>
                                            <p x-show="item.photo_label" x-text="item.photo_label"></p>
                                            <p x-show="item.photo_size_label" x-text="`Ukuran hasil kompres: ${item.photo_size_label}`"></p>
                                        </div>
                                    </label>

                                    <label class="block space-y-2">
                                        <span class="text-sm font-medium text-ink-700">Keterangan</span>
                                        <textarea x-model="item.item_notes" x-bind:name="`items[${index}][item_notes]`" rows="3" class="w-full rounded-2xl border border-ink-200 bg-white px-4 py-3 text-sm text-ink-900 outline-none transition placeholder:text-ink-500 focus:border-brand-500"></textarea>
                                    </label>
                                </div>

                                <div x-show="quotationMode === 'per_item'" x-cloak class="mt-4 rounded-3xl border border-ink-100 bg-ink-50/60 p-4">
                                    <div class="mb-4">
                                        <h4 class="font-display text-sm font-semibold text-ink-900">Penawaran Vendor Per Barang</h4>
                                        <p class="text-sm text-ink-500">Setiap barang bisa memiliki maksimal 3 vendor.</p>
                                    </div>

                                    <div class="space-y-4">
                                        <template x-for="(quotation, quotationIndex) in item.quotations" :key="`${item._id}-quotation-${quotationIndex}`">
                                            <div class="grid gap-4 md:grid-cols-2">
                                                <label class="block space-y-2">
                                                    <span class="text-sm font-medium text-ink-700" x-text="`Vendor ${quotationIndex + 1}`"></span>
                                                    <input x-model="quotation.vendor_name" x-bind:name="`items[${index}][quotations][${quotationIndex}][vendor_name]`" type="text" class="w-full rounded-2xl border border-ink-200 bg-white px-4 py-3 text-sm text-ink-900 outline-none transition placeholder:text-ink-500 focus:border-brand-500" />
                                                </label>

                                                <label class="block space-y-2">
                                                    <span class="text-sm font-medium text-ink-700">Attach File</span>
                                                    <input x-bind:name="`items[${index}][quotations][${quotationIndex}][current_attachment_name]`" x-model="quotation.current_attachment_name" type="hidden">
                                                    <input x-bind:name="`items[${index}][quotations][${quotationIndex}][current_attachment_path]`" x-model="quotation.current_attachment_path" type="hidden">
                                                    <input x-bind:name="`items[${index}][quotations][${quotationIndex}][current_attachment_mime]`" x-model="quotation.current_attachment_mime" type="hidden">
                                                    <input x-bind:name="`items[${index}][quotations][${quotationIndex}][attachment]`" x-on:change="handleQuotationChange($event, quotation)" type="file" accept=".pdf,application/pdf" class="w-full rounded-2xl border border-ink-200 bg-white px-4 py-3 text-sm text-ink-900 outline-none transition file:mr-4 file:rounded-xl file:border-0 file:bg-ink-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-ink-700 hover:file:bg-ink-200 focus:border-brand-500" />
                                                    <div class="text-xs text-ink-500">
                                                        <p>Format: PDF saja. Nama file yang sama akan memakai file yang sudah ada.</p>
                                                        <p x-show="quotation.attachment_label" x-text="quotation.attachment_label"></p>
                                                        <p x-show="quotation.attachment_size_label" x-text="quotation.attachment_size_label"></p>
                                                    </div>
                                                </label>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="mt-4">
                        <button
                            type="button"
                            x-on:click="addItem()"
                            class="inline-flex items-center justify-center rounded-2xl bg-ink-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-ink-800"
                        >
                            Tambah Barang
                        </button>
                    </div>
                </x-card>

                <div class="flex flex-wrap gap-3">
                    <x-button type="submit">{{ $formMode === 'edit' ? 'Update' : 'Submit' }}</x-button>
                    <x-button type="button" variant="secondary" x-on:click="clearDraft()">Clear Data</x-button>
                    <x-button :href="route('forms.ict-requests.index')" variant="secondary">Kembali</x-button>
                </div>
            </form>
        </x-card>
    </div>
</x-app-layout>
