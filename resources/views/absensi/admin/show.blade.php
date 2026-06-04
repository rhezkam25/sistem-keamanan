<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('absensi.admin.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800">Rekap Absensi — {{ $user->name }}</h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

            {{-- Info Satpam --}}
            <div class="bg-white rounded-lg shadow p-5 mb-6">
                <div class="flex flex-wrap gap-6">
                    <div>
                        <p class="text-xs text-gray-500">Nama</p>
                        <p class="font-semibold text-gray-800">{{ $user->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">NIP</p>
                        <p class="font-semibold text-gray-800">{{ $user->nip ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Jabatan</p>
                        <p class="font-semibold text-gray-800">{{ $user->jabatan ?? '-' }}</p>
                    </div>
                </div>
            </div>

            {{-- Filter Bulan --}}
            <div class="bg-white rounded-lg shadow p-5 mb-6">
                <form method="GET" class="flex flex-wrap gap-3 items-end">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Bulan</label>
                        <select name="bulan" class="rounded-lg border-gray-300 text-sm shadow-sm">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" @selected($m == $bulan)>
                                    {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tahun</label>
                        <select name="tahun" class="rounded-lg border-gray-300 text-sm shadow-sm">
                            @for($y = now()->year; $y >= now()->year - 2; $y--)
                                <option value="{{ $y }}" @selected($y == $tahun)>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700">
                        Tampilkan
                    </button>
                </form>
            </div>

            {{-- Ringkasan Bulan --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow p-4">
                    <p class="text-xs text-gray-500 mb-1">Total Hadir</p>
                    <p class="text-2xl font-bold text-green-600">{{ $totalHadir }} hari</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <p class="text-xs text-gray-500 mb-1">Rata-rata Kerja</p>
                    <p class="text-2xl font-bold text-blue-600">
                        @if($rataRataMenit)
                            {{ intdiv((int) $rataRataMenit, 60) }}j {{ (int) $rataRataMenit % 60 }}m
                        @else
                            -
                        @endif
                    </p>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <p class="text-xs text-gray-500 mb-1">Periode</p>
                    <p class="text-lg font-bold text-gray-700">
                        {{ \Carbon\Carbon::create($tahun, $bulan)->translatedFormat('F Y') }}
                    </p>
                </div>
            </div>

            {{-- Tabel Detail --}}
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="p-4 border-b">
                    <h3 class="font-semibold text-gray-700">Detail Absensi</h3>
                </div>

                @if($absensi->isEmpty())
                <div class="p-8 text-center text-gray-400">Tidak ada data absensi untuk periode ini.</div>
                @else
                <div class="overflow-x-auto">
                    <table class="text-sm">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Tanggal</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Masuk</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Keluar</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Durasi</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">GPS Masuk</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Foto</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach($absensi as $a)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium whitespace-nowrap">{{ $a->tanggal->translatedFormat('d M Y') }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $a->waktu_masuk?->format('H:i') ?? '-' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $a->waktu_keluar?->format('H:i') ?? '-' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $a->durasiFormatted() }}</td>
                                <td class="px-4 py-3 text-xs">
                                    @if($a->latitude_masuk)
                                        <span class="text-gray-600">
                                            {{ number_format($a->latitude_masuk, 5) }},
                                            {{ number_format($a->longitude_masuk, 5) }}
                                        </span>
                                        <br>
                                        <span class="{{ $a->akurasi_masuk <= 50 ? 'text-green-600' : ($a->akurasi_masuk <= 150 ? 'text-yellow-600' : 'text-red-600') }}">
                                            ±{{ round($a->akurasi_masuk) }}m
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if($a->status === 'hadir')
                                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">Hadir</span>
                                    @elseif($a->status === 'belum_keluar')
                                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">Bertugas</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">Tidak Hadir</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if($a->foto_masuk)
                                        <a href="{{ url('/foto/' . $a->foto_masuk) }}" target="_blank"
                                           class="text-xs text-blue-500 hover:underline">Masuk</a>
                                    @endif
                                    @if($a->foto_keluar)
                                        @if($a->foto_masuk) <span class="text-gray-300"> | </span> @endif
                                        <a href="{{ url('/foto/' . $a->foto_keluar) }}" target="_blank"
                                           class="text-xs text-blue-500 hover:underline">Keluar</a>
                                    @endif
                                    @if(!$a->foto_masuk && !$a->foto_keluar) - @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t">
                    {{ $absensi->withQueryString()->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
