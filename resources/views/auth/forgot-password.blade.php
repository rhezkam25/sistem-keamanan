<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        Lupa kata sandi? Masukkan email atau username Anda. Permintaan akan dikirim ke Admin dan Admin akan mengganti password Anda.
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div>
            <x-input-label for="email" value="Email / Username" />
            <x-text-input id="email" class="block mt-1 w-full" type="text" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                Kirim Permintaan ke Admin
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
