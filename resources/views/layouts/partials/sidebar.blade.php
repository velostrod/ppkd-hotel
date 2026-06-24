@php
    $c = function($cls) {
        return 'md:' . $cls;
    };
@endphp
<!-- Sidebar Backdrop for Mobile -->
<div id="sideBarBackdrop" class="fixed inset-0 bg-black/40 z-20 hidden transition-opacity duration-300 opacity-0"></div>

<!-- start sidebar -->
<div id="sideBar" class="relative flex flex-col bg-white border-r border-gray-300 p-6 flex-none w-64 border-t border-gray-100 {{ $c('-ml-64') }} {{ $c('fixed') }} {{ $c('top-0') }} {{ $c('z-30') }} {{ $c('h-screen') }} {{ $c('shadow-xl') }} transition-all duration-300">
    
    <!-- sidebar content -->
    <div class="flex flex-col flex-grow overflow-y-auto pr-1">

        <!-- Logo and Close Button (Mobile Only) -->
        <div class="flex flex-row items-center justify-between mb-6 hidden {{ $c('flex') }}">
            <!-- Logo & PPKD text -->
            <div class="flex items-center space-x-2">
                <img src="{{ asset('template/image/PPKDJP.png') }}" class="w-8 h-8 object-contain">
                <strong class="capitalize text-teal-600 font-bold tracking-wider text-base sm:text-lg">PPKD</strong>
            </div>

            <!-- Close button -->
            <button id="sideBarHideBtn" class="focus:outline-none text-gray-500 hover:text-gray-700">
                <i class="fad fa-times-circle text-lg"></i>
            </button>
        </div>
        <!-- end Logo and Close Button -->

        @if(auth()->user()->isAdmin() || auth()->user()->isManager())
            <!-- ==========================================
                 ADMIN / MANAGER SIDEBAR
                 ========================================== -->
            <p class="uppercase text-xs text-gray-500 mb-4 tracking-wider font-semibold">Menu Utama</p>

            <!-- link -->
            <a href="{{ route('dashboard') }}" class="mb-3 capitalize font-medium text-sm transition ease-in-out duration-500 flex items-center {{ request()->routeIs('dashboard') ? 'text-teal-600 font-bold' : 'text-gray-700 hover:text-teal-600' }}">
                <i class="fad fa-chart-pie text-xs mr-3 {{ request()->routeIs('dashboard') ? 'text-teal-600' : 'text-gray-400' }}"></i>                
                Dashboard
            </a>
            <!-- end link -->

            @if(!auth()->user()->isManager())
            <!-- link -->
            <a href="{{ route('admin.users') }}" class="mb-3 capitalize font-medium text-sm transition ease-in-out duration-500 flex items-center {{ request()->routeIs('admin.users*') ? 'text-teal-600 font-bold' : 'text-gray-700 hover:text-teal-600' }}">
                <i class="fad fa-users text-xs mr-3 {{ request()->routeIs('admin.users*') ? 'text-teal-600' : 'text-gray-400' }}"></i>
                Staff Management
            </a>
            <!-- end link -->
            @endif

            <!-- link -->
            <a href="{{ route('admin.room-types') }}" class="mb-3 capitalize font-medium text-sm transition ease-in-out duration-500 flex items-center {{ request()->routeIs('admin.room-types*') ? 'text-teal-600 font-bold' : 'text-gray-700 hover:text-teal-600' }}">
                <i class="fad fa-tags text-xs mr-3 {{ request()->routeIs('admin.room-types*') ? 'text-teal-600' : 'text-gray-400' }}"></i>
                Tipe Kamar (Pricing)
            </a>
            <!-- end link -->

            <!-- link -->
            <a href="{{ route('admin.rooms') }}" class="mb-3 capitalize font-medium text-sm transition ease-in-out duration-500 flex items-center {{ request()->routeIs('admin.rooms*') ? 'text-teal-600 font-bold' : 'text-gray-700 hover:text-teal-600' }}">
                <i class="fad fa-door-open text-xs mr-3 {{ request()->routeIs('admin.rooms*') ? 'text-teal-600' : 'text-gray-400' }}"></i>
                Data Kamar
            </a>
            <!-- end link -->

            @if(!auth()->user()->isManager())
            <!-- link -->
            <a href="{{ route('admin.settings') }}" class="mb-3 capitalize font-medium text-sm transition ease-in-out duration-500 flex items-center {{ request()->routeIs('admin.settings*') ? 'text-teal-600 font-bold' : 'text-gray-700 hover:text-teal-600' }}">
                <i class="fad fa-sliders-h text-xs mr-3 {{ request()->routeIs('admin.settings*') ? 'text-teal-600' : 'text-gray-400' }}"></i>
                Hotel Settings
            </a>
            <!-- end link -->
            @endif

            <p class="uppercase text-xs text-gray-500 mb-4 mt-6 tracking-wider font-semibold">Laporan</p>

            <!-- link -->
            <a href="{{ route('reports.reservations') }}" class="mb-3 capitalize font-medium text-sm transition ease-in-out duration-500 flex items-center {{ request()->routeIs('reports.reservations*') ? 'text-teal-600 font-bold' : 'text-gray-700 hover:text-teal-600' }}">
                <i class="fad fa-file-chart-line text-xs mr-3 {{ request()->routeIs('reports.reservations*') ? 'text-teal-600' : 'text-gray-400' }}"></i>
                Lap. Reservasi
            </a>
            <!-- end link -->

            <!-- link -->
            <a href="{{ route('reports.occupancy') }}" class="mb-3 capitalize font-medium text-sm transition ease-in-out duration-500 flex items-center {{ request()->routeIs('reports.occupancy*') ? 'text-teal-600 font-bold' : 'text-gray-700 hover:text-teal-600' }}">
                <i class="fad fa-analytics text-xs mr-3 {{ request()->routeIs('reports.occupancy*') ? 'text-teal-600' : 'text-gray-400' }}"></i>
                Lap. Occupancy
            </a>
            <!-- end link -->

            <!-- link -->
            <a href="{{ route('reports.fnb') }}" class="mb-3 capitalize font-medium text-sm transition ease-in-out duration-500 flex items-center {{ request()->routeIs('reports.fnb*') ? 'text-teal-600 font-bold' : 'text-gray-700 hover:text-teal-600' }}">
                <i class="fad fa-book-open text-xs mr-3 {{ request()->routeIs('reports.fnb*') ? 'text-teal-600' : 'text-gray-400' }}"></i>
                Lap. FnB
            </a>
            <!-- end link -->

            <!-- link -->
            <a href="{{ route('reports.revenue') }}" class="mb-3 capitalize font-medium text-sm transition ease-in-out duration-500 flex items-center {{ request()->routeIs('reports.revenue*') ? 'text-teal-600 font-bold' : 'text-gray-700 hover:text-teal-600' }}">
                <i class="fad fa-usd-circle text-xs mr-3 {{ request()->routeIs('reports.revenue*') ? 'text-teal-600' : 'text-gray-400' }}"></i>
                Lap. Pendapatan
            </a>
            <!-- end link -->

            <!-- link -->
            <a href="{{ route('reports.summary') }}" class="mb-3 capitalize font-medium text-sm transition ease-in-out duration-500 flex items-center {{ request()->routeIs('reports.summary*') ? 'text-teal-600 font-bold' : 'text-gray-700 hover:text-teal-600' }}">
                <i class="fad fa-chart-line text-xs mr-3 {{ request()->routeIs('reports.summary*') ? 'text-teal-600' : 'text-gray-400' }}"></i>
                Lap. Summary
            </a>
            <!-- end link -->

        @elseif(auth()->user()->isFrontOffice())
            <!-- ==========================================
                 FRONT OFFICE SIDEBAR
                 ========================================== -->
            <p class="uppercase text-xs text-gray-500 mb-4 tracking-wider font-semibold">Resepsionis</p>

            <!-- link -->
            <a href="{{ route('dashboard') }}" class="mb-3 capitalize font-medium text-sm transition ease-in-out duration-500 flex items-center {{ request()->routeIs('dashboard') ? 'text-teal-600 font-bold' : 'text-gray-700 hover:text-teal-600' }}">
                <i class="fad fa-hotel text-xs mr-3 {{ request()->routeIs('dashboard') ? 'text-teal-600' : 'text-gray-400' }}"></i>                
                Status Kamar (Board)
            </a>
            <!-- end link -->

            <!-- link -->
            <a href="{{ route('guests.index') }}" class="mb-3 capitalize font-medium text-sm transition ease-in-out duration-500 flex items-center {{ request()->routeIs('guests*') ? 'text-teal-600 font-bold' : 'text-gray-700 hover:text-teal-600' }}">
                <i class="fad fa-users text-xs mr-3 {{ request()->routeIs('guests*') ? 'text-teal-600' : 'text-gray-400' }}"></i>
                Data Tamu
            </a>
            <!-- end link -->

            <!-- link -->
            <a href="{{ route('reservations.index') }}" class="mb-3 capitalize font-medium text-sm transition ease-in-out duration-500 flex items-center {{ request()->routeIs('reservations*') && !request()->routeIs('checkouts*') ? 'text-teal-600 font-bold' : 'text-gray-700 hover:text-teal-600' }}">
                <i class="fad fa-calendar-alt text-xs mr-3 {{ request()->routeIs('reservations*') && !request()->routeIs('checkouts*') ? 'text-teal-600' : 'text-gray-400' }}"></i>
                Reservasi Kamar
            </a>
            <!-- end link -->

            <!-- link -->
            <a href="{{ route('admin.room-types') }}" class="mb-3 capitalize font-medium text-sm transition ease-in-out duration-500 flex items-center {{ request()->routeIs('admin.room-types*') ? 'text-teal-600 font-bold' : 'text-gray-700 hover:text-teal-600' }}">
                <i class="fad fa-tags text-xs mr-3 {{ request()->routeIs('admin.room-types*') ? 'text-teal-600' : 'text-gray-400' }}"></i>
                Tipe Kamar (Pricing)
            </a>
            <!-- end link -->

            <p class="uppercase text-xs text-gray-500 mb-4 mt-6 tracking-wider font-semibold">Layanan Tamu</p>

            <!-- link -->
            <a href="{{ route('services.cleaning') }}" class="mb-3 capitalize font-medium text-sm transition ease-in-out duration-500 flex items-center {{ request()->routeIs('services.cleaning*') ? 'text-teal-600 font-bold' : 'text-gray-700 hover:text-teal-600' }}">
                <i class="fad fa-broom text-xs mr-3 {{ request()->routeIs('services.cleaning*') ? 'text-teal-600' : 'text-gray-400' }}"></i>
                Cleaning Request
            </a>
            <!-- end link -->

            <!-- link -->
            <a href="{{ route('services.laundry') }}" class="mb-3 capitalize font-medium text-sm transition ease-in-out duration-500 flex items-center {{ request()->routeIs('services.laundry*') ? 'text-teal-600 font-bold' : 'text-gray-700 hover:text-teal-600' }}">
                <i class="fad fa-washer text-xs mr-3 {{ request()->routeIs('services.laundry*') ? 'text-teal-600' : 'text-gray-400' }}"></i>
                Laundry Request
            </a>
            <!-- end link -->

            <!-- link -->
            <a href="{{ route('services.fnb') }}" class="mb-3 capitalize font-medium text-sm transition ease-in-out duration-500 flex items-center {{ request()->routeIs('services.fnb*') ? 'text-teal-600 font-bold' : 'text-gray-700 hover:text-teal-600' }}">
                <i class="fad fa-utensils text-xs mr-3 {{ request()->routeIs('services.fnb*') ? 'text-teal-600' : 'text-gray-400' }}"></i>
                Order F&B
            </a>
            <!-- end link -->

        @elseif(auth()->user()->isHousekeeping())
            <!-- ==========================================
                 HOUSEKEEPING SIDEBAR
                 ========================================== -->
            <p class="uppercase text-xs text-gray-500 mb-4 tracking-wider font-semibold">Housekeeping</p>

            <!-- link -->
            <a href="{{ route('dashboard') }}" class="mb-3 capitalize font-medium text-sm transition ease-in-out duration-500 flex items-center {{ request()->routeIs('dashboard') || request()->routeIs('housekeeping.dashboard') ? 'text-teal-600 font-bold' : 'text-gray-700 hover:text-teal-600' }}">
                <i class="fad fa-tasks text-xs mr-3 {{ request()->routeIs('dashboard') || request()->routeIs('housekeeping.dashboard') ? 'text-teal-600' : 'text-gray-400' }}"></i>                
                Antrean Tugas (Board)
            </a>
            <!-- end link -->

            <p class="uppercase text-xs text-gray-500 mb-4 mt-6 tracking-wider font-semibold">Riwayat Tugas</p>

            <!-- link -->
            <a href="{{ route('housekeeping.cleaning-history') }}" class="mb-3 capitalize font-medium text-sm transition ease-in-out duration-500 flex items-center {{ request()->routeIs('housekeeping.cleaning-history*') ? 'text-teal-600 font-bold' : 'text-gray-700 hover:text-teal-600' }}">
                <i class="fad fa-broom text-xs mr-3 {{ request()->routeIs('housekeeping.cleaning-history*') ? 'text-teal-600' : 'text-gray-400' }}"></i>
                Riwayat Pembersihan
            </a>
            <!-- end link -->

            <!-- link -->
            <a href="{{ route('housekeeping.inspection-history') }}" class="mb-3 capitalize font-medium text-sm transition ease-in-out duration-500 flex items-center {{ request()->routeIs('housekeeping.inspection-history*') ? 'text-teal-600 font-bold' : 'text-gray-700 hover:text-teal-600' }}">
                <i class="fad fa-clipboard-check text-xs mr-3 {{ request()->routeIs('housekeeping.inspection-history*') ? 'text-teal-600' : 'text-gray-400' }}"></i>
                Riwayat Inspeksi
            </a>
            <!-- end link -->

        @elseif(auth()->user()->isFnb())
            <!-- ==========================================
                 FNB / RESTORAN SIDEBAR
                 ========================================== -->
            <p class="uppercase text-xs text-gray-500 mb-4 tracking-wider font-semibold">Dapur & Restoran</p>

            <!-- link -->
            <a href="{{ route('dashboard') }}" class="mb-3 capitalize font-medium text-sm transition ease-in-out duration-500 flex items-center {{ request()->routeIs('dashboard') || request()->routeIs('fnb.index') ? 'text-teal-600 font-bold' : 'text-gray-700 hover:text-teal-600' }}">
                <i class="fad fa-list-ol text-xs mr-3 {{ request()->routeIs('dashboard') || request()->routeIs('fnb.index') ? 'text-teal-600' : 'text-gray-400' }}"></i>                
                Antrean Pesanan
            </a>
            <!-- end link -->

            <!-- link -->
            <a href="{{ route('fnb.history') }}" class="mb-3 capitalize font-medium text-sm transition ease-in-out duration-500 flex items-center {{ request()->routeIs('fnb.history*') ? 'text-teal-600 font-bold' : 'text-gray-700 hover:text-teal-600' }}">
                <i class="fad fa-history text-xs mr-3 {{ request()->routeIs('fnb.history*') ? 'text-teal-600' : 'text-gray-400' }}"></i>
                Riwayat Pesanan
            </a>
            <!-- end link -->
        @endif

    </div>
    <!-- end sidebar content -->

    <!-- Profile Dropup & Theme Toggle -->
    <div class="mt-auto border-t border-gray-200 pt-4 flex items-center justify-between">
        <!-- user dropdown -->
        <div class="dropdown relative flex-1">
            <button class="menu-btn focus:outline-none flex items-center w-full text-left">
                <div class="w-8 h-8 overflow-hidden rounded-full border border-gray-300 shrink-0">
                    <img class="w-full h-full object-cover" src="{{ asset('template/dist/img/user.svg') }}" alt="user image">
                </div>
                <div class="ml-2 min-w-0">
                    <h1 class="text-sm text-gray-800 font-semibold leading-tight truncate">{{ auth()->user()->name }}</h1>
                    <span class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider block mt-0.5">{{ auth()->user()->role->name }}</span>
                </div>
            </button>

            <button class="hidden fixed top-0 left-0 z-10 w-full h-full menu-overflow"></button>

            <!-- Dropup menu: absolute bottom-full mb-2 -->
            <div class="text-gray-500 menu hidden rounded bg-white shadow-md absolute z-20 left-0 w-48 mb-2 bottom-full py-1 animated faster border border-gray-100">
                <!-- logout -->
                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" class="w-full text-left px-4 py-2 block capitalize font-medium text-sm tracking-wide bg-white hover:bg-gray-100 hover:text-red-600 transition-all duration-300 ease-in-out">
                        <i class="fad fa-sign-out-alt text-xs mr-2"></i> log out
                    </button>
                </form>
            </div>
        </div>

        <!-- Theme Toggle -->
        <button onclick="toggleTheme()" class="p-2 text-slate-500 hover:text-slate-700 rounded-lg hover:bg-slate-100 focus:outline-none transition-colors shrink-0 ml-2" title="Toggle Night Mode">
            <!-- Sun Icon -->
            <svg id="theme-icon-sun" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l-.707-.707M12 7a5 5 0 100 10 5 5 0 000-10z"/></svg>
            <!-- Moon Icon -->
            <svg id="theme-icon-moon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
        </button>
    </div>

</div>
<!-- end sidebar -->
