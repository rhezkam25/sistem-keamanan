<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Approval Kunjungan Tamu</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @include('partials.alert')

            @if($tamu->isEmpty())
                <div class="bg-white rounded-lg shadow p-10 text-center text-gray-400">
                    <p class="text-lg">Tidak ada permintaan kunjungan yang menunggu persetujuan.</p>
                </div>
            @else
            <div class="space-y-4">
                @foreach($tamu as $t)
                <div class="bg-white rounded-lg shadow p-5">
                    <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <h3 class="text-base font-semibold text-gray-800">{{ $t->nama }}</h3>
                                <span class="px-2 py-0.5 rounded-full text-xs bg-yellow-100 text-yellow-800 font-medium">Menunggu</span>
                            </div>
                            <dl class="grid grid-cols-2 md:grid-cols-3 gap-x-4 gap-y-1 text-sm">
                                <div><dt class="text-gray-400">No. KTP</dt><dd class="font-medium">{{ $t->nomor_id }}</dd></div>
                                <div><dt class="text-gray-400">No. HP</dt><dd class="font-medium">{{ $t->no_hp }}</dd></div>
                                <div><dt class="text-gray-400">Kendaraan</dt><dd class="font-medium">{{ $t->jenis_kendaraan ? $t->jenis_kendaraan . ' — ' . $t->plat_kendaraan : '-' }}</dd></div>
                                <div class="col-span-2 md:col-span-3"><dt class="text-gray-400">Tujuan</dt><dd class="font-medium">{{ $t->tujuan_kunjungan }}</dd></div>
                                <div><dt class="text-gray-400">Didaftarkan oleh</dt><dd class="font-medium">{{ $t->pendaftar?->name ?? 'N/A' }}</dd></div>
                                <div><dt class="text-gray-400">Tanggal</dt><dd class="font-medium">{{ $t->created_at->format('d/m/Y H:i') }}</dd></div>
                            </dl>
                        </div>

                        <div class="flex flex-col gap-3 min-w-[220px]">
                            {{-- Setujui --}}
                            <form method="POST" action="{{ route('approval.setujui', $t) }}">
                                @csrf
                                <textarea name="catatan" placeholder="Catatan (opsional)" rows="2" class="w-full border-gray-300 rounded-md text-sm mb-2 focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium">
                                    Setujui & Generate QR
                                </button>
                            </form>

                            {{-- Tolak --}}
                            <form method="POST" action="{{ route('approval.tolak', $t) }}" x-data="{ open: false }">
                                @csrf
                                <div x-show="!open">
                                    <button type="button" @click="open = true" class="w-full px-4 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 text-sm font-medium">
                                        Tolak
                                    </button>
                                </div>
                                <div x-show="open" class="space-y-2">
                                    <textarea name="catatan" placeholder="Alasan penolakan *" rows="2" class="w-full border-gray-300 rounded-md text-sm focus:border-indigo-500 focus:ring-indigo-500" required></textarea>
                                    <div class="flex gap-2">
                                        <button type="button" @click="open = false" class="flex-1 px-3 py-1.5 bg-gray-200 text-gray-700 rounded-lg text-sm">Batal</button>
                                        <button type="submit" class="flex-1 px-3 py-1.5 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-medium">Konfirmasi Tolak</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
                <div>{{ $tamu->links() }}</div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
