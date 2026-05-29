<nav x-data="{ open: false }" class="bg-white border-b border-gray-100 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                        <img src="{{ asset('images/logo-kjri.png') }}" alt="KJRI Penang" class="h-10 w-10 object-contain rounded-full">
                        <div class="hidden lg:block">
                            <p class="font-bold text-gray-800 text-sm leading-tight">Sistem Keamanan</p>
                            <p class="text-xs text-blue-600 font-medium leading-tight">KJRI Penang</p>
                        </div>
                    </a>
                </div>

                <div class="hidden space-x-1 sm:-my-px sm:ms-6 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        Dashboard
                    </x-nav-link>

                    @if(Auth::user()->canInputTamu())
                    <x-nav-link :href="route('tamu.index')" :active="request()->routeIs('tamu.*')">
                        Data Tamu
                    </x-nav-link>
                    @endif

                    @if(Auth::user()->canApprove())
                    <x-nav-link :href="route('approval.index')" :active="request()->routeIs('approval.*')">
                        Approval
                        @php $pending = \App\Models\Tamu::where('status','menunggu')->when(Auth::user()->isPejabat(), fn($q) => $q->where('pejabat_id', Auth::id()))->count() @endphp
                        @if($pending > 0)
                            <span class="ml-1 bg-red-500 text-white text-xs rounded-full px-1.5 py-0.5">{{ $pending }}</span>
                        @endif
                    </x-nav-link>
                    @endif

                    @if(Auth::user()->canScanQr())
                    <x-nav-link :href="route('scan.index')" :active="request()->routeIs('scan.*')">
                        Scan QR
                    </x-nav-link>
                    @endif

                    @if(Auth::user()->isAdmin())
                    <x-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                        Manajemen User
                    </x-nav-link>
                    <x-nav-link :href="route('laporan.index')" :active="request()->routeIs('laporan.*')">
                        Laporan
                    </x-nav-link>
                    @endif
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <div class="me-3">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        @if(Auth::user()->isAdmin()) bg-purple-100 text-purple-800
                        @elseif(Auth::user()->isPejabat()) bg-blue-100 text-blue-800
                        @elseif(Auth::user()->isStaff()) bg-green-100 text-green-800
                        @else bg-yellow-100 text-yellow-800 @endif">
                        {{ ucfirst(Auth::user()->role) }}
                    </span>
                </div>
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">Profil Saya</x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault();this.closest('form').submit();">
                                Keluar
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">Dashboard</x-responsive-nav-link>
            @if(Auth::user()->canInputTamu())
            <x-responsive-nav-link :href="route('tamu.index')" :active="request()->routeIs('tamu.*')">Data Tamu</x-responsive-nav-link>
            @endif
            @if(Auth::user()->canApprove())
            <x-responsive-nav-link :href="route('approval.index')" :active="request()->routeIs('approval.*')">Approval</x-responsive-nav-link>
            @endif
            @if(Auth::user()->canScanQr())
            <x-responsive-nav-link :href="route('scan.index')" :active="request()->routeIs('scan.*')">Scan QR</x-responsive-nav-link>
            @endif
            @if(Auth::user()->isAdmin())
            <x-responsive-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">Manajemen User</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('laporan.index')" :active="request()->routeIs('laporan.*')">Laporan</x-responsive-nav-link>
            @endif
        </div>
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>
            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">Profil Saya</x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault();this.closest('form').submit();">Keluar</x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
