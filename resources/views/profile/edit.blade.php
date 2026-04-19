<x-app-layout>
    <div class="ui-page-workspace space-y-6">
        <x-card title="{{ __('Profile') }}" subtitle="Kelola data akun, password, dan penghapusan akun." class="mx-auto w-full max-w-5xl">
            <div class="space-y-6">
                <div class="rounded-2xl border border-ink-100 bg-white p-4 sm:p-6">
                    @include('profile.partials.update-profile-information-form')
                </div>

                <div class="rounded-2xl border border-ink-100 bg-white p-4 sm:p-6">
                    @include('profile.partials.update-password-form')
                </div>

                <div class="rounded-2xl border border-danger-100 bg-white p-4 sm:p-6">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </x-card>
    </div>
</x-app-layout>
