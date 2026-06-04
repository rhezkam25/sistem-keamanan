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

            {{-- Filter & Search --}}
            <div class="bg-white rounded-lg shadow p-4 mb-4">
                <form method="GET" id="filterForm" class="flex flex-wrap gap-3 items-end">
                    {{-- Pertahankan sort & direction saat filter disubmit --}}
                    <input type="hidden" name="sort" value="{{ $sort }}">
                    <input type="hidden" name="direction" value="{{ $direction }}">

                    {{-- Search --}}
                    <div class="flex-1 min-w-[180px]">
                        <label class="block text-xs text-gray-500 mb-1">Cari</label>
                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Nama atau No. KTP..."
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                        />
                    </div>

                    {{-- Status --}}
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Status</label>
                        <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            <option value="">Semua</option>
                            <option value="menunggu"  {{ request('status') === 'menunggu'  ? 'selected' : '' }}>Menunggu</option>
                            <option value="disetujui" {{ request('status') === 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                            <option value="ditolak"   {{ request('status') === 'ditolak'   ? 'selected' : '' }}>Ditolak</option>
                        </select>
                    </div>

                    {{-- Tanggal Dari --}}
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Dari Tanggal</label>
                        <input
                            type="date"
                            id="dari_tanggal"
                            name="dari_tanggal"
                            value="{{ request('dari_tanggal') }}"
                            max="{{ now()->format('Y-m-d') }}"
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                        />
                    </div>

                    {{-- Tanggal Sampai --}}
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Sampai Tanggal</label>
                        <input
                            type="date"
                            id="sampai_tanggal"
                            name="sampai_tanggal"
                            value="{{ request('sampai_tanggal') }}"
                            max="{{ now()->format('Y-m-d') }}"
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                        />
                        <p id="rentang_warning" class="text-xs text-red-500 mt-1 hidden">Maksimal rentang 90 hari.</p>
                    </div>

                    {{-- Per page --}}
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Tampilkan</label>
                        <select name="per_page" class="border border-gray-300 rounded-lg px-6 py-2 text-sm" onchange="this.form.submit()">
                            <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                            <option value="20" {{ $perPage == 20 ? 'selected' : '' }}>20</option>
                            <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                        </select>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">Cari</button>
                        <a href="{{ route('tamu.index') }}" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 text-sm">Reset</a>
                    </div>
                </form>

                <script>
                (function () {
                    const dari    = document.getElementById('dari_tanggal');
                    const sampai  = document.getElementById('sampai_tanggal');
                    const warning = document.getElementById('rentang_warning');
                    const MAX_DAYS = 90;

                    function addDays(dateStr, days) {
                        const d = new Date(dateStr);
                        d.setDate(d.getDate() + days);
                        return d.toISOString().split('T')[0];
                    }

                    function diffDays(a, b) {
                        return Math.round((new Date(b) - new Date(a)) / 86400000);
                    }

                    function validate() {
                        if (!dari.value || !sampai.value) { warning.classList.add('hidden'); return; }
                        const diff = diffDays(dari.value, sampai.value);
                        if (diff < 0) {
                            sampai.value = dari.value;
                        }
                        warning.classList.toggle('hidden', diff <= MAX_DAYS);
                    }

                    dari.addEventListener('change', function () {
                        if (!this.value) return;
                        // Atur minimum sampai = dari, maksimum = dari + 90
                        sampai.min = this.value;
                        const max90 = addDays(this.value, MAX_DAYS);
                        const today = new Date().toISOString().split('T')[0];
                        sampai.max = max90 < today ? max90 : today;
                        // Jika sampai sudah diisi tapi di luar rentang, reset
                        if (sampai.value && (sampai.value < this.value || diffDays(this.value, sampai.value) > MAX_DAYS)) {
                            sampai.value = '';
                        }
                        validate();
                    });

                    sampai.addEventListener('change', validate);

                    // Inisialisasi saat halaman load (jika ada nilai dari query)
                    if (dari.value) dari.dispatchEvent(new Event('change'));
                })();
                </script>
            </div>

            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    @if($tamu->isEmpty())
                        <div class="p-10 text-center text-gray-400">Belum ada data tamu.</div>
                    @else

                    @php
                        $sortUrl = function($col) use ($sort, $direction) {
                            return request()->fullUrlWithQuery([
                                'sort'      => $col,
                                'direction' => ($sort === $col && $direction === 'asc') ? 'desc' : 'asc',
                                'page'      => 1,
                            ]);
                        };
                        $sortIcon = function($col) use ($sort, $direction) {
                            if ($sort !== $col) return '<span class="text-gray-300 text-xs">⇅</span>';
                            return $direction === 'asc' ? '<span class="text-blue-500 text-xs">▲</span>' : '<span class="text-blue-500 text-xs">▼</span>';
                        };
                    @endphp

                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">
                                    <a href="{{ $sortUrl('nama') }}" class="inline-flex items-center gap-1 hover:text-blue-600">
                                        Nama Tamu {!! $sortIcon('nama') !!}
                                    </a>
                                </th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">
                                    <a href="{{ $sortUrl('nomor_id') }}" class="inline-flex items-center gap-1 hover:text-blue-600">
                                        No. ID {!! $sortIcon('nomor_id') !!}
                                    </a>
                                </th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">Tujuan</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">
                                    <a href="{{ $sortUrl('pejabat') }}" class="inline-flex items-center gap-1 hover:text-blue-600">
                                        Pejabat {!! $sortIcon('pejabat') !!}
                                    </a>
                                </th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">Status</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">
                                    <a href="{{ $sortUrl('created_at') }}" class="inline-flex items-center gap-1 hover:text-blue-600">
                                        Tanggal {!! $sortIcon('created_at') !!}
                                    </a>
                                </th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($tamu as $t)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $t->nama }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $t->nomor_id }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ Str::limit($t->tujuan_kunjungan, 35) }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $t->pejabat?->name ?? 'N/A' }}</td>
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

                    <div class="px-4 py-3 border-t border-gray-100 flex items-center justify-between">
                        <p class="text-xs text-gray-400">
                            Menampilkan {{ $tamu->firstItem() }}–{{ $tamu->lastItem() }} dari {{ $tamu->total() }} data
                        </p>
                        {{ $tamu->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
