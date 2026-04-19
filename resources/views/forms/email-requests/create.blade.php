<x-app-layout>
    <div class="ui-page-workspace">
        <x-card title="Form Permohonan E-mail" subtitle="Penamaan dan akses mengikuti SOP-ICT-01" class="mx-auto w-full max-w-7xl">
            <form method="POST" action="{{ route('forms.email-requests.store') }}" x-data="persistedForm('email-request-create')" @submit="clearOnSubmit()" class="space-y-5">
                @csrf

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="min-w-0">
                        <x-input name="employee_name" label="Nama Pemohon" />
                    </div>
                    <div class="min-w-0">
                        <x-input name="department_name" label="Departemen" />
                    </div>
                    <div class="min-w-0">
                        <x-input name="job_title" label="Jabatan" />
                    </div>
                </div>

                <div class="min-w-0">
                    <x-textarea name="justification" label="Alasan Pengajuan" rows="4" />
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="min-w-0">
                        <x-input name="diketahui_dept_head_name" label="Diketahui - Nama Dept. Head" :value="$defaultApprovalProfile?->diketahui_dept_head_name" />
                    </div>
                    <div class="min-w-0">
                        <x-input name="diketahui_dept_head_title" label="Diketahui - Jabatan Dept. Head" :value="$defaultApprovalProfile?->diketahui_dept_head_title" />
                    </div>
                    <div class="min-w-0">
                        <x-input name="diketahui_div_head_name" label="Diketahui - Nama Div. Head" :value="$defaultApprovalProfile?->diketahui_div_head_name" />
                    </div>
                    <div class="min-w-0">
                        <x-input name="diketahui_div_head_title" label="Diketahui - Jabatan Div. Head" :value="$defaultApprovalProfile?->diketahui_div_head_title" />
                    </div>
                    <div class="min-w-0">
                        <x-input name="disetujui_hrga_head_name" label="Disetujui - Nama Div. Head HRGA EAS" :value="$defaultApprovalProfile?->disetujui_hrga_head_name" />
                    </div>
                    <div class="min-w-0">
                        <x-input name="disetujui_hrga_head_title" label="Disetujui - Jabatan Div. Head HRGA EAS" :value="$defaultApprovalProfile?->disetujui_hrga_head_title" />
                    </div>
                    <div class="min-w-0">
                        <x-input name="pelaksana_ict_head_name" label="Pelaksana - Nama Dept. Head ICT EAS" :value="$defaultApprovalProfile?->pelaksana_ict_head_name" />
                    </div>
                    <div class="min-w-0">
                        <x-input name="pelaksana_ict_head_title" label="Pelaksana - Jabatan Dept. Head ICT EAS" :value="$defaultApprovalProfile?->pelaksana_ict_head_title" />
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3 border-t border-ink-100 pt-4">
                    <x-button type="submit">Submit</x-button>
                    <x-button type="button" variant="secondary" @click="clearDraft()">Clear Data</x-button>
                    <x-button :href="route('forms.email-requests.index')" variant="secondary">Kembali</x-button>
                </div>
            </form>
        </x-card>
    </div>
</x-app-layout>
