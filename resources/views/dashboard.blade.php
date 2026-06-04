<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard &mdash; Selamat datang, {{ Auth::user()->name }}
            <span class="ml-2 text-sm font-normal text-gray-500">({{ ucfirst(Auth::user()->role) }})</span>
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Stats Cards --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @if(Auth::user()->isAdmin())
                    @include('partials.stat-card', ['label' => 'Total Tamu', 'value' => $stats['total_tamu'], 'color' => 'blue'])
                    @include('partials.stat-card', ['label' => 'Menunggu Approval', 'value' => $stats['menunggu'], 'color' => 'yellow'])
                    @include('partials.stat-card', ['label' => 'Disetujui', 'value' => $stats['disetujui'], 'color' => 'green'])
                    @include('partials.stat-card', ['label' => 'Kunjungan Hari Ini', 'value' => $stats['kunjungan_hari_ini'], 'color' => 'purple'])
                @elseif(Auth::user()->isPejabat())
                    @include('partials.stat-card', ['label' => 'Total Tamu', 'value' => $stats['total_tamu'], 'color' => 'blue'])
                    @include('partials.stat-card', ['label' => 'Menunggu Approval', 'value' => $stats['menunggu'], 'color' => 'yellow'])
                    @include('partials.stat-card', ['label' => 'Disetujui', 'value' => $stats['disetujui'], 'color' => 'green'])
                    @include('partials.stat-card', ['label' => 'Total Staff', 'value' => $stats['total_staff'], 'color' => 'purple'])
                @elseif(Auth::user()->isStaff())
                    @include('partials.stat-card', ['label' => 'Total Tamu Saya', 'value' => $stats['total_tamu'], 'color' => 'blue'])
                    @include('partials.stat-card', ['label' => 'Menunggu', 'value' => $stats['menunggu'], 'color' => 'yellow'])
                    @include('partials.stat-card', ['label' => 'Disetujui', 'value' => $stats['disetujui'], 'color' => 'green'])
                    @include('partials.stat-card', ['label' => 'Ditolak', 'value' => $stats['ditolak'], 'color' => 'red'])
                @else
                    @include('partials.stat-card', ['label' => 'Kunjungan Hari Ini', 'value' => $stats['kunjungan_hari_ini'], 'color' => 'blue'])
                    @include('partials.stat-card', ['label' => 'Tamu Masuk', 'value' => $stats['tamu_masuk'], 'color' => 'green'])
                    @include('partials.stat-card', ['label' => 'Tamu Keluar', 'value' => $stats['tamu_keluar'], 'color' => 'purple'])
                    @include('partials.stat-card', ['label' => 'Tamu Disetujui', 'value' => $stats['tamu_disetujui'], 'color' => 'yellow'])
                @endif
            </div>

            {{-- Quick Actions --}}
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-base font-semibold text-gray-700 mb-4">Aksi Cepat</h3>
                <div class="flex flex-wrap gap-3">
                    @if(Auth::user()->canInputTamu())
                    <a href="{{ route('tamu.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                        + Daftarkan Tamu
                    </a>
                    @endif
                    @if(Auth::user()->canApprove() && isset($stats['menunggu']) && $stats['menunggu'] > 0)
                    <a href="{{ route('approval.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 text-sm font-medium">
                        Proses Approval ({{ $stats['menunggu'] }})
                    </a>
                    @endif
                    @if(Auth::user()->canScanQr())
                    <a href="{{ route('scan.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium">
                        Scan QR Tamu
                    </a>
                    @endif
                    @if(Auth::user()->isSatpam())
                    <a href="{{ route('absensi.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 text-sm font-medium">
                        Absensi Saya
                    </a>
                    @endif
                    @if(Auth::user()->canViewAbsensi())
                    <a href="{{ route('absensi.admin.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-teal-100 text-teal-700 rounded-lg hover:bg-teal-200 text-sm font-medium">
                        Data Absensi Security
                    </a>
                    @endif
                    @if(Auth::user()->canInputTamu())
                    <a href="{{ route('tamu.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm font-medium">
                        Lihat Semua Tamu
                    </a>
                    @endif
                </div>
            </div>

            {{-- Status Absensi Hari Ini (khusus satpam) --}}
            @if(Auth::user()->isSatpam())
            <div class="bg-white rounded-lg shadow p-5">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-base font-semibold text-gray-700 mb-1">Absensi Hari Ini</h3>
                        <p class="text-sm text-gray-500">{{ now()->translatedFormat('l, d F Y') }}</p>
                    </div>
                    <a href="{{ route('absensi.index') }}" class="text-sm text-teal-600 hover:underline">Buka Absensi →</a>
                </div>
                @php $absensiHariIni = auth()->user()->absensiHariIni()->first(); @endphp
                <div class="mt-4 flex items-center gap-3">
                    @if(!$absensiHariIni)
                        <span class="w-3 h-3 rounded-full bg-gray-400 shrink-0"></span>
                        <span class="text-gray-600">Belum absen masuk</span>
                    @elseif($absensiHariIni->waktu_keluar)
                        <span class="w-3 h-3 rounded-full bg-green-500 shrink-0"></span>
                        <span class="text-green-700 font-medium">Selesai Bertugas</span>
                        <span class="text-sm text-gray-500 ml-2">
                            {{ $absensiHariIni->waktu_masuk->format('H:i') }} –
                            {{ $absensiHariIni->waktu_keluar->format('H:i') }}
                            ({{ $absensiHariIni->durasiFormatted() }})
                        </span>
                    @else
                        <span class="w-3 h-3 rounded-full bg-blue-500 animate-pulse shrink-0"></span>
                        <span class="text-blue-700 font-medium">Sedang Bertugas</span>
                        <span class="text-sm text-gray-500 ml-2">
                            Masuk: {{ $absensiHariIni->waktu_masuk->format('H:i') }}
                        </span>
                    @endif
                </div>
            </div>
            @endif

            {{-- Kunjungan Terbaru --}}
            <div class="bg-white rounded-lg shadow">
                <div class="p-5 border-b flex justify-between items-center">
                    <h3 class="text-base font-semibold text-gray-700">Kunjungan Hari Ini</h3>
                    <span class="text-sm text-gray-400">{{ now()->format('d M Y') }}</span>
                </div>
                <div class="overflow-x-auto">
                    @if($kunjunganHariIni->isEmpty())
                        <div class="p-10 text-center text-gray-400">Belum ada kunjungan hari ini.</div>
                    @else
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-gray-600 font-medium">Nama Tamu</th>
                                <th class="px-4 py-3 text-left text-gray-600 font-medium">Tujuan</th>
                                <th class="px-4 py-3 text-left text-gray-600 font-medium">Status</th>
                                <th class="px-4 py-3 text-left text-gray-600 font-medium">Waktu</th>
                                <th class="px-4 py-3 text-left text-gray-600 font-medium">Petugas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($kunjunganHariIni as $k)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $k->tamu->nama }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ Str::limit($k->tamu->tujuan_kunjungan, 40) }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $k->jenis === 'masuk' ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800' }}">
                                        {{ $k->jenis === 'masuk' ? 'CHECK IN' : 'CHECK OUT' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-500">{{ $k->waktu_scan->format('H:i') }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $k->petugas?->name ?? 'N/A' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
