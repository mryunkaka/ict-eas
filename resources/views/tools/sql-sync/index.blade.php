<x-app-layout>
    <div class="space-y-6">
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
