<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Laporan Kunjungan Tamu</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @include('partials.alert')

            {{-- Filter --}}
            <div class="bg-white rounded-lg shadow p-4 mb-4">
                <form method="GET" class="flex flex-wrap gap-3 items-end">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Status</label>
                        <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            <option value="">Semua</option>
                            <option value="menunggu" {{ request('status') === 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                            <option value="disetujui" {{ request('status') === 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                            <option value="ditolak" {{ request('status') === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Dari Tanggal</label>
                        <input type="date" name="dari" value="{{ request('dari') }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Sampai Tanggal</label>
                        <input type="date" name="sampai" value="{{ request('sampai') }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    </div>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">Filter</button>
                    <a href="{{ route('laporan.index') }}" class="px-4 py-2 text-gray-500 hover:text-gray-700 text-sm">Reset</a>
                </form>
            </div>

            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    @if($tamu->isEmpty())
                        <div class="p-10 text-center text-gray-400">Tidak ada data untuk ditampilkan.</div>
                    @else
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-gray-600 font-medium">Nama Tamu</th>
                                <th class="px-4 py-3 text-left text-gray-600 font-medium">No. KTP</th>
                                <th class="px-4 py-3 text-left text-gray-600 font-medium">Tujuan</th>
                                <th class="px-4 py-3 text-left text-gray-600 font-medium">Pendaftar</th>
                                <th class="px-4 py-3 text-left text-gray-600 font-medium">Pejabat</th>
                                <th class="px-4 py-3 text-left text-gray-600 font-medium">Status</th>
                                <th class="px-4 py-3 text-left text-gray-600 font-medium">Masuk</th>
                                <th class="px-4 py-3 text-left text-gray-600 font-medium">Keluar</th>
                                <th class="px-4 py-3 text-left text-gray-600 font-medium">Tgl Daftar</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($tamu as $t)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $t->nama }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $t->nomor_id }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ Str::limit($t->tujuan_kunjungan, 30) }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $t->pendaftar->name }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $t->pejabat->name }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                                        @if($t->status === 'menunggu') bg-yellow-100 text-yellow-800
                                        @elseif($t->status === 'disetujui') bg-green-100 text-green-800
                                        @else bg-red-100 text-red-800 @endif">
                                        {{ ucfirst($t->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-500 text-xs">
                                    {{ $t->kunjungan->where('jenis', 'masuk')->first()?->waktu_scan?->format('d/m H:i') ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-gray-500 text-xs">
                                    {{ $t->kunjungan->where('jenis', 'keluar')->first()?->waktu_scan?->format('d/m H:i') ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-gray-500">{{ $t->created_at->format('d/m/Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="p-4">{{ $tamu->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
