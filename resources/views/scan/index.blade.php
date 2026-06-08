<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Scan QR Code Tamu</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @include('partials.alert')

            {{-- QR Hangus Notification --}}
            @if(session('qr_hangus'))
            <div class="bg-orange-50 border border-orange-300 rounded-lg p-4 flex items-start gap-3">
                <svg class="w-6 h-6 text-orange-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                </svg>
                <div>
                    <p class="font-semibold text-orange-800">QR Code Tidak Aktif</p>
                    <p class="text-sm text-orange-700 mt-0.5">Kunjungan <strong>{{ session('qr_hangus_nama') }}</strong> sudah selesai. QR Code ini tidak dapat digunakan kembali.</p>
                </div>
            </div>
            @endif

            {{-- Baris atas: Form Scan + Panel Tamu Belum Keluar --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- ===== FORM SCAN QR ===== --}}
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

                        <form method="POST" action="{{ route('scan.proses') }}" id="scanForm"
                              @submit.prevent="submitForm">
                            @csrf

                            {{-- Mode: Manual --}}
                            <div x-show="mode === 'manual'">
                                <div class="flex justify-between items-center mb-1">
                                    <x-input-label for="qr_token" value="Kode QR atau Kode Manual" />
                                    <span class="text-xs font-mono"
                                        :class="manualLen === 8 ? 'text-green-600 font-semibold' : 'text-gray-400'"
                                        x-text="manualLen + '/8'"></span>
                                </div>
                                <x-text-input
                                    id="qr_token"
                                    name="qr_token"
                                    type="text"
                                    class="mt-1 block w-full text-center text-xl font-mono tracking-widest uppercase"
                                    x-bind:class="inputError ? 'border-red-400 focus:border-red-500 focus:ring-red-500' : ''"
                                    placeholder="Ketik kode 8 karakter..."
                                    value="{{ old('qr_token') }}"
                                    autocomplete="off"
                                    x-ref="tokenInput"
                                    maxlength="8"
                                    @input="onManualInput($event)"
                                />
                                {{-- Error inline --}}
                                <p x-show="inputError" x-text="inputError"
                                   class="mt-1 text-xs text-red-600 font-medium"></p>
                                <p x-show="!inputError" class="mt-1 text-xs text-gray-400">
                                    Ketik kode 8 karakter lalu klik tombol di bawah.
                                </p>
                            </div>

                            {{-- Mode: Kamera --}}
                            <div x-show="mode === 'kamera'">
                                <div id="qr-reader" class="w-full rounded-lg overflow-hidden border border-gray-200 bg-gray-50" style="min-height: 250px;"></div>
                                <p class="mt-2 text-xs text-gray-400 text-center">Arahkan kamera ke QR Code tamu.</p>

                                {{-- Error kamera --}}
                                <p x-show="cameraError" x-text="cameraError"
                                   class="mt-2 text-xs text-red-600 text-center font-medium"></p>

                                {{-- Kode terdeteksi + konfirmasi --}}
                                <div x-show="detectedCode" class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg text-center">
                                    <p class="text-xs text-blue-500 mb-1">Kode terdeteksi:</p>
                                    <p class="text-lg font-mono font-bold tracking-widest text-blue-700" x-text="detectedCode"></p>
                                    <button type="button" @click="resetCamera()" class="mt-2 text-xs text-gray-400 underline">Scan ulang</button>
                                </div>

                            </div>

                            {{-- Inline error dari server --}}
                            @if(session('scan_error'))
                            <div class="mt-3 p-3 bg-red-50 border border-red-300 rounded-lg flex items-start gap-2">
                                <svg class="w-4 h-4 text-red-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <p class="text-sm text-red-800 font-medium">{{ session('scan_error') }}</p>
                            </div>
                            @endif

                            <div class="mt-4">
                                <x-primary-button
                                    id="btnProses"
                                    type="submit"
                                    class="w-full justify-center"
                                    x-bind:disabled="mode === 'kamera' && !detectedCode">
                                    Proses Check-in / Check-out
                                </x-primary-button>
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

                {{-- ===== PANEL TAMU BELUM KELUAR ===== --}}
                <div class="bg-white rounded-lg shadow" x-data="{ checkoutTamuId: null, checkoutTamuNama: '' }">
                    <div class="p-5 border-b flex justify-between items-center">
                        <div>
                            <h3 class="text-base font-semibold text-gray-700">Tamu Belum Keluar</h3>
                            <p class="text-xs text-gray-400 mt-0.5">Tamu yang sudah check-in namun belum check-out</p>
                        </div>
                        @if($tamuBelumKeluar->count() > 0)
                        <span class="inline-flex items-center justify-center w-6 h-6 text-xs font-bold rounded-full
                            {{ $tamuBelumKeluar->where('durasi_jam', '>=', 12)->count() > 0 ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700' }}">
                            {{ $tamuBelumKeluar->count() }}
                        </span>
                        @endif
                    </div>

                    <div class="overflow-y-auto max-h-[420px]">
                        @if($tamuBelumKeluar->isEmpty())
                            <div class="p-8 text-center text-gray-400">
                                <svg class="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Semua tamu sudah keluar.
                            </div>
                        @else
                        <div class="divide-y divide-gray-100">
                            @foreach($tamuBelumKeluar as $t)
                            @php $lewat = $t->durasi_jam >= 12; @endphp
                            <div class="px-4 py-3 flex items-center gap-3 {{ $lewat ? 'bg-red-50' : '' }}">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <p class="font-medium text-sm text-gray-800 truncate">{{ $t->nama }}</p>
                                        @if($lewat)
                                        <span class="shrink-0 inline-block px-1.5 py-0.5 rounded text-xs font-bold bg-red-100 text-red-700">
                                            ⚠ {{ $t->durasi_jam }}j
                                        </span>
                                        @else
                                        <span class="shrink-0 inline-block px-1.5 py-0.5 rounded text-xs bg-yellow-100 text-yellow-700">
                                            {{ $t->durasi_jam }}j
                                        </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-400 truncate mt-0.5">
                                        Masuk: {{ $t->waktu_masuk_dt?->format('H:i') ?? '-' }}
                                        @if($t->pejabat) · Tujuan: {{ $t->pejabat->name }} @endif
                                    </p>
                                </div>
                                <button type="button"
                                    @click="checkoutTamuId = {{ $t->id }}; checkoutTamuNama = {{ \Illuminate\Support\Js::from($t->nama) }}"
                                    class="shrink-0 text-xs px-3 py-1.5 rounded-lg font-medium
                                        {{ $lewat ? 'bg-red-600 hover:bg-red-700 text-white' : 'bg-gray-100 hover:bg-gray-200 text-gray-700' }}
                                        transition">
                                    Checkout
                                </button>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>

                    {{-- Modal Konfirmasi Manual Checkout --}}
                    <div x-show="checkoutTamuId !== null"
                         x-transition.opacity
                         class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
                         @click.self="checkoutTamuId = null">
                        <div class="bg-white rounded-xl shadow-xl max-w-sm w-full p-6" @click.stop>
                            <div class="text-center mb-4">
                                <div class="w-12 h-12 rounded-full bg-orange-100 flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-6 h-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-bold text-gray-800">Konfirmasi Checkout Manual</h3>
                                <p class="text-sm text-gray-500 mt-1">
                                    Checkout tamu <strong x-text="checkoutTamuNama"></strong> tanpa scan QR?
                                </p>
                                <p class="text-xs text-orange-600 mt-2">Tindakan ini akan dicatat sebagai checkout manual oleh petugas.</p>
                            </div>
                            <div class="flex gap-3">
                                <button type="button" @click="checkoutTamuId = null"
                                    class="flex-1 px-4 py-2 rounded-lg border border-gray-300 text-gray-700 text-sm font-medium hover:bg-gray-50 transition">
                                    Batal
                                </button>
                                <form method="POST" class="flex-1"
                                      :action="`{{ url('scan/checkout') }}/` + checkoutTamuId">
                                    @csrf
                                    <button type="submit"
                                        class="w-full px-4 py-2 rounded-lg bg-orange-600 hover:bg-orange-700 text-white text-sm font-medium transition">
                                        Ya, Checkout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== LOG KUNJUNGAN HARI INI ===== --}}
            <div class="bg-white rounded-lg shadow">
                <div class="p-5 border-b flex justify-between items-center">
                    <h3 class="text-base font-semibold text-gray-700">Log Kunjungan Hari Ini</h3>
                    <span class="text-sm text-gray-400">{{ now()->format('d M Y') }}</span>
                </div>
                <div class="overflow-x-auto">
                    @if($kunjunganTerbaru->isEmpty())
                        <div class="p-8 text-center text-gray-400">Belum ada kunjungan hari ini.</div>
                    @else
                    <div class="divide-y divide-gray-100">
                        @foreach($kunjunganTerbaru as $k)
                        <div class="flex items-center gap-3 px-5 py-3">
                            <span class="px-2 py-1 rounded text-xs font-semibold min-w-[70px] text-center {{ $k->jenis === 'masuk' ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800' }}">
                                {{ $k->jenis === 'masuk' ? 'CHECK IN' : 'CHECK OUT' }}
                            </span>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-sm text-gray-800 truncate">{{ $k->tamu->nama }}</p>
                                <p class="text-xs text-gray-400 truncate">{{ $k->tamu->tujuan_kunjungan }}</p>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-sm text-gray-600">{{ $k->waktu_scan->format('H:i') }}</p>
                                @if($k->catatan)
                                <p class="text-xs text-orange-500">{{ $k->catatan }}</p>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
    const QR_PATTERN = /^[A-Z0-9]{8}$/;

    function scanPage() {
        return {
            mode: 'manual',
            detectedCode: '',
            scanner: null,
            inputError: '',
            cameraError: '',
            manualLen: 0,

            init() {
                this.$nextTick(() => {
                    const inp = this.$refs.tokenInput;
                    if (inp && inp.value) {
                        this.manualLen = inp.value.length;
                    }
                });
            },

            switchMode(newMode) {
                if (this.mode === newMode) return;
                if (this.mode === 'kamera') this.stopCamera();
                this.mode = newMode;
                this.detectedCode = '';
                this.inputError = '';
                this.cameraError = '';
                if (newMode === 'kamera') {
                    this.$nextTick(() => {
                        if (this.$refs.tokenInput) this.$refs.tokenInput.value = '';
                        this.manualLen = 0;
                        this.startCamera();
                    });
                } else {
                    this.$nextTick(() => {
                        const inp = this.$refs.tokenInput;
                        if (inp) { inp.value = ''; inp.focus(); }
                        this.manualLen = 0;
                    });
                }
            },

            // Real-time validasi input manual
            onManualInput(e) {
                const raw = e.target.value.toUpperCase();
                // Strip karakter non-alfanumerik
                const cleaned = raw.replace(/[^A-Z0-9]/g, '');
                if (cleaned !== raw) {
                    e.target.value = cleaned;
                    this.inputError = 'Hanya huruf A–Z dan angka 0–9 yang diperbolehkan.';
                } else {
                    this.inputError = '';
                }
                this.manualLen = cleaned.length;
            },

            // Validasi saat form disubmit
            submitForm() {
                if (this.mode === 'manual') {
                    const val = (this.$refs.tokenInput?.value || '').toUpperCase().replace(/[^A-Z0-9]/g, '');
                    if (this.$refs.tokenInput) this.$refs.tokenInput.value = val;
                    if (!QR_PATTERN.test(val)) {
                        this.inputError = val.length === 0
                            ? 'Masukkan kode QR terlebih dahulu.'
                            : `Kode harus 8 karakter alfanumerik (saat ini ${val.length} karakter).`;
                        return;
                    }
                    this.inputError = '';
                }
                document.getElementById('scanForm').submit();
            },

            startCamera() {
                this.cameraError = '';
                this.scanner = new Html5Qrcode('qr-reader');
                this.scanner.start(
                    { facingMode: 'environment' },
                    { fps: 10, qrbox: { width: 220, height: 220 } },
                    (decodedText) => {
                        const code = decodedText.trim().toUpperCase();
                        if (this.detectedCode === code) return;

                        if (!QR_PATTERN.test(code)) {
                            // QR bukan dari sistem ini — tampilkan error, kamera tetap jalan
                            this.cameraError = 'QR Code ini bukan dari sistem. Pastikan QR dicetak dari menu Detail Tamu.';
                            return;
                        }

                        this.cameraError = '';
                        this.detectedCode = code;
                        if (this.$refs.tokenInput) this.$refs.tokenInput.value = code;
                        this.stopCamera();
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

            resetCamera() {
                this.detectedCode = '';
                this.cameraError = '';
                if (this.$refs.tokenInput) this.$refs.tokenInput.value = '';
                this.$nextTick(() => this.startCamera());
            },
        };
    }
    </script>
</x-app-layout>
