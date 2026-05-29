<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Scan QR Code Tamu</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @include('partials.alert')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- Scan Form --}}
                <div class="space-y-4">
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-base font-semibold text-gray-700 mb-4">Input QR Code</h3>
                        <form method="POST" action="{{ route('scan.proses') }}" id="scanForm">
                            @csrf
                            <div class="mb-4">
                                <x-input-label for="qr_token" value="Kode QR atau Kode Manual" />
                                <x-text-input
                                    id="qr_token"
                                    name="qr_token"
                                    type="text"
                                    class="mt-1 block w-full text-center text-xl font-mono tracking-widest uppercase"
                                    placeholder="Scan atau ketik kode..."
                                    autofocus
                                    autocomplete="off"
                                />
                                <p class="mt-1 text-xs text-gray-400">Arahkan scanner ke QR Code, atau ketik kode 8 karakter secara manual.</p>
                            </div>
                            <x-primary-button class="w-full justify-center">Proses Check-in / Check-out</x-primary-button>
                        </form>
                    </div>

                    {{-- Scan Result --}}
                    @if(session('scan_success'))
                    <div class="bg-green-50 border border-green-200 rounded-lg p-5 text-center">
                        <div class="text-2xl font-bold text-green-700 mb-1">
                            {{ session('scan_jenis') === 'masuk' ? 'CHECK IN' : 'CHECK OUT' }}
                        </div>
                        <p class="text-green-800 font-semibold">{{ session('scan_tamu_nama') }}</p>
                        <p class="text-green-600 text-sm mt-1">{{ session('scan_tamu_tujuan') }}</p>
                    </div>
                    @endif
                </div>

                {{-- Log Kunjungan Hari Ini --}}
                <div class="bg-white rounded-lg shadow">
                    <div class="p-5 border-b flex justify-between items-center">
                        <h3 class="text-base font-semibold text-gray-700">Log Kunjungan Hari Ini</h3>
                        <span class="text-sm text-gray-400">{{ now()->format('d M Y') }}</span>
                    </div>
                    <div class="overflow-y-auto max-h-[500px]">
                        @if($kunjunganTerbaru->isEmpty())
                            <div class="p-8 text-center text-gray-400">Belum ada kunjungan hari ini.</div>
                        @else
                        <div class="divide-y divide-gray-100">
                            @foreach($kunjunganTerbaru as $k)
                            <div class="flex items-center gap-3 px-4 py-3">
                                <span class="px-2 py-1 rounded text-xs font-semibold min-w-[70px] text-center {{ $k->jenis === 'masuk' ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800' }}">
                                    {{ $k->jenis === 'masuk' ? 'IN' : 'OUT' }}
                                </span>
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-sm text-gray-800 truncate">{{ $k->tamu->nama }}</p>
                                    <p class="text-xs text-gray-400 truncate">{{ $k->tamu->tujuan_kunjungan }}</p>
                                </div>
                                <span class="text-xs text-gray-400 shrink-0">{{ $k->waktu_scan->format('H:i') }}</span>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Auto-submit saat kode QR berhasil discan (panjang 8 karakter)
    document.getElementById('qr_token').addEventListener('input', function() {
        const val = this.value.trim();
        if (val.length === 8) {
            setTimeout(() => document.getElementById('scanForm').submit(), 200);
        }
    });
    </script>
</x-app-layout>
