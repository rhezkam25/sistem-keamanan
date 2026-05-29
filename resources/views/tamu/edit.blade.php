<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('tamu.show', $tamu) }}" class="text-gray-400 hover:text-gray-600">&larr;</a>
            <h2 class="font-semibold text-xl text-gray-800">Edit Data Tamu: {{ $tamu->nama }}</h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow p-6">
                <form method="POST" action="{{ route('tamu.update', $tamu) }}" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <x-input-label for="nama" value="Nama Lengkap *" />
                            <x-text-input id="nama" name="nama" type="text" class="mt-1 block w-full" :value="old('nama', $tamu->nama)" required />
                            <x-input-error :messages="$errors->get('nama')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="nomor_id" value="Nomor KTP/Identitas *" />
                            <x-text-input id="nomor_id" name="nomor_id" type="text" class="mt-1 block w-full" :value="old('nomor_id', $tamu->nomor_id)" required />
                            <x-input-error :messages="$errors->get('nomor_id')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="no_hp" value="Nomor HP *" />
                            <x-text-input id="no_hp" name="no_hp" type="text" class="mt-1 block w-full" :value="old('no_hp', $tamu->no_hp)" required />
                            <x-input-error :messages="$errors->get('no_hp')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="pejabat_id" value="Pejabat yang Dituju *" />
                            <select id="pejabat_id" name="pejabat_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" required>
                                @foreach($pejabatList as $p)
                                    <option value="{{ $p->id }}" {{ old('pejabat_id', $tamu->pejabat_id) == $p->id ? 'selected' : '' }}>{{ $p->name }} ({{ $p->jabatan }})</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('pejabat_id')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="jenis_kendaraan" value="Jenis Kendaraan" />
                            <x-text-input id="jenis_kendaraan" name="jenis_kendaraan" type="text" class="mt-1 block w-full" :value="old('jenis_kendaraan', $tamu->jenis_kendaraan)" />
                            <x-input-error :messages="$errors->get('jenis_kendaraan')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="plat_kendaraan" value="Plat Kendaraan" />
                            <x-text-input id="plat_kendaraan" name="plat_kendaraan" type="text" class="mt-1 block w-full" :value="old('plat_kendaraan', $tamu->plat_kendaraan)" />
                            <x-input-error :messages="$errors->get('plat_kendaraan')" class="mt-1" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="tujuan_kunjungan" value="Tujuan Kunjungan *" />
                        <textarea id="tujuan_kunjungan" name="tujuan_kunjungan" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" required>{{ old('tujuan_kunjungan', $tamu->tujuan_kunjungan) }}</textarea>
                        <x-input-error :messages="$errors->get('tujuan_kunjungan')" class="mt-1" />
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <a href="{{ route('tamu.show', $tamu) }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm">Batal</a>
                        <x-primary-button>Simpan Perubahan</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
