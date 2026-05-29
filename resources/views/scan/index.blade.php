<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Scan QR Code Tamu</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @include('partials.alert')

            {{-- QR Hangus Notification --}}
            @if(session('qr_hangus'))
            <div class="mb-4 bg-orange-50 border border-orange-300 rounded-lg p-4 flex items-start gap-3">
                <svg class="w-6 h-6 text-orange-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                </svg>
                <div>
                    <p class="font-semibold text-orange-800">QR Code Tidak Aktif</p>
                    <p class="text-sm text-orange-700 mt-0.5">Kunjungan <strong>{{ session('qr_hangus_nama') }}</strong> sudah selesai. QR Code ini tidak dapat digunakan kembali.</p>
                </div>
            </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- Scan Form --}}
                <div class="space-y-4">
                    <div class="bg-white rounded-lg shadow p-6" x-data="scanPage()">

                        {{-- Tab Toggle --}}
                        <div class="flex gap-2 mb-5">
                            <button type="button"
                                @click="switchMode('manual')"
                                :class="mode === 'manual' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                                class="flex-1 py-2 rounded-lg text-sm font-medium transition">
                                ⌨ Kode Manual
                            </button>
                            <button type="button"
                                @click="switchMode('kamera')"
                                :class="mode === 'kamera' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                                class="flex-1 py-2 rounded-lg text-sm font-medium transition">
                                📷 Kamera
                            </button>
                        </div>

                        <form method="POST" action="{{ route('scan.proses') }}" id="scanForm">
                            @csrf

                            {{-- Mode: Manual --}}
                            <div x-show="mode === 'manual'">
                                <x-input-label for="qr_token" value="Kode QR atau Kode Manual" />
                                <x-text-input
                                    id="qr_token"
                                    name="qr_token"
                                    type="text"
                                    class="mt-1 block w-full text-center text-xl font-mono tracking-widest uppercase"
                                    placeholder="Scan atau ketik kode..."
                                    autofocus
                                    autocomplete="off"
                                    x-ref="tokenInput"
                                />
                                <p class="mt-1 text-xs text-gray-400">Arahkan scanner ke QR Code, atau ketik kode 8 karakter secara manual.</p>
                            </div>

                            {{-- Mode: Kamera --}}
                            <div x-show="mode === 'kamera'">
                                <div id="qr-reader" class="w-full rounded-lg overflow-hidden border border-gray-200 bg-gray-50" style="min-height: 250px;"></div>
                                <p class="mt-2 text-xs text-gray-400 text-center">Arahkan kamera ke QR Code tamu. Otomatis terdeteksi.</p>
                                <p class="mt-1 text-xs text-center font-mono tracking-widest" x-text="detectedCode ? 'Terdeteksi: ' + detectedCode : ''" :class="detectedCode ? 'text-blue-600' : 'text-gray-300'"></p>
                                {{-- Hidden input untuk mode kamera --}}
                                <input type="hidden" name="qr_token" id="qr_token_camera" />
                            </div>

                            <div class="mt-4" x-show="mode === 'manual'">
                                <x-primary-button class="w-full justify-center">Proses Check-in / Check-out</x-primary-button>
                            </div>
                        </form>
                    </div>

                    {{-- Scan Result --}}
                    @if(session('scan_success'))
                    <div class="rounded-lg p-5 text-center border
                        {{ session('scan_qr_hangus') ? 'bg-blue-50 border-blue-200' : 'bg-green-50 border-green-200' }}">
                        <div class="text-2xl font-bold mb-1
                            {{ session('scan_qr_hangus') ? 'text-blue-700' : 'text-green-700' }}">
                            {{ session('scan_jenis') === 'masuk' ? 'CHECK IN ✓' : 'CHECK OUT ✓' }}
                        </div>
                        <p class="font-semibold {{ session('scan_qr_hangus') ? 'text-blue-800' : 'text-green-800' }}">
                            {{ session('scan_tamu_nama') }}
                        </p>
                        <p class="text-sm mt-1 {{ session('scan_qr_hangus') ? 'text-blue-600' : 'text-green-600' }}">
                            {{ session('scan_tamu_tujuan') }}
                        </p>
                        @if(session('scan_qr_hangus'))
                        <p class="mt-2 text-xs text-orange-600 font-medium">QR Code telah dinonaktifkan</p>
                        @endif
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

    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
    function scanPage() {
        return {
            mode: 'manual',
            detectedCode: '',
            scanner: null,

            switchMode(newMode) {
                if (this.mode === newMode) return;
                if (this.mode === 'kamera') this.stopCamera();
                this.mode = newMode;
                this.detectedCode = '';
                if (newMode === 'kamera') {
                    this.$nextTick(() => this.startCamera());
                } else {
                    this.$nextTick(() => this.$refs.tokenInput?.focus());
                }
            },

            startCamera() {
                this.scanner = new Html5Qrcode('qr-reader');
                this.scanner.start(
                    { facingMode: 'environment' },
                    { fps: 10, qrbox: { width: 220, height: 220 } },
                    (decodedText) => {
                        const code = decodedText.trim().toUpperCase().substring(0, 8);
                        if (code.length === 8 && this.detectedCode !== code) {
                            this.detectedCode = code;
                            document.getElementById('qr_token_camera').value = code;
                            this.stopCamera();
                            setTimeout(() => document.getElementById('scanForm').submit(), 300);
                        }
                    },
                    () => {}
                ).catch(err => {
                    console.warn('Kamera tidak dapat diakses:', err);
                });
            },

            stopCamera() {
                if (this.scanner) {
                    this.scanner.stop().catch(() => {});
                    this.scanner = null;
                }
            },
        };
    }

    // Auto-submit saat kode QR berhasil discan via hardware scanner (mode manual)
    document.getElementById('qr_token').addEventListener('input', function() {
        const val = this.value.trim();
        if (val.length === 8) {
            setTimeout(() => document.getElementById('scanForm').submit(), 200);
        }
    });
    </script>
</x-app-layout>
