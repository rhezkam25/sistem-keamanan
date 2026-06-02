<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Pengaturan Sistem Absensi</h2>
    </x-slot>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            @include('partials.alert')

            <form method="POST" action="{{ route('pengaturan.update') }}" x-data="pengaturanPage()">
                @csrf
                @method('PATCH')

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                    {{-- Kiri: Form --}}
                    <div class="space-y-4">
                        <div class="bg-white rounded-lg shadow p-5">
                            <h3 class="font-semibold text-gray-700 mb-4">Informasi Kantor</h3>

                            <div class="space-y-4">
                                <div>
                                    <x-input-label for="kantor_nama" value="Nama Kantor" />
                                    <x-text-input id="kantor_nama" name="kantor_nama" type="text"
                                        class="mt-1 block w-full"
                                        value="{{ old('kantor_nama', $pengaturan->kantor_nama) }}"
                                        required />
                                    <x-input-error :messages="$errors->get('kantor_nama')" class="mt-1" />
                                </div>

                                <div>
                                    <x-input-label value="Koordinat Kantor" />
                                    <p class="text-xs text-gray-500 mb-2">Klik pada peta di sebelah kanan untuk menentukan titik lokasi kantor, atau isi manual di bawah.</p>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="text-xs text-gray-500">Latitude</label>
                                            <x-text-input name="kantor_lat" type="number" step="any"
                                                class="mt-0.5 block w-full text-sm"
                                                x-model="lat"
                                                placeholder="Contoh: 5.4229"
                                                required />
                                        </div>
                                        <div>
                                            <label class="text-xs text-gray-500">Longitude</label>
                                            <x-text-input name="kantor_lng" type="number" step="any"
                                                class="mt-0.5 block w-full text-sm"
                                                x-model="lng"
                                                placeholder="Contoh: 100.3241"
                                                required />
                                        </div>
                                    </div>
                                    <x-input-error :messages="$errors->get('kantor_lat')" class="mt-1" />
                                    <x-input-error :messages="$errors->get('kantor_lng')" class="mt-1" />
                                </div>

                                <div>
                                    <x-input-label for="radius_meter" value="Radius Absensi (meter)" />
                                    <div class="flex items-center gap-3 mt-1">
                                        <input type="range" name="radius_meter" id="radius_meter"
                                               min="50" max="2000" step="50"
                                               x-model="radius"
                                               @input="updateRadius()"
                                               class="flex-1">
                                        <span class="text-sm font-semibold text-blue-700 w-20 text-right"
                                              x-text="radius + ' meter'"></span>
                                    </div>
                                    <x-input-error :messages="$errors->get('radius_meter')" class="mt-1" />
                                </div>

                                <div>
                                    <x-input-label for="jam_kerja_minimum" value="Jam Kerja Minimum (jam)" />
                                    <x-text-input id="jam_kerja_minimum" name="jam_kerja_minimum" type="number"
                                        class="mt-1 block w-full"
                                        value="{{ old('jam_kerja_minimum', $pengaturan->jam_kerja_minimum) }}"
                                        min="1" max="24" required />
                                    <p class="text-xs text-gray-500 mt-1">Security harus bekerja minimal sejumlah jam ini sebelum bisa absen keluar.</p>
                                    <x-input-error :messages="$errors->get('jam_kerja_minimum')" class="mt-1" />
                                </div>
                            </div>
                        </div>

                        @if($pengaturan->sudahDikonfigurasi())
                        <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                            <p class="text-sm font-semibold text-blue-800 mb-1">Konfigurasi Saat Ini</p>
                            <p class="text-sm text-blue-700">
                                {{ $pengaturan->kantor_nama }} —
                                {{ $pengaturan->kantor_lat }}, {{ $pengaturan->kantor_lng }}
                            </p>
                            <p class="text-sm text-blue-700">
                                Radius: {{ $pengaturan->radius_meter }}m |
                                Min. kerja: {{ $pengaturan->jam_kerja_minimum }} jam
                            </p>
                        </div>
                        @endif

                        <div class="flex justify-end">
                            <x-primary-button>Simpan Pengaturan</x-primary-button>
                        </div>
                    </div>

                    {{-- Kanan: Peta --}}
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="p-4 border-b">
                            <h3 class="font-semibold text-gray-700">Pilih Titik Lokasi Kantor</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Klik di peta untuk menentukan titik kantor. Gunakan scroll untuk zoom.</p>
                        </div>
                        <div id="peta-pengaturan" style="height: 420px; width: 100%;"></div>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
    function pengaturanPage() {
        return {
            lat: '{{ old('kantor_lat', $pengaturan->kantor_lat ?? '') }}',
            lng: '{{ old('kantor_lng', $pengaturan->kantor_lng ?? '') }}',
            radius: {{ old('radius_meter', $pengaturan->radius_meter) }},
            peta: null,
            marker: null,
            circle: null,

            init() {
                const initLat = parseFloat(this.lat) || 5.4229;
                const initLng = parseFloat(this.lng) || 100.3241;

                this.peta = L.map('peta-pengaturan').setView([initLat, initLng], 15);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                }).addTo(this.peta);

                // Jika sudah ada koordinat tersimpan, pasang marker
                if (this.lat && this.lng) {
                    this.pasangMarker(parseFloat(this.lat), parseFloat(this.lng));
                }

                this.peta.on('click', (e) => {
                    this.lat = e.latlng.lat.toFixed(7);
                    this.lng = e.latlng.lng.toFixed(7);
                    this.pasangMarker(e.latlng.lat, e.latlng.lng);
                });
            },

            pasangMarker(lat, lng) {
                if (this.marker) this.peta.removeLayer(this.marker);
                if (this.circle) this.peta.removeLayer(this.circle);

                this.marker = L.marker([lat, lng], {
                    draggable: true,
                    icon: L.divIcon({
                        className: '',
                        html: '<div style="background:#2563eb;color:#fff;width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:18px;box-shadow:0 2px 6px rgba(0,0,0,0.3)">🏢</div>',
                        iconSize: [36, 36], iconAnchor: [18, 18]
                    })
                }).addTo(this.peta);

                this.marker.on('dragend', (e) => {
                    const pos = e.target.getLatLng();
                    this.lat = pos.lat.toFixed(7);
                    this.lng = pos.lng.toFixed(7);
                    this.updateRadius();
                });

                this.circle = L.circle([lat, lng], {
                    radius: parseInt(this.radius),
                    color: '#2563eb', fillColor: '#bfdbfe', fillOpacity: 0.3, weight: 2
                }).addTo(this.peta);
            },

            updateRadius() {
                if (!this.circle || !this.lat || !this.lng) return;
                this.peta.removeLayer(this.circle);
                this.circle = L.circle([parseFloat(this.lat), parseFloat(this.lng)], {
                    radius: parseInt(this.radius),
                    color: '#2563eb', fillColor: '#bfdbfe', fillOpacity: 0.3, weight: 2
                }).addTo(this.peta);
            },
        };
    }
    </script>
</x-app-layout>
