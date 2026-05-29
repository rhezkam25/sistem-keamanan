<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('tamu.index') }}" class="text-gray-400 hover:text-gray-600">&larr;</a>
                <h2 class="font-semibold text-xl text-gray-800">Detail Tamu: {{ $tamu->nama }}</h2>
            </div>
            <div class="flex gap-2">
                @if($tamu->disetujui())
                <a href="{{ route('tamu.qr', $tamu) }}" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium">Lihat QR Code</a>
                @endif
                @if($tamu->menunggu())
                <a href="{{ route('tamu.edit', $tamu) }}" class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 text-sm font-medium">Edit</a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @include('partials.alert')

            {{-- Status Badge --}}
            <div class="bg-white rounded-lg shadow p-5">
                <div class="flex items-center gap-4">
                    @if($tamu->menunggu())
                        <span class="px-4 py-2 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-800">Menunggu Persetujuan</span>
                    @elseif($tamu->disetujui())
                        <span class="px-4 py-2 rounded-full text-sm font-semibold bg-green-100 text-green-800">Disetujui</span>
                        <span class="text-sm text-gray-500">pada {{ $tamu->disetujui_pada?->format('d/m/Y H:i') }}</span>
                    @else
                        <span class="px-4 py-2 rounded-full text-sm font-semibold bg-red-100 text-red-800">Ditolak</span>
                    @endif
                </div>
                @if($tamu->catatan_pejabat)
                <p class="mt-3 text-sm text-gray-600"><span class="font-medium">Catatan Pejabat:</span> {{ $tamu->catatan_pejabat }}</p>
                @endif
            </div>

            {{-- Data Tamu --}}
            <div class="bg-white rounded-lg shadow p-5">
                <h3 class="text-base font-semibold text-gray-700 mb-4">Informasi Tamu</h3>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-gray-500">Nama Lengkap</dt><dd class="font-medium mt-1">{{ $tamu->nama }}</dd></div>
                    <div><dt class="text-gray-500">Nomor KTP/ID</dt><dd class="font-medium mt-1">{{ $tamu->nomor_id }}</dd></div>
                    <div><dt class="text-gray-500">No. HP</dt><dd class="font-medium mt-1">{{ $tamu->no_hp }}</dd></div>
                    <div><dt class="text-gray-500">Jenis Kendaraan</dt><dd class="font-medium mt-1">{{ $tamu->jenis_kendaraan ?? '-' }}</dd></div>
                    <div><dt class="text-gray-500">Plat Kendaraan</dt><dd class="font-medium mt-1">{{ $tamu->plat_kendaraan ?? '-' }}</dd></div>
                    <div><dt class="text-gray-500">Pejabat yang Dituju</dt><dd class="font-medium mt-1">{{ $tamu->pejabat->name }}</dd></div>
                    <div class="md:col-span-2"><dt class="text-gray-500">Tujuan Kunjungan</dt><dd class="font-medium mt-1">{{ $tamu->tujuan_kunjungan }}</dd></div>
                    <div><dt class="text-gray-500">Didaftarkan Oleh</dt><dd class="font-medium mt-1">{{ $tamu->pendaftar->name }}</dd></div>
                    <div><dt class="text-gray-500">Tanggal Daftar</dt><dd class="font-medium mt-1">{{ $tamu->created_at->format('d/m/Y H:i') }}</dd></div>
                </dl>
            </div>

            {{-- Riwayat Kunjungan --}}
            @if($tamu->kunjungan->isNotEmpty())
            <div class="bg-white rounded-lg shadow p-5">
                <h3 class="text-base font-semibold text-gray-700 mb-4">Riwayat Kunjungan</h3>
                <div class="space-y-3">
                    @foreach($tamu->kunjungan->sortByDesc('waktu_scan') as $k)
                    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $k->jenis === 'masuk' ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800' }}">
                            {{ $k->jenis === 'masuk' ? 'CHECK IN' : 'CHECK OUT' }}
                        </span>
                        <span class="text-sm text-gray-600">{{ $k->waktu_scan->format('d/m/Y H:i:s') }}</span>
                        <span class="text-sm text-gray-400">— {{ $k->petugas->name }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>
