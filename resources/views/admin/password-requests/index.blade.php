<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Permintaan Reset Password</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            @include('partials.alert')

            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    @if($requests->isEmpty())
                        <div class="p-10 text-center text-gray-400">Tidak ada permintaan reset password.</div>
                    @else
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-gray-600 font-medium">Nama User</th>
                                <th class="px-4 py-3 text-left text-gray-600 font-medium">Username</th>
                                <th class="px-4 py-3 text-left text-gray-600 font-medium">Email</th>
                                <th class="px-4 py-3 text-left text-gray-600 font-medium">Tanggal Request</th>
                                <th class="px-4 py-3 text-left text-gray-600 font-medium">Status</th>
                                <th class="px-4 py-3 text-left text-gray-600 font-medium">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($requests as $req)
                            <tr class="hover:bg-gray-50 {{ $req->status === 'selesai' ? 'opacity-60' : '' }}">
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $req->user->name }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $req->user->username ?? '-' }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $req->user->email }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $req->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3">
                                    @if($req->status === 'pending')
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">Menunggu</span>
                                    @else
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">Selesai</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex gap-2 items-center">
                                        <a href="{{ route('users.edit', $req->user) }}" class="text-blue-600 hover:underline text-xs">Edit User</a>
                                        @if($req->status === 'pending')
                                        <form method="POST" action="{{ route('password-requests.selesai', $req) }}">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="text-green-600 hover:underline text-xs">Tandai Selesai</button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="p-4">{{ $requests->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
