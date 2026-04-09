<x-app-layout>
    <x-card title="Form Pengajuan Project ICT">
        <form method="POST" action="{{ route('forms.projects.store') }}" x-data="persistedForm('project-request-create')" @submit="clearOnSubmit()" class="space-y-5">
            @csrf
            <div class="grid gap-4 md:grid-cols-2">
                <x-input name="title" label="Judul Project" />
                <x-input name="target_date" type="date" label="Target Selesai" />
                <x-select name="priority" label="Prioritas" :options="['low' => 'Low', 'normal' => 'Normal', 'high' => 'High']" />
            </div>
            <x-textarea name="background" label="Latar Belakang" rows="4" />
            <x-textarea name="scope" label="Ruang Lingkup" rows="4" />
            <x-textarea name="expected_outcome" label="Output yang Diharapkan" rows="4" />
            <div class="flex flex-wrap gap-3">
                <x-button type="submit">Submit</x-button>
                <x-button type="button" variant="secondary" @click="clearDraft()">Clear Data</x-button>
            </div>
        </form>
    </x-card>
</x-app-layout>
