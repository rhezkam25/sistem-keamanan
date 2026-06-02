<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Data Absensi Security</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @include('partials.alert')

            {{-- Filter & Export --}}
            <div class="bg-white rounded-lg shadow p-5 mb-6">
                <form method="GET" action="{{ route('absensi.admin.index') }}" class="flex flex-wrap gap-3 items-end">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Security</label>
                        <select name="user_id" class="rounded-lg border-gray-300 text-sm shadow-sm">
                            <option value="">Semua Security</option>
                            @foreach($satpamList as $satpam)
                                <option value="{{ $satpam->id }}" @selected($userId == $satpam->id)>{{ $satpam->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Dari Tanggal</label>
                        <input type="date" name="dari" value="{{ $dari }}"
                               class="rounded-lg border-gray-300 text-sm shadow-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Sampai Tanggal</label>
                        <input type="date" name="sampai" value="{{ $sampai }}"
                               class="rounded-lg border-gray-300 text-sm shadow-sm">
                    </div>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700">
                        Filter
                    </button>
                    <a href="{{ route('absensi.admin.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200">
                        Reset
                    </a>
                </form>

                {{-- Export --}}
                <div class="mt-4 pt-4 border-t flex flex-wrap gap-2">
                    <span class="text-xs text-gray-500 self-center">Unduh data saat ini:</span>
                    <a href="{{ route('absensi.admin.export', array_merge(request()->only('dari','sampai','user_id'), ['format' => 'xlsx'])) }}"
                       class="flex items-center gap-1.5 px-3 py-1.5 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Export XLS
                    </a>
                    <a href="{{ route('absensi.admin.export', array_merge(request()->only('dari','sampai','user_id'), ['format' => 'csv'])) }}"
                       class="flex items-center gap-1.5 px-3 py-1.5 bg-gray-600 text-white text-sm rounded-lg hover:bg-gray-700">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Export CSV
                    </a>
                </div>
            </div>

            {{-- Tabel Data --}}
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="p-4 border-b flex justify-between items-center">
                    <h3 class="font-semibold text-gray-700">Daftar Absensi</h3>
                    <span class="text-sm text-gray-500">{{ $absensi->total() }} entri</span>
                </div>

                @if($absensi->isEmpty())
                <div class="p-8 text-center text-gray-400">Tidak ada data absensi untuk filter yang dipilih.</div>
                @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Security</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Tanggal</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Masuk</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Keluar</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Durasi</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Akurasi GPS</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach($absensi as $a)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-800">{{ $a->user->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $a->user->nip }}</p>
                                </td>
                                <td class="px-4 py-3">{{ $a->tanggal->translatedFormat('d M Y') }}</td>
                                <td class="px-4 py-3">
                                    <span class="font-medium">{{ $a->waktu_masuk?->format('H:i') ?? '-' }}</span>
                                    @if($a->foto_masuk)
                                        <a href="{{ Storage::url($a->foto_masuk) }}" target="_blank" class="ml-1 text-blue-400 hover:text-blue-600">
                                            <svg class="w-3.5 h-3.5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                        </a>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="font-medium">{{ $a->waktu_keluar?->format('H:i') ?? '-' }}</span>
                                    @if($a->foto_keluar)
                                        <a href="{{ Storage::url($a->foto_keluar) }}" target="_blank" class="ml-1 text-blue-400 hover:text-blue-600">
                                            <svg class="w-3.5 h-3.5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                        </a>
                                    @endif
                                </td>
                                <td class="px-4 py-3">{{ $a->durasiFormatted() }}</td>
                                <td class="px-4 py-3 text-xs">
                                    @if($a->akurasi_masuk)
                                        <span class="{{ $a->akurasi_masuk <= 50 ? 'text-green-600' : ($a->akurasi_masuk <= 150 ? 'text-yellow-600' : 'text-red-600') }}">
                                            {{ round($a->akurasi_masuk) }}m
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if($a->status === 'hadir')
                                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">Hadir</span>
                                    @elseif($a->status === 'belum_keluar')
                                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">Bertugas</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">Tidak Hadir</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('absensi.admin.show', $a->user_id) }}"
                                       class="text-blue-600 hover:underline text-xs">Detail</a>
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
