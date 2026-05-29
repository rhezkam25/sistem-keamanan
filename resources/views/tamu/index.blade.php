<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">Data Tamu</h2>
            @if(Auth::user()->canInputTamu())
            <a href="{{ route('tamu.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">+ Daftarkan Tamu</a>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @include('partials.alert')

            {{-- Filter --}}
            <div class="bg-white rounded-lg shadow p-4 mb-4">
                <form method="GET" class="flex gap-3 flex-wrap">
                    <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="">Semua Status</option>
                        <option value="menunggu" {{ request('status') === 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                        <option value="disetujui" {{ request('status') === 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                        <option value="ditolak" {{ request('status') === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                    <button type="submit" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm">Filter</button>
                    <a href="{{ route('tamu.index') }}" class="px-4 py-2 text-gray-500 hover:text-gray-700 text-sm">Reset</a>
                </form>
            </div>

            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    @if($tamu->isEmpty())
                        <div class="p-10 text-center text-gray-400">Belum ada data tamu.</div>
                    @else
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-gray-600 font-medium">Nama Tamu</th>
                                <th class="px-4 py-3 text-left text-gray-600 font-medium">No. ID</th>
                                <th class="px-4 py-3 text-left text-gray-600 font-medium">Tujuan</th>
                                <th class="px-4 py-3 text-left text-gray-600 font-medium">Pejabat</th>
                                <th class="px-4 py-3 text-left text-gray-600 font-medium">Status</th>
                                <th class="px-4 py-3 text-left text-gray-600 font-medium">Tanggal</th>
                                <th class="px-4 py-3 text-left text-gray-600 font-medium">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($tamu as $t)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $t->nama }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $t->nomor_id }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ Str::limit($t->tujuan_kunjungan, 35) }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $t->pejabat->name }}</td>
                                <td class="px-4 py-3">
                                    @if($t->status === 'menunggu')
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">Menunggu</span>
                                    @elseif($t->status === 'disetujui')
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">Disetujui</span>
                                    @else
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">Ditolak</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-500">{{ $t->created_at->format('d/m/Y') }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex gap-2">
                                        <a href="{{ route('tamu.show', $t) }}" class="text-blue-600 hover:underline text-xs">Detail</a>
                                        @if($t->disetujui())
                                        <a href="{{ route('tamu.qr', $t) }}" class="text-green-600 hover:underline text-xs">QR Code</a>
                                        @endif
                                        @if($t->menunggu())
                                        <a href="{{ route('tamu.edit', $t) }}" class="text-yellow-600 hover:underline text-xs">Edit</a>
                                        @endif
                                    </div>
                                </td>
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
