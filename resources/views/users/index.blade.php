<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">Manajemen User</h2>
            <a href="{{ route('users.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">+ Tambah User</a>
        </div>
    </x-slot>

    <div class="py-8">
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
                                <th class="px-4 py-3 text-left text-gray-600 font-medium">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($users as $u)
                            <tr class="hover:bg-gray-50">
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
                                    <div class="flex gap-2">
                                        <a href="{{ route('users.edit', $u) }}" class="text-blue-600 hover:underline text-xs">Edit</a>
                                        @if($u->id !== Auth::id())
                                        <form method="POST" action="{{ route('users.destroy', $u) }}" onsubmit="return confirm('Hapus user {{ $u->name }}?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:underline text-xs">Hapus</button>
                                        </form>
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
    </div>
</x-app-layout>
