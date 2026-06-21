# Room Status Board — Harga & Fitur Kamar: Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Tambahkan harga dasar dan icon badges (kapasitas, breakfast, extra bed) dengan CSS tooltip ke setiap kartu kamar di Visual Peta Kamar pada FO dan Admin Dashboard.

**Architecture:** FO Dashboard sudah memiliki Visual Peta Kamar lengkap — hanya perlu tambah harga dan badges di view. Admin Dashboard belum punya Visual Peta Kamar — perlu tambah query `$rooms` di controller dan section baru di view. Semua tooltip menggunakan Tailwind CSS `group`/`group-hover` tanpa JS.

**Tech Stack:** Laravel Blade, Tailwind CSS, PHP 8.x, PHPUnit (feature tests)

## Global Constraints

- Tidak ada perubahan database atau migration
- Tidak ada JS baru — tooltip pure CSS via Tailwind `group`/`group-hover`
- Format harga: `number_format($amount, 0, ',', '.')` dengan prefix `Rp ` (konsisten dengan pola di `admin.blade.php`)
- Eager load `roomType` wajib ada agar tidak N+1 query
- Semua SVG icon menggunakan Heroicons outline style (konsisten dengan codebase)

---

### Task 1: Tambah Harga & Icon Badges ke FO Dashboard

**Files:**
- Modify: `resources/views/dashboard/fo.blade.php`
- Test: `tests/Feature/DashboardRoomBoardTest.php`

**Interfaces:**
- Consumes: `$rooms` (sudah ada, eager load `roomType` sudah aktif di controller baris 68)
- Produces: Kartu kamar menampilkan `base_price`, badge kapasitas, badge breakfast (kondisional), badge extra bed (kondisional)

- [ ] **Step 1: Buat file test baru dan tulis failing test**

Buat `tests/Feature/DashboardRoomBoardTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardRoomBoardTest extends TestCase
{
    use RefreshDatabase;

    public function test_fo_dashboard_shows_room_price_and_badges(): void
    {
        $roomType = RoomType::factory()->create([
            'name' => 'Deluxe',
            'base_price' => 500000,
            'capacity' => 2,
            'breakfast_included' => true,
            'extra_bed_allowed' => false,
        ]);

        Room::factory()->create([
            'room_type_id' => $roomType->id,
            'room_number' => '101',
            'floor' => 1,
            'status' => 'available',
        ]);

        $user = User::factory()->create(['role' => 'front_office']);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('500.000');
        $response->assertSee('Kapasitas: 2 orang');
        $response->assertSee('Breakfast Included');
        $response->assertDontSee('Extra Bed Tersedia');
    }
}
```

- [ ] **Step 2: Jalankan test dan pastikan FAIL**

```bash
cd /srv/http/projects/belajar/Hotel-Kejora-fix && php artisan test tests/Feature/DashboardRoomBoardTest.php --filter test_fo_dashboard_shows_room_price_and_badges
```

Expected: FAIL — elemen harga dan badge belum ada di view.

- [ ] **Step 3: Tambah harga dan icon badges ke kartu di fo.blade.php**

Di `resources/views/dashboard/fo.blade.php`, temukan baris:

```blade
                        <p class="text-[11px] text-slate-400 mt-0.5">Lantai {{ $room->floor }}</p>
```

Tambahkan block berikut **setelah** baris tersebut (sebelum blok `<!-- Guest Info if Reserved/Occupied -->`):

```blade
                        {{-- Harga dasar --}}
                        <p class="text-[11px] font-semibold text-slate-600 mt-2">Rp {{ number_format($room->roomType->base_price, 0, ',', '.') }} <span class="font-normal text-slate-400">/ malam</span></p>

                        {{-- Icon badges dengan CSS tooltip --}}
                        <div class="flex items-center gap-2 mt-2">
                            {{-- Kapasitas --}}
                            <div class="relative group">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 whitespace-nowrap px-2 py-1 bg-slate-800 text-white text-[10px] rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10">
                                    Kapasitas: {{ $room->roomType->capacity }} orang
                                </span>
                            </div>

                            {{-- Breakfast --}}
                            @if($room->roomType->breakfast_included)
                            <div class="relative group">
                                <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 whitespace-nowrap px-2 py-1 bg-slate-800 text-white text-[10px] rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10">
                                    Breakfast Included
                                </span>
                            </div>
                            @endif

                            {{-- Extra Bed --}}
                            @if($room->roomType->extra_bed_allowed)
                            <div class="relative group">
                                <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M12 5l7 7-7 7"/>
                                </svg>
                                <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 whitespace-nowrap px-2 py-1 bg-slate-800 text-white text-[10px] rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10">
                                    Extra Bed Tersedia
                                </span>
                            </div>
                            @endif
                        </div>
```

- [ ] **Step 4: Jalankan test dan pastikan PASS**

```bash
cd /srv/http/projects/belajar/Hotel-Kejora-fix && php artisan test tests/Feature/DashboardRoomBoardTest.php --filter test_fo_dashboard_shows_room_price_and_badges
```

Expected: PASS

- [ ] **Step 5: Commit**

```bash
cd /srv/http/projects/belajar/Hotel-Kejora-fix && git add resources/views/dashboard/fo.blade.php tests/Feature/DashboardRoomBoardTest.php
git commit -m "feat: add room price and feature badges to FO dashboard board"
```

---

### Task 2: Tambah `$rooms` ke Admin Dashboard Controller

**Files:**
- Modify: `app/Http/Controllers/DashboardController.php`
- Test: `tests/Feature/DashboardRoomBoardTest.php` (extend)

**Interfaces:**
- Consumes: `Room::with([...])` — sama persis dengan query di `foDashboard()` baris 68-70
- Produces: `$rooms` tersedia di `dashboard.admin` view

- [ ] **Step 1: Tulis failing test untuk admin dashboard**

Tambahkan method berikut ke `tests/Feature/DashboardRoomBoardTest.php`:

```php
    public function test_admin_dashboard_passes_rooms_to_view(): void
    {
        $roomType = RoomType::factory()->create([
            'base_price' => 750000,
            'capacity' => 3,
            'breakfast_included' => false,
            'extra_bed_allowed' => true,
        ]);

        Room::factory()->create([
            'room_type_id' => $roomType->id,
            'room_number' => '201',
            'status' => 'available',
        ]);

        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('rooms');
        $response->assertSee('750.000');
        $response->assertSee('Extra Bed Tersedia');
    }
```

- [ ] **Step 2: Jalankan test dan pastikan FAIL**

```bash
cd /srv/http/projects/belajar/Hotel-Kejora-fix && php artisan test tests/Feature/DashboardRoomBoardTest.php --filter test_admin_dashboard_passes_rooms_to_view
```

Expected: FAIL — `rooms` belum di-pass ke view admin, dan belum ada markup harga di admin view.

- [ ] **Step 3: Tambah query `$rooms` ke `adminDashboard()` di DashboardController**

Di `app/Http/Controllers/DashboardController.php`, temukan method `adminDashboard()`. Tambahkan query `$rooms` sebelum baris `$recentLogs`:

```php
    private function adminDashboard()
    {
        $roomsCount = Room::count();
        $availableRooms = Room::available()->count();
        $occupiedRooms = Room::where('status', RoomStatus::Occupied->value)->count();
        $dirtyRooms = Room::where('status', RoomStatus::Dirty->value)->count();

        $rooms = Room::with(['roomType', 'reservations' => function ($q) {
            $q->whereIn('status', [ReservationStatus::Confirmed->value, ReservationStatus::CheckedIn->value])->with('guest');
        }])->orderBy('room_number')->get();

        $activeGuestsCount = Reservation::checkedIn()->count();
        // ... sisa method tidak berubah
```

Kemudian di baris `return view('dashboard.admin', compact(...))`, tambahkan `'rooms'`:

```php
        return view('dashboard.admin', compact(
            'roomsCount', 'availableRooms', 'occupiedRooms', 'dirtyRooms',
            'activeGuestsCount', 'todayCheckins', 'todayRevenue', 'recentLogs',
            'rooms'
        ));
```

- [ ] **Step 4: Jalankan test (akan partial pass — viewHas rooms lulus, assertSee masih fail)**

```bash
cd /srv/http/projects/belajar/Hotel-Kejora-fix && php artisan test tests/Feature/DashboardRoomBoardTest.php --filter test_admin_dashboard_passes_rooms_to_view
```

Expected: FAIL masih di `assertSee('750.000')` — view belum render kartu kamar.

- [ ] **Step 5: Commit controller change**

```bash
cd /srv/http/projects/belajar/Hotel-Kejora-fix && git add app/Http/Controllers/DashboardController.php
git commit -m "feat: pass rooms data to admin dashboard controller"
```

---

### Task 3: Tambah Visual Peta Kamar ke Admin Dashboard View

**Files:**
- Modify: `resources/views/dashboard/admin.blade.php`
- Test: `tests/Feature/DashboardRoomBoardTest.php` (test dari Task 2 selesai di sini)

**Interfaces:**
- Consumes: `$rooms` (tersedia dari Task 2), setiap item punya `roomType->name`, `roomType->base_price`, `roomType->capacity`, `roomType->breakfast_included`, `roomType->extra_bed_allowed`, `room_number`, `floor`, `status`, `reservations->first()`

- [ ] **Step 1: Tambah section Visual Peta Kamar ke admin.blade.php**

Di `resources/views/dashboard/admin.blade.php`, temukan baris penutup `</div>` terakhir sebelum `@endsection`. Tambahkan section berikut sebelum `</div>` penutup `<div class="space-y-4 md:space-y-8">`:

```blade
    <!-- Visual Peta Kamar -->
    <div class="bg-white p-4 md:p-6 rounded-2xl border border-slate-100 shadow-sm">
        <h3 class="text-base font-bold text-slate-800 mb-4 md:mb-6">Visual Peta Kamar</h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 md:gap-6">
            @foreach($rooms as $room)
                @php
                    $activeReservation = $room->reservations->first();
                    $cardClass = 'bg-slate-50 border-slate-200';
                    $badgeClass = 'bg-slate-100 text-slate-700';

                    switch($room->status) {
                        case 'available':
                            $cardClass = 'bg-emerald-50/50 border-emerald-200 hover:shadow-md hover:scale-[1.01]';
                            $badgeClass = 'bg-emerald-500 text-white';
                            break;
                        case 'reserved':
                            $cardClass = 'bg-blue-50/50 border-blue-200 hover:shadow-md hover:scale-[1.01]';
                            $badgeClass = 'bg-blue-500 text-white';
                            break;
                        case 'occupied':
                            $cardClass = 'bg-rose-50/50 border-rose-200 hover:shadow-md hover:scale-[1.01]';
                            $badgeClass = 'bg-rose-500 text-white';
                            break;
                        case 'dirty':
                            $cardClass = 'bg-amber-50/50 border-amber-200';
                            $badgeClass = 'bg-amber-500 text-white';
                            break;
                        case 'maintenance':
                        case 'out_of_order':
                            $cardClass = 'bg-red-50/50 border-red-200 opacity-75';
                            $badgeClass = 'bg-red-500 text-white';
                            break;
                    }
                @endphp

                <div class="border rounded-2xl p-5 flex flex-col justify-between transition-all duration-300 {{ $cardClass }}">
                    <div>
                        <div class="flex justify-between items-start">
                            <span class="text-2xl font-bold tracking-tight text-slate-800">#{{ $room->room_number }}</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wider {{ $badgeClass }}">
                                {{ strtoupper($room->status) }}
                            </span>
                        </div>
                        <p class="text-xs font-semibold text-slate-400 mt-1 uppercase tracking-wider">{{ $room->roomType->name }}</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">Lantai {{ $room->floor }}</p>

                        {{-- Harga dasar --}}
                        <p class="text-[11px] font-semibold text-slate-600 mt-2">Rp {{ number_format($room->roomType->base_price, 0, ',', '.') }} <span class="font-normal text-slate-400">/ malam</span></p>

                        {{-- Icon badges dengan CSS tooltip --}}
                        <div class="flex items-center gap-2 mt-2">
                            {{-- Kapasitas --}}
                            <div class="relative group">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 whitespace-nowrap px-2 py-1 bg-slate-800 text-white text-[10px] rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10">
                                    Kapasitas: {{ $room->roomType->capacity }} orang
                                </span>
                            </div>

                            {{-- Breakfast --}}
                            @if($room->roomType->breakfast_included)
                            <div class="relative group">
                                <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 whitespace-nowrap px-2 py-1 bg-slate-800 text-white text-[10px] rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10">
                                    Breakfast Included
                                </span>
                            </div>
                            @endif

                            {{-- Extra Bed --}}
                            @if($room->roomType->extra_bed_allowed)
                            <div class="relative group">
                                <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M12 5l7 7-7 7"/>
                                </svg>
                                <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 whitespace-nowrap px-2 py-1 bg-slate-800 text-white text-[10px] rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10">
                                    Extra Bed Tersedia
                                </span>
                            </div>
                            @endif
                        </div>

                        {{-- Guest Info if Reserved/Occupied --}}
                        @if(($room->status === 'reserved' || $room->status === 'occupied') && $activeReservation)
                            <div class="mt-4 pt-3 border-t border-slate-100">
                                <p class="text-[10px] text-slate-400 uppercase tracking-widest">Tamu Aktif</p>
                                <p class="text-xs font-bold text-slate-700 truncate mt-0.5">{{ $activeReservation->guest->full_name }}</p>
                                <p class="text-[10px] text-slate-500 mt-0.5">Code: {{ $activeReservation->reservation_code }}</p>
                            </div>
                        @endif
                    </div>

                    <div class="mt-6">
                        @if($room->status === 'available')
                            <a href="{{ route('reservations.create', ['room_id' => $room->id]) }}" class="block w-full py-1.5 bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold text-center rounded-lg shadow-sm transition-colors uppercase tracking-wider">
                                Booking Kamar
                            </a>
                        @elseif($room->status === 'reserved' && $activeReservation)
                            <a href="{{ route('checkins.create', $activeReservation->id) }}" class="block w-full py-1.5 bg-blue-500 hover:bg-blue-600 text-white text-xs font-bold text-center rounded-lg shadow-sm transition-colors uppercase tracking-wider">
                                Check-In
                            </a>
                        @elseif($room->status === 'occupied' && $activeReservation)
                            <div class="grid grid-cols-2 gap-2">
                                <a href="{{ route('reservations.show', $activeReservation->id) }}" class="py-1.5 bg-slate-800 hover:bg-slate-700 text-white text-[10px] font-bold text-center rounded-lg transition-colors uppercase">
                                    Detail
                                </a>
                                <a href="{{ route('checkouts.invoice', $activeReservation->id) }}" class="py-1.5 bg-rose-500 hover:bg-rose-600 text-white text-[10px] font-bold text-center rounded-lg transition-colors uppercase">
                                    Checkout
                                </a>
                            </div>
                        @else
                            <button disabled class="w-full py-1.5 bg-slate-200 text-slate-400 text-xs font-semibold text-center rounded-lg uppercase tracking-wider cursor-not-allowed">
                                Operasional HK
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
```

- [ ] **Step 2: Jalankan semua test di file**

```bash
cd /srv/http/projects/belajar/Hotel-Kejora-fix && php artisan test tests/Feature/DashboardRoomBoardTest.php
```

Expected: semua test PASS (2 tests, 2 passed)

- [ ] **Step 3: Jalankan full test suite untuk cek regresi**

```bash
cd /srv/http/projects/belajar/Hotel-Kejora-fix && php artisan test
```

Expected: semua test pass, tidak ada regresi.

- [ ] **Step 4: Commit**

```bash
cd /srv/http/projects/belajar/Hotel-Kejora-fix && git add resources/views/dashboard/admin.blade.php tests/Feature/DashboardRoomBoardTest.php
git commit -m "feat: add Visual Peta Kamar with price and feature badges to admin dashboard"
```
