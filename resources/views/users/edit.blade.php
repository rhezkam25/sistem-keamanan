<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('users.index') }}" class="text-gray-400 hover:text-gray-600">&larr;</a>
            <h2 class="font-semibold text-xl text-gray-800">Edit User: {{ $user->name }}</h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow p-6">
                <form method="POST" action="{{ route('users.update', $user) }}" class="space-y-5" x-data="{ role: '{{ old('role', $user->role) }}' }">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <x-input-label for="name" value="Nama Lengkap *" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required />
                            <x-input-error :messages="$errors->get('name')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="nip" value="NIP" />
                            <x-text-input id="nip" name="nip" type="text" class="mt-1 block w-full" :value="old('nip', $user->nip)" />
                            <x-input-error :messages="$errors->get('nip')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="email" value="Email *" />
                            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required />
                            <x-input-error :messages="$errors->get('email')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="phone" value="No. HP" />
                            <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $user->phone)" />
                            <x-input-error :messages="$errors->get('phone')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="jabatan" value="Jabatan" />
                            <x-text-input id="jabatan" name="jabatan" type="text" class="mt-1 block w-full" :value="old('jabatan', $user->jabatan)" />
                            <x-input-error :messages="$errors->get('jabatan')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="role" value="Role *" />
                            <select id="role" name="role" x-model="role" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" required>
                                <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="pejabat" {{ old('role', $user->role) === 'pejabat' ? 'selected' : '' }}>Pejabat</option>
                                <option value="staff" {{ old('role', $user->role) === 'staff' ? 'selected' : '' }}>Staff</option>
                                <option value="satpam" {{ old('role', $user->role) === 'satpam' ? 'selected' : '' }}>Satpam</option>
                            </select>
                            <x-input-error :messages="$errors->get('role')" class="mt-1" />
                        </div>
                        <div x-show="role === 'staff'" class="md:col-span-2">
                            <x-input-label for="pejabat_id" value="Pejabat Atasan *" />
                            <select id="pejabat_id" name="pejabat_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="">-- Pilih Pejabat --</option>
                                @foreach($pejabatList as $p)
                                    <option value="{{ $p->id }}" {{ old('pejabat_id', $user->pejabat_id) == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('pejabat_id')" class="mt-1" />
                        </div>
                        <div class="md:col-span-2">
                            <x-input-label for="password" value="Password Baru (kosongkan jika tidak diubah)" />
                            <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" />
                            <x-input-error :messages="$errors->get('password')" class="mt-1" />
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <a href="{{ route('users.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm">Batal</a>
                        <x-primary-button>Simpan Perubahan</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
