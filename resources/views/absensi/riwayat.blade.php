<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Riwayat Absensi Saya</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @include('partials.alert')

            {{-- Filter --}}
            <div class="bg-white rounded-lg shadow p-5 mb-6">
                <form method="GET" action="{{ route('absensi.riwayat') }}" class="flex flex-wrap gap-3 items-end">
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
                    <a href="{{ route('absensi.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200">
                        Absensi Hari Ini
                    </a>
                </form>
            </div>

            {{-- Tabel --}}
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="p-4 border-b flex justify-between items-center">
                    <h3 class="font-semibold text-gray-700">
                        Riwayat — {{ \Carbon\Carbon::create($tahun, $bulan)->translatedFormat('F Y') }}
                    </h3>
                    <span class="text-sm text-gray-500">{{ $absensi->total() }} entri</span>
                </div>

                @if($absensi->isEmpty())
                <div class="p-8 text-center text-gray-400">Belum ada data absensi untuk periode ini.</div>
                @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Tanggal</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Masuk</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Keluar</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Durasi</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Foto</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach($absensi as $a)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium">{{ $a->tanggal->translatedFormat('d M Y') }}</td>
                                <td class="px-4 py-3">{{ $a->waktu_masuk?->format('H:i') ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $a->waktu_keluar?->format('H:i') ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $a->durasiFormatted() }}</td>
                                <td class="px-4 py-3">
                                    @if($a->status === 'hadir')
                                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">Hadir</span>
                                    @elseif($a->status === 'belum_keluar')
                                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">Sedang Bertugas</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">Tidak Hadir</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if($a->foto_masuk)
                                        <a href="{{ url('/foto/' . $a->foto_masuk) }}" target="_blank"
                                           class="text-blue-500 hover:underline text-xs">Masuk</a>
                                    @endif
                                    @if($a->foto_keluar)
                                        <span class="text-gray-300 mx-1">|</span>
                                        <a href="{{ url('/foto/' . $a->foto_keluar) }}" target="_blank"
                                           class="text-blue-500 hover:underline text-xs">Keluar</a>
                                    @endif
                                    @if(!$a->foto_masuk && !$a->foto_keluar)
                                        <span class="text-gray-400">-</span>
                                    @endif
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
