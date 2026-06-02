<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Absensi</h2>
    </x-slot>

    {{-- Leaflet CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            @include('partials.alert')

            @if($errors->any())
            <div class="mb-4 bg-red-50 border border-red-300 rounded-lg p-4">
                <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if(!$pengaturan->sudahDikonfigurasi())
            <div class="mb-4 bg-yellow-50 border border-yellow-300 rounded-lg p-4 flex items-start gap-3">
                <svg class="w-5 h-5 text-yellow-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
                <p class="text-sm text-yellow-800">Titik lokasi kantor belum dikonfigurasi. Silakan hubungi admin.</p>
            </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6"
                 x-data="absensiPage({
                     kantorLat: {{ $pengaturan->kantor_lat ?? 'null' }},
                     kantorLng: {{ $pengaturan->kantor_lng ?? 'null' }},
                     radiusMeter: {{ $pengaturan->radius_meter }},
                     jamKerjaMin: {{ $pengaturan->jam_kerja_minimum }},
                     sudahMasuk: {{ $absensiHariIni && $absensiHariIni->waktu_masuk ? 'true' : 'false' }},
                     sudahKeluar: {{ $absensiHariIni && $absensiHariIni->waktu_keluar ? 'true' : 'false' }},
                     waktuMasuk: '{{ $absensiHariIni?->waktu_masuk?->toISOString() ?? '' }}',
                 })">

                {{-- Panel Kiri: Absensi Form --}}
                <div class="space-y-4">

                    {{-- Status Card --}}
                    <div class="bg-white rounded-lg shadow p-5">
                        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Status Hari Ini</h3>

                        @if(!$absensiHariIni)
                            <div class="flex items-center gap-3">
                                <span class="w-3 h-3 rounded-full bg-gray-400"></span>
                                <span class="text-gray-700 font-medium">Belum Absen</span>
                            </div>
                        @elseif($absensiHariIni->waktu_keluar)
                            <div class="flex items-center gap-3 mb-2">
                                <span class="w-3 h-3 rounded-full bg-green-500"></span>
                                <span class="text-green-700 font-semibold">Selesai Bertugas</span>
                            </div>
                            <div class="text-sm text-gray-600 space-y-1">
                                <p>Masuk: <strong>{{ $absensiHariIni->waktu_masuk->format('H:i') }}</strong></p>
                                <p>Keluar: <strong>{{ $absensiHariIni->waktu_keluar->format('H:i') }}</strong></p>
                                <p>Durasi: <strong>{{ $absensiHariIni->durasiFormatted() }}</strong></p>
                            </div>
                        @else
                            <div class="flex items-center gap-3 mb-2">
                                <span class="w-3 h-3 rounded-full bg-blue-500 animate-pulse"></span>
                                <span class="text-blue-700 font-semibold">Sedang Bertugas</span>
                            </div>
                            <div class="text-sm text-gray-600 space-y-1">
                                <p>Masuk: <strong>{{ $absensiHariIni->waktu_masuk->format('H:i') }}</strong></p>
                                <p>Sisa waktu: <strong x-text="sisaWaktuTeks"></strong></p>
                            </div>
                        @endif
                    </div>

                    {{-- GPS Status --}}
                    <div class="bg-white rounded-lg shadow p-5">
                        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Status Lokasi</h3>

                        {{-- Peringatan Fake GPS --}}
                        <div x-show="fakeGpsDetected" x-cloak
                             class="mb-3 bg-red-50 border border-red-400 rounded-lg p-3 flex items-start gap-2">
                            <svg class="w-5 h-5 text-red-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                            </svg>
                            <div>
                                <p class="font-semibold text-red-800 text-sm">Fake GPS Terdeteksi!</p>
                                <p class="text-xs text-red-700 mt-0.5">Nonaktifkan VPN atau aplikasi fake GPS, lalu muat ulang halaman ini.</p>
                            </div>
                        </div>

                        <div x-show="!gpsLoading" x-cloak class="space-y-2 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500">Akurasi GPS</span>
                                <span :class="akurasi <= 50 ? 'text-green-600' : akurasi <= 150 ? 'text-yellow-600' : 'text-red-600'"
                                      x-text="akurasi ? Math.round(akurasi) + ' meter' : '-'"></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500">Jarak dari Kantor</span>
                                <span :class="dalamRadius ? 'text-green-600 font-semibold' : 'text-red-600 font-semibold'"
                                      x-text="jarak !== null ? Math.round(jarak) + ' meter' : '-'"></span>
                            </div>
                            <div class="flex items-center gap-2 mt-2">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold"
                                      :class="dalamRadius ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                    <span class="w-2 h-2 rounded-full"
                                          :class="dalamRadius ? 'bg-green-500' : 'bg-red-500'"></span>
                                    <span x-text="dalamRadius ? 'Dalam Jangkauan ✓' : 'Di Luar Jangkauan ✗'"></span>
                                </span>
                            </div>
                        </div>

                        <div x-show="gpsLoading" class="flex items-center gap-2 text-gray-500 text-sm">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            Mengambil lokasi...
                        </div>
                    </div>

                    {{-- Foto Selfie --}}
                    <div class="bg-white rounded-lg shadow p-5">
                        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Foto Selfie (Opsional)</h3>
                        <div class="space-y-3">
                            <video x-ref="video" class="w-full rounded-lg bg-gray-100" autoplay playsinline
                                   x-show="kameraAktif && !fotoDiambil" style="max-height:200px;object-fit:cover;"></video>
                            <canvas x-ref="canvas" class="hidden w-full rounded-lg"></canvas>
                            <img x-ref="preview" src="" alt="Selfie preview"
                                 class="w-full rounded-lg" x-show="fotoDiambil" style="max-height:200px;object-fit:cover;">

                            <div class="flex gap-2">
                                <button type="button" @click="bukaKamera()"
                                        x-show="!kameraAktif && !fotoDiambil"
                                        class="flex-1 px-3 py-2 text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition">
                                    Buka Kamera
                                </button>
                                <button type="button" @click="ambilFoto()"
                                        x-show="kameraAktif && !fotoDiambil"
                                        class="flex-1 px-3 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                                    Ambil Foto
                                </button>
                                <button type="button" @click="hapusFoto()"
                                        x-show="fotoDiambil"
                                        class="flex-1 px-3 py-2 text-sm bg-red-100 hover:bg-red-200 text-red-700 rounded-lg transition">
                                    Hapus & Ulangi
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Tombol Absensi --}}
                    @if(!$absensiHariIni)
                    <form action="{{ route('absensi.masuk') }}" method="POST" @submit.prevent="submitAbsen($event)">
                        @csrf
                        <input type="hidden" name="latitude"  x-model="lat">
                        <input type="hidden" name="longitude" x-model="lng">
                        <input type="hidden" name="akurasi"   x-model="akurasi">
                        <input type="hidden" name="foto"      x-model="fotoBase64">
                        <button type="submit"
                                :disabled="!dalamRadius || fakeGpsDetected || gpsLoading || akurasi > 150 || !{{ $pengaturan->sudahDikonfigurasi() ? 'true' : 'false' }}"
                                class="w-full py-3 px-4 rounded-lg font-semibold text-white transition
                                       bg-green-600 hover:bg-green-700
                                       disabled:bg-gray-300 disabled:cursor-not-allowed">
                            <span x-show="!gpsLoading">Absen Masuk</span>
                            <span x-show="gpsLoading">Mengambil Lokasi...</span>
                        </button>
                    </form>

                    @elseif(!$absensiHariIni->waktu_keluar)
                    <form action="{{ route('absensi.keluar') }}" method="POST" @submit.prevent="submitAbsen($event)">
                        @csrf
                        <input type="hidden" name="latitude"  x-model="lat">
                        <input type="hidden" name="longitude" x-model="lng">
                        <input type="hidden" name="akurasi"   x-model="akurasi">
                        <input type="hidden" name="foto"      x-model="fotoBase64">
                        <button type="submit"
                                :disabled="!dalamRadius || fakeGpsDetected || gpsLoading || akurasi > 150 || !cukupKerja || !{{ $pengaturan->sudahDikonfigurasi() ? 'true' : 'false' }}"
                                class="w-full py-3 px-4 rounded-lg font-semibold text-white transition
                                       bg-orange-600 hover:bg-orange-700
                                       disabled:bg-gray-300 disabled:cursor-not-allowed">
                            <span x-show="!gpsLoading && cukupKerja">Absen Keluar</span>
                            <span x-show="!gpsLoading && !cukupKerja" x-text="'Belum Waktunya (' + sisaWaktuTeks + ')'"></span>
                            <span x-show="gpsLoading">Mengambil Lokasi...</span>
                        </button>
                    </form>

                    @else
                    <div class="w-full py-3 px-4 rounded-lg text-center bg-green-50 border border-green-200 text-green-700 font-semibold">
                        Absensi hari ini telah selesai
                    </div>
                    @endif

                    <a href="{{ route('absensi.riwayat') }}"
                       class="block text-center text-sm text-blue-600 hover:underline mt-1">
                        Lihat Riwayat Absensi
                    </a>
                </div>

                {{-- Panel Kanan: Peta --}}
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="p-4 border-b">
                        <h3 class="font-semibold text-gray-700">Peta Lokasi</h3>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Absensi hanya dapat dilakukan dalam radius
                            <strong>{{ $pengaturan->radius_meter }} meter</strong> dari kantor
                        </p>
                    </div>
                    <div id="peta" style="height: 420px; width: 100%;"></div>
                </div>

            </div>
        </div>
    </div>

    {{-- Leaflet JS --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
    function absensiPage(config) {
        return {
            kantorLat:    config.kantorLat,
            kantorLng:    config.kantorLng,
            radiusMeter:  config.radiusMeter,
            jamKerjaMin:  config.jamKerjaMin,
            sudahMasuk:   config.sudahMasuk,
            sudahKeluar:  config.sudahKeluar,
            waktuMasuk:   config.waktuMasuk ? new Date(config.waktuMasuk) : null,

            // GPS state
            lat: null, lng: null, akurasi: null,
            jarak: null, dalamRadius: false,
            gpsLoading: true, fakeGpsDetected: false,
            posisiSamples: [],

            // Kerja
            cukupKerja: false,
            sisaWaktuTeks: '-',

            // Foto
            kameraAktif: false, fotoDiambil: false,
            fotoBase64: '', stream: null,

            // Leaflet map
            peta: null, markerUser: null,

            init() {
                this.initPeta();
                this.mulaiPollGps();
                if (this.sudahMasuk && !this.sudahKeluar && this.waktuMasuk) {
                    this.updateSisaWaktu();
                    setInterval(() => this.updateSisaWaktu(), 30000);
                }
            },

            initPeta() {
                const defaultLat = this.kantorLat ?? 5.4229;
                const defaultLng = this.kantorLng ?? 100.3241;

                this.peta = L.map('peta').setView([defaultLat, defaultLng], 17);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                }).addTo(this.peta);

                if (this.kantorLat && this.kantorLng) {
                    L.marker([this.kantorLat, this.kantorLng], {
                        icon: L.divIcon({
                            className: '',
                            html: '<div style="background:#2563eb;color:#fff;width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:18px;box-shadow:0 2px 6px rgba(0,0,0,0.3)">🏢</div>',
                            iconSize: [36, 36], iconAnchor: [18, 18]
                        })
                    }).addTo(this.peta).bindPopup('<b>{{ $pengaturan->kantor_nama }}</b><br>Radius: {{ $pengaturan->radius_meter }}m');

                    L.circle([this.kantorLat, this.kantorLng], {
                        radius: this.radiusMeter,
                        color: '#2563eb', fillColor: '#bfdbfe', fillOpacity: 0.3, weight: 2
                    }).addTo(this.peta);
                }
            },

            async mulaiPollGps() {
                this.gpsLoading = true;
                if (!navigator.geolocation) {
                    this.gpsLoading = false;
                    return;
                }

                const ambilSatu = () => new Promise((resolve, reject) => {
                    navigator.geolocation.getCurrentPosition(resolve, reject, {
                        enableHighAccuracy: true, timeout: 10000, maximumAge: 0
                    });
                });

                try {
                    // Ambil 3 sampel untuk deteksi fake GPS
                    for (let i = 0; i < 3; i++) {
                        const pos = await ambilSatu();
                        this.posisiSamples.push({
                            lat: pos.coords.latitude,
                            lng: pos.coords.longitude,
                            akurasi: pos.coords.accuracy,
                            ts: pos.timestamp
                        });
                        if (i < 2) await new Promise(r => setTimeout(r, 1500));
                    }

                    this.deteksiFakeGps();
                    const s = this.posisiSamples[1];
                    this.lat = s.lat;
                    this.lng = s.lng;
                    this.akurasi = s.akurasi;
                    this.gpsLoading = false;

                    this.updatePosisiPeta(s.lat, s.lng);
                    if (this.kantorLat) this.hitungJarak(s.lat, s.lng);

                    // Update posisi setiap 20 detik
                    setInterval(async () => {
                        try {
                            const pos = await ambilSatu();
                            this.lat = pos.coords.latitude;
                            this.lng = pos.coords.longitude;
                            this.akurasi = pos.coords.accuracy;
                            this.updatePosisiPeta(this.lat, this.lng);
                            if (this.kantorLat) this.hitungJarak(this.lat, this.lng);
                        } catch (e) {}
                    }, 20000);

                } catch (err) {
                    this.gpsLoading = false;
                }
            },

            deteksiFakeGps() {
                if (this.posisiSamples.length < 3) return;

                // Hanya cek kecepatan perpindahan tidak masuk akal (teleportasi)
                // Ini satu-satunya indikator yang andal di semua perangkat termasuk PC
                const s1 = this.posisiSamples[0], s2 = this.posisiSamples[2];
                const jarak = this.haversine(s1.lat, s1.lng, s2.lat, s2.lng);
                const waktu = (s2.ts - s1.ts) / 1000; // detik
                // >300 m/s artinya berpindah > 1000 km dalam beberapa detik — jelas tidak mungkin
                if (waktu > 0 && (jarak / waktu) > 300) {
                    this.fakeGpsDetected = true;
                }
            },

            haversine(lat1, lng1, lat2, lng2) {
                const R = 6371000;
                const dLat = (lat2 - lat1) * Math.PI / 180;
                const dLng = (lng2 - lng1) * Math.PI / 180;
                const a = Math.sin(dLat/2)**2 + Math.cos(lat1*Math.PI/180) * Math.cos(lat2*Math.PI/180) * Math.sin(dLng/2)**2;
                return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            },

            hitungJarak(lat, lng) {
                this.jarak = this.haversine(this.kantorLat, this.kantorLng, lat, lng);
                this.dalamRadius = this.jarak <= this.radiusMeter;
            },

            updatePosisiPeta(lat, lng) {
                if (!this.peta) return;
                const icon = L.divIcon({
                    className: '',
                    html: `<div style="background:${this.dalamRadius ? '#16a34a' : '#dc2626'};color:#fff;width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:16px;box-shadow:0 2px 6px rgba(0,0,0,0.3)">📍</div>`,
                    iconSize: [32, 32], iconAnchor: [16, 16]
                });
                if (this.markerUser) {
                    this.markerUser.setLatLng([lat, lng]).setIcon(icon);
                } else {
                    this.markerUser = L.marker([lat, lng], { icon }).addTo(this.peta).bindPopup('Lokasi Anda');
                }
                this.peta.setView([lat, lng], this.peta.getZoom());
            },

            updateSisaWaktu() {
                if (!this.waktuMasuk) { this.cukupKerja = false; return; }
                const sudahMenit = Math.floor((Date.now() - this.waktuMasuk.getTime()) / 60000);
                const targetMenit = this.jamKerjaMin * 60;
                const sisaMenit = targetMenit - sudahMenit;
                this.cukupKerja = sisaMenit <= 0;
                if (sisaMenit <= 0) {
                    this.sisaWaktuTeks = 'Siap keluar';
                } else {
                    const j = Math.floor(sisaMenit / 60);
                    const m = sisaMenit % 60;
                    this.sisaWaktuTeks = `${j} jam ${m} menit`;
                }
            },

            async bukaKamera() {
                try {
                    this.stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } });
                    this.$refs.video.srcObject = this.stream;
                    this.kameraAktif = true;
                } catch (e) {
                    alert('Tidak dapat mengakses kamera.');
                }
            },

            ambilFoto() {
                const video = this.$refs.video;
                const canvas = this.$refs.canvas;
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                canvas.getContext('2d').drawImage(video, 0, 0);
                this.fotoBase64 = canvas.toDataURL('image/jpeg', 0.7);
                this.$refs.preview.src = this.fotoBase64;
                this.fotoDiambil = true;
                this.kameraAktif = false;
                if (this.stream) this.stream.getTracks().forEach(t => t.stop());
            },

            hapusFoto() {
                this.fotoBase64 = '';
                this.fotoDiambil = false;
                this.kameraAktif = false;
                if (this.stream) this.stream.getTracks().forEach(t => t.stop());
            },

            submitAbsen(event) {
                if (!this.lat || !this.lng) {
                    alert('Lokasi GPS belum tersedia. Harap tunggu.');
                    return;
                }
                if (this.fakeGpsDetected) {
                    alert('Terdeteksi fake GPS. Absensi tidak dapat dilakukan.');
                    return;
                }
                event.target.submit();
            },
        };
    }
    </script>
</x-app-layout>
