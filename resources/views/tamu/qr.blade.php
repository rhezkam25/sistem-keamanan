<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('tamu.show', $tamu) }}" class="text-gray-400 hover:text-gray-600">&larr;</a>
            <h2 class="font-semibold text-xl text-gray-800">QR Code Tamu: {{ $tamu->nama }}</h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-md mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow p-8 text-center">
                <p class="text-sm text-gray-500 mb-2">Kode QR untuk</p>
                <h3 class="text-xl font-bold text-gray-800 mb-1">{{ $tamu->nama }}</h3>
                <p class="text-sm text-gray-500 mb-6">{{ $tamu->tujuan_kunjungan }}</p>

                <div class="flex justify-center mb-4">
                    {!! $qrCode !!}
                </div>

                <div class="bg-gray-100 rounded-lg px-4 py-3 mb-6">
                    <p class="text-xs text-gray-500 mb-1">Kode Manual</p>
                    <p class="text-2xl font-mono font-bold tracking-widest text-gray-800">{{ $tamu->qr_token }}</p>
                </div>

                <p class="text-xs text-gray-400 mb-6">Tunjukkan QR Code ini kepada petugas keamanan saat memasuki kantor.</p>

                <div class="flex gap-3 justify-center">
                    <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">Cetak QR Code</button>
                    <a href="{{ route('tamu.show', $tamu) }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm">Kembali</a>
                </div>
            </div>

            {{-- Info Tamu --}}
            <div class="bg-white rounded-lg shadow p-5 mt-4 text-sm">
                <dl class="space-y-2">
                    <div class="flex justify-between"><dt class="text-gray-500">No. KTP</dt><dd class="font-medium">{{ $tamu->nomor_id }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">No. HP</dt><dd class="font-medium">{{ $tamu->no_hp }}</dd></div>
                    @if($tamu->plat_kendaraan)
                    <div class="flex justify-between"><dt class="text-gray-500">Kendaraan</dt><dd class="font-medium">{{ $tamu->jenis_kendaraan }} — {{ $tamu->plat_kendaraan }}</dd></div>
                    @endif
                    <div class="flex justify-between"><dt class="text-gray-500">Disetujui oleh</dt><dd class="font-medium">{{ $tamu->pejabat->name }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Tanggal Disetujui</dt><dd class="font-medium">{{ $tamu->disetujui_pada?->format('d/m/Y H:i') }}</dd></div>
                </dl>
            </div>
        </div>
    </div>
</x-app-layout>
