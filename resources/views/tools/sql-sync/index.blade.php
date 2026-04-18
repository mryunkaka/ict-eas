<x-app-layout>
    <div class="space-y-6">
        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        <x-card title="Storage publik (upload)" subtitle="Total isi `storage/app/public` dan file terbesar.">
            <div class="grid gap-4 md:grid-cols-3 text-sm">
                <div class="rounded-2xl border border-ink-100 bg-ink-50/70 p-4">
                    <p class="text-xs font-medium uppercase tracking-wide text-ink-500">Total ukuran</p>
                    <p class="mt-1 text-lg font-semibold text-ink-900">{{ number_format($stats['total_bytes'] / 1024 / 1024, 2) }} MB</p>
                    <p class="mt-1 text-xs text-ink-600">{{ number_format($stats['file_count']) }} file</p>
                </div>
                <div class="rounded-2xl border border-ink-100 bg-ink-50/70 p-4 md:col-span-2">
                    <p class="text-xs font-medium uppercase tracking-wide text-ink-500">File terbesar</p>
                    @if ($stats['largest_path'])
                        <p class="mt-1 break-all font-mono text-xs text-ink-800">{{ $stats['largest_path'] }}</p>
                        <p class="mt-1 text-sm font-semibold text-ink-900">{{ number_format($stats['largest_bytes'] / 1024 / 1024, 2) }} MB</p>
                    @else
                        <p class="mt-1 text-ink-600">Belum ada file.</p>
                    @endif
                </div>
            </div>
            <p class="mt-4 text-xs text-ink-600">
                Kompres PDF saat upload: Ghostscript (paling ringan) jika tersedia; tanpa SSH/shared hosting tetap bisa lewat library PHP (FPDI+TCPDF di <code class="rounded bg-ink-100 px-1">vendor</code>).
                @if ($ghostscriptConfigured)
                    <span class="font-medium text-emerald-700">GS_BINARY terisi — Ghostscript dipakai jika binary valid.</span>
                @else
                    Tanpa <code class="rounded bg-ink-100 px-1">GS_BINARY</code>, kompresi mengandalkan PHP
                    @if ($pdfPhpRewriteEnabled)
                        <span class="font-medium text-emerald-700">(aktif — maks. halaman sesuai <code class="rounded bg-ink-100 px-1">PDF_COMPRESS_PHP_MAX_PAGES</code>).</span>
                    @else
                        — nonaktif (<code class="rounded bg-ink-100 px-1">PDF_COMPRESS_PHP_ENABLED=false</code>).
                    @endif
                @endif
            </p>
        </x-card>

        <x-card title="Jalankan SQL (phpMyAdmin-style)" subtitle="Tempel skrip dari docs/sql — UPDATE, DELETE, ALTER, CREATE TABLE, dll. Hindari operasi yang memutus koneksi.">
            @error('sql')
                <div class="mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ $message }}</div>
            @enderror
            <form method="POST" action="{{ route('tools.sql-sync.run') }}" class="space-y-3">
                @csrf
                <textarea name="sql" rows="14" class="w-full rounded-2xl border border-ink-200 bg-white px-4 py-3 font-mono text-xs text-ink-900 outline-none focus:border-brand-500" placeholder="-- Contoh: UPDATE ict_requests SET ...">{{ old('sql') }}</textarea>
                <div class="flex flex-wrap items-center gap-2">
                    <x-button type="submit" variant="secondary">Jalankan SQL</x-button>
                    <span class="text-xs text-ink-500">Diblok: DROP/CREATE DATABASE, GRANT, REVOKE, OUTFILE, LOAD DATA …</span>
                </div>
            </form>
        </x-card>

        <x-card title="Bersihkan file orphan" subtitle="File di folder upload ICT yang tidak punya referensi di database (sama seperti artisan storage:clean-orphaned-public-files).">
            @error('clean')
                <div class="mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ $message }}</div>
            @enderror

            @if (session('orphan_report'))
                @php $rep = session('orphan_report'); @endphp
                <div class="mb-4 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-950">
                    <p class="font-semibold">Dry run: {{ $rep['count'] }} file orphan</p>
                    @if (! empty($rep['paths']))
                        <ul class="mt-2 max-h-48 list-inside list-disc overflow-y-auto font-mono text-xs">
                            @foreach ($rep['paths'] as $p)
                                <li class="break-all">{{ $p }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endif

            <div class="flex flex-wrap gap-3">
                <form method="POST" action="{{ route('tools.sql-sync.clean-orphans') }}">
                    @csrf
                    <input type="hidden" name="dry_run" value="1">
                    <x-button type="submit" variant="secondary">Periksa orphan (dry-run)</x-button>
                </form>
                <form method="POST" action="{{ route('tools.sql-sync.clean-orphans') }}" class="flex flex-wrap items-center gap-2" onsubmit="return confirm('Hapus permanen file orphan yang terdeteksi?');">
                    @csrf
                    <input type="hidden" name="dry_run" value="0">
                    <input type="hidden" name="confirm_delete" value="1">
                    <x-button type="submit" variant="secondary">Hapus file orphan</x-button>
                </form>
            </div>
            <p class="mt-3 text-xs text-ink-600">Folder sementara <code class="rounded bg-ink-100 px-1">tmp-uploads/</code> tidak ikut dibersihkan otomatis.</p>
            @if ($orphanCount > 0)
                <p class="mt-2 text-xs font-medium text-amber-800">Saat ini terdeteksi ± {{ number_format($orphanCount) }} orphan (preview {{ count($orphanPreview) }} pertama di bawah).</p>
                <ul class="mt-2 max-h-32 list-inside list-disc overflow-y-auto font-mono text-[11px] text-ink-700">
                    @foreach ($orphanPreview as $p)
                        <li class="break-all">{{ $p }}</li>
                    @endforeach
                </ul>
            @else
                <p class="mt-2 text-xs text-emerald-700">Tidak ada orphan di folder terkelola.</p>
            @endif
        </x-card>

        <x-card title="Download SQL Data Sync" subtitle="Export data aplikasi saja dalam format SQL upsert untuk diimport ke database lokal lewat phpMyAdmin.">
            <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_20rem]">
                <div class="space-y-4 text-sm text-ink-700">
                    <div class="rounded-2xl border border-ink-100 bg-ink-50/70 p-4">
                        <p>Database aktif: <strong>{{ $databaseName ?: '-' }}</strong></p>
                        <p class="mt-2">File download hanya berisi data, bukan `CREATE TABLE` atau `ALTER TABLE`.</p>
                        <p class="mt-2">Metode import: setiap row menggunakan `INSERT ... ON DUPLICATE KEY UPDATE`, jadi row lama dengan primary key/unique key yang sama akan diperbarui otomatis.</p>
                    </div>

                    <div class="rounded-2xl border border-ink-100 bg-white p-4">
                        <h3 class="text-base font-semibold text-ink-900">Tabel yang disertakan</h3>
                        <div class="mt-3 overflow-hidden rounded-2xl border border-ink-100">
                            <table class="min-w-full divide-y divide-ink-100 text-sm">
                                <thead class="bg-ink-50 text-left text-ink-500">
                                    <tr>
                                        <th class="px-4 py-3">Tabel</th>
                                        <th class="px-4 py-3">Jumlah Data</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-ink-100">
                                    @forelse ($tables as $table)
                                        <tr>
                                            <td class="px-4 py-3 font-medium text-ink-900">{{ $table['name'] }}</td>
                                            <td class="px-4 py-3 text-ink-700">{{ number_format($table['rows']) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="px-4 py-6 text-center text-ink-500">Belum ada tabel aplikasi yang bisa diexport.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="rounded-3xl border border-ink-100 bg-white p-5 shadow-[0_20px_40px_-30px_rgba(17,32,51,0.35)]">
                        <h3 class="text-base font-semibold text-ink-900">Aksi</h3>
                        <p class="mt-2 text-sm text-ink-600">Unduh file SQL sinkronisasi data untuk import manual ke local database.</p>
                        <div class="mt-4">
                            <x-button :href="route('tools.sql-sync.download')" variant="secondary">
                                Download SQL Data
                            </x-button>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-brand-100 bg-brand-50/70 p-5 text-sm text-brand-700">
                        <p class="font-semibold">Catatan import</p>
                        <p class="mt-2">Schema lokal harus sudah sama dengan hosting.</p>
                        <p class="mt-2">File ini menambah data baru dan mengupdate data lama, tetapi tidak menghapus row yang sudah tidak ada di hosting.</p>
                    </div>
                </div>
            </div>
        </x-card>
    </div>
</x-app-layout>
