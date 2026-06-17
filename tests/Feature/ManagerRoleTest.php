<?php

use App\Models\User;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    $this->managerUser = User::whereHas('role', function ($q) {
        $q->where('name', 'manager');
    })->first();
});

test('manager can view the dashboard showing operational summary', function () {
    $response = $this->actingAs($this->managerUser)
        ->get(route('dashboard'));

    // Should redirect to dashboard rendering the admin summary page or return 200 directly
    // Let's trace if /dashboard returns 200 (adminDashboard)
    $response->assertStatus(200);
    $response->assertSeeText('Dashboard Administrator'); 
    $response->assertSeeText('Log Aktivitas Staf Terbaru');
});

test('manager can access rooms and room types viewing list, but cannot see add/edit buttons', function () {
    $responseRooms = $this->actingAs($this->managerUser)
        ->get(route('admin.rooms'));

    $responseRooms->assertStatus(200);
    $responseRooms->assertDontSee('+ Tambah Kamar');
    $responseRooms->assertDontSee('Edit</button>');

    $responseRoomTypes = $this->actingAs($this->managerUser)
        ->get(route('admin.room-types'));

    $responseRoomTypes->assertStatus(200);
    $responseRoomTypes->assertDontSee('+ Tambah Tipe Kamar');
    $responseRoomTypes->assertDontSee('Edit</button>');
});

test('manager is blocked from modifying rooms and room types data', function () {
    // Attempt to create room
    $roomType = RoomType::first();
    $responseStoreRoom = $this->actingAs($this->managerUser)
        ->post(route('admin.rooms.store'), [
            'room_number' => '999',
            'room_type_id' => $roomType->id,
            'floor' => 9,
        ]);
    $responseStoreRoom->assertStatus(403);

    // Attempt to update room
    $room = Room::first();
    $responseUpdateRoom = $this->actingAs($this->managerUser)
        ->put(route('admin.rooms.update', $room->id), [
            'room_number' => $room->room_number,
            'room_type_id' => $room->room_type_id,
            'floor' => $room->floor,
            'status' => 'maintenance',
        ]);
    $responseUpdateRoom->assertStatus(403);

    // Attempt to create room type
    $responseStoreRoomType = $this->actingAs($this->managerUser)
        ->post(route('admin.room-types.store'), [
            'name' => 'Ultra Deluxe',
            'base_price' => 2000000.00,
            'capacity' => 4,
            'breakfast_price' => 100000.00,
            'extra_bed_price' => 150000.00,
        ]);
    $responseStoreRoomType->assertStatus(403);

    // Attempt to update room type
    $responseUpdateRoomType = $this->actingAs($this->managerUser)
        ->put(route('admin.room-types.update', $roomType->id), [
            'name' => $roomType->name,
            'base_price' => 2500000.00,
            'capacity' => $roomType->capacity,
            'breakfast_price' => $roomType->breakfast_price,
            'extra_bed_price' => $roomType->extra_bed_price,
        ]);
    $responseUpdateRoomType->assertStatus(403);
});

test('manager cannot access hotel settings', function () {
    $responseGetSettings = $this->actingAs($this->managerUser)
        ->get(route('admin.settings'));
    $responseGetSettings->assertStatus(403);

    $responseUpdateSettings = $this->actingAs($this->managerUser)
        ->put(route('admin.settings.update'), [
            'name' => 'New Hotel Name',
        ]);
    $responseUpdateSettings->assertStatus(403);
});

test('manager can access all reports', function () {
    $reports = [
        'reports.reservations',
        'reports.occupancy',
        'reports.fnb',
        'reports.revenue',
        'reports.summary',
    ];

    foreach ($reports as $reportRoute) {
        $response = $this->actingAs($this->managerUser)
            ->get(route($reportRoute));
        $response->assertStatus(200);
    }
});
