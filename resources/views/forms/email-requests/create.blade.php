<x-app-layout>
    <x-card title="Form Permohonan E-mail" subtitle="Penamaan dan akses mengikuti SOP-ICT-01">
        <form method="POST" action="{{ route('forms.email-requests.store') }}" x-data="persistedForm('email-request-create')" @submit="clearOnSubmit()" class="space-y-5">
            @csrf
            <div class="grid gap-4 md:grid-cols-2">
                <x-input name="employee_name" label="Nama Karyawan" />
                <x-input name="department_name" label="Departemen" />
                <x-input name="job_title" label="Jabatan" />
                <x-input name="requested_email" type="email" label="Alamat Email" hint="Gunakan format nama depan.nama belakang atau namaunit@easgroup.co.id" />
            </div>

            <x-select name="access_level" label="Jenis Akses Pengiriman" :options="['internal' => 'Internal saja', 'external' => 'Internal + eksternal']" />
            <x-textarea name="justification" label="Alasan Pengajuan" rows="4" />

            <div class="flex flex-wrap gap-3">
                <x-button type="submit">Submit</x-button>
                <x-button type="button" variant="secondary" @click="clearDraft()">Clear Data</x-button>
                <x-button :href="route('forms.email-requests.index')" variant="secondary">Kembali</x-button>
            </div>
        </form>
    </x-card>
</x-app-layout>
