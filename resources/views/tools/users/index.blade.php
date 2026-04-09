<x-app-layout>
    <div class="space-y-6">
        @if (session('status'))
            <x-alert>{{ session('status') }}</x-alert>
        @endif

        <x-card title="Management User" subtitle="Kelola akun, role, status aktif, dan unit kerja">
            <form method="GET" class="grid gap-4 md:grid-cols-[1fr_auto]">
                <x-input name="search" label="Cari User" :value="request('search')" />
                <div class="flex items-end">
                    <x-button type="submit">Cari</x-button>
                </div>
            </form>
        </x-card>

        <div class="grid gap-6 xl:grid-cols-[1.3fr_1fr]">
            <x-card title="Daftar User" subtitle="Edit langsung role dan status aktif">
                <div class="space-y-4">
                    @foreach ($users as $managedUser)
                        <form method="POST" action="{{ route('tools.users.update', $managedUser) }}" class="rounded-2xl border border-ink-100 p-4">
                            @csrf
                            @method('PUT')
                            <div class="grid gap-4 md:grid-cols-2">
                                <x-input name="name" label="Nama" :value="$managedUser->name" />
                                <x-input name="email" label="Email" :value="$managedUser->email" />
                                <x-input name="employee_id" label="NIK" :value="$managedUser->employee_id" />
                                <x-input name="job_title" label="Jabatan" :value="$managedUser->job_title" />
                                <x-input name="phone" label="Telepon" :value="$managedUser->phone" />
                                <x-select name="unit_id" label="Unit" :options="$units->all()" :value="$managedUser->unit_id" />
                                <x-select name="role" label="Role" :options="$roles->all()" :value="$managedUser->role?->value" />
                                <x-input name="password" label="Password Baru" type="password" hint="Kosongkan jika tidak diubah." />
                            </div>
                            <label class="mt-4 flex items-center gap-2 text-sm text-ink-700">
                                <input type="checkbox" name="is_active" value="1" @checked($managedUser->is_active) />
                                User aktif
                            </label>
                            <div class="mt-4">
                                <x-button type="submit">Simpan Perubahan</x-button>
                            </div>
                        </form>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $users->links() }}
                </div>
            </x-card>

            <x-card title="Tambah User" subtitle="Seed manual untuk admin unit, HRGA, atau ICT">
                <form method="POST" action="{{ route('tools.users.store') }}" class="space-y-4">
                    @csrf
                    <x-input name="name" label="Nama" />
                    <x-input name="email" label="Email" />
                    <x-input name="employee_id" label="NIK" />
                    <x-input name="job_title" label="Jabatan" />
                    <x-input name="phone" label="Telepon" />
                    <x-select name="unit_id" label="Unit" :options="$units->all()" />
                    <x-select name="role" label="Role" :options="$roles->all()" />
                    <x-input name="password" label="Password" type="password" />
                    <label class="flex items-center gap-2 text-sm text-ink-700">
                        <input type="checkbox" name="is_active" value="1" checked />
                        User aktif
                    </label>
                    <x-button type="submit">Tambah User</x-button>
                </form>
            </x-card>
        </div>
    </div>
</x-app-layout>
