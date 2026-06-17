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
    <div class="flex flex-col">

        <!-- Logo and Close Button (Mobile Only) -->
        <div class="flex flex-row items-center justify-between mb-6 hidden {{ $c('flex') }}">
            <!-- Logo & KEJORA text -->
            <div class="flex items-center space-x-2">
                <img src="{{ asset('template/dist/img/logo.png') }}" class="w-8 h-8">
                <strong class="capitalize text-teal-600 font-bold tracking-wider text-base sm:text-lg">KEJORA</strong>
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

</div>
<!-- end sidebar -->
