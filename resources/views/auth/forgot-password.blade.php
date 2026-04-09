<x-guest-layout>
    <div class="space-y-5">
        <h2 class="font-display text-2xl font-bold text-ink-900">Reset Password</h2>
        <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
            @csrf
            <x-input name="email" type="email" label="Email" />
            <x-button type="submit">Kirim Link Reset</x-button>
        </form>
    </div>
</x-guest-layout>
