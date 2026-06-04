<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">Manajemen User</h2>
            <a href="{{ route('users.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">+ Tambah User</a>
        </div>
    </x-slot>

    <div class="py-8" x-data="{ showModal: false, targetName: '', targetForm: null }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @include('partials.alert')
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-gray-600 font-medium">Nama</th>
                                <th class="px-4 py-3 text-left text-gray-600 font-medium">NIP</th>
                                <th class="px-4 py-3 text-left text-gray-600 font-medium">Email</th>
                                <th class="px-4 py-3 text-left text-gray-600 font-medium">Jabatan</th>
                                <th class="px-4 py-3 text-left text-gray-600 font-medium">Role</th>
                                <th class="px-4 py-3 text-left text-gray-600 font-medium">Pejabat</th>
                                <th class="px-4 py-3 text-left text-gray-600 font-medium">Status</th>
                                <th class="px-4 py-3 text-left text-gray-600 font-medium">Akses Absensi</th>
                                <th class="px-4 py-3 text-left text-gray-600 font-medium">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($users as $u)
                            <tr class="hover:bg-gray-50 {{ !$u->is_active ? 'bg-gray-50 opacity-75' : '' }}">
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $u->name }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $u->nip ?? '-' }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $u->email }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $u->jabatan ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold
                                        @if($u->role === 'admin') bg-purple-100 text-purple-800
                                        @elseif($u->role === 'pejabat') bg-blue-100 text-blue-800
                                        @elseif($u->role === 'staff') bg-green-100 text-green-800
                                        @else bg-yellow-100 text-yellow-800 @endif">
                                        {{ ucfirst($u->role) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-500">{{ $u->pejabat?->name ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    @if($u->is_active)
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">Aktif</span>
                                    @else
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if($u->isPejabat())
                                        <form method="POST" action="{{ route('users.toggleAbsensiAccess', $u) }}" class="inline">
                                            @csrf @method('PATCH')
                                            <button type="submit"
                                                class="px-2 py-0.5 rounded-full text-xs font-semibold transition
                                                    {{ $u->can_view_absensi ? 'bg-teal-100 text-teal-700 hover:bg-teal-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                                                {{ $u->can_view_absensi ? 'Ada Akses ✓' : 'Tidak Ada Akses' }}
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex gap-2 items-center">
                                        <a href="{{ route('users.edit', $u) }}" class="text-blue-600 hover:underline text-xs">Edit</a>
                                        @if($u->id !== Auth::id())
                                            {{-- Form toggle aktif/nonaktif --}}
                                            <form id="toggle-form-{{ $u->id }}" method="POST" action="{{ route('users.toggleActive', $u) }}" class="hidden">
                                                @csrf @method('PATCH')
                                            </form>
                                            @if($u->is_active)
                                                <button type="button"
                                                    class="text-orange-600 hover:underline text-xs"
                                                    @click="targetName = '{{ addslashes($u->name) }}'; targetForm = 'toggle-form-{{ $u->id }}'; showModal = true">
                                                    Nonaktifkan
                                                </button>
                                            @else
                                                <button type="button"
                                                    class="text-green-600 hover:underline text-xs"
                                                    @click="document.getElementById('toggle-form-{{ $u->id }}').submit()">
                                                    Aktifkan
                                                </button>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="p-4">{{ $users->links() }}</div>
                </div>
            </div>
        </div>

        {{-- Modal konfirmasi nonaktifkan --}}
        <template x-teleport="body">
            <div x-show="showModal"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
                 style="display: none;">
                <div class="absolute inset-0 bg-black bg-opacity-50" @click="showModal = false"></div>
                <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-md mx-auto z-10">
                    <div class="p-6">
                        <div class="flex items-start gap-3 mb-4">
                            <div class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded-full bg-orange-100">
                                <svg class="w-5 h-5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-semibold text-gray-900">Nonaktifkan Akun</h3>
                                <p class="mt-1 text-sm text-gray-600">
                                    Apakah Anda yakin ingin menonaktifkan akun <strong x-text="targetName"></strong>?
                                    User tidak akan bisa login sampai diaktifkan kembali.
                                </p>
                            </div>
                        </div>
                        <div class="flex justify-end gap-3 mt-6">
                            <button type="button"
                                @click="showModal = false"
                                class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                                Tidak
                            </button>
                            <button type="button"
                                @click="document.getElementById(targetForm).submit()"
                                class="px-5 py-2.5 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition">
                                Ya
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</x-app-layout>
