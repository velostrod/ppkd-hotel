<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardRoomBoardTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Seed the full database (roles, users, room types, rooms) before each test.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_fo_dashboard_shows_room_price_and_badges(): void
    {
        // Superior Room (seeded): base_price=650000, capacity=2,
        // breakfast_included=true, extra_bed_allowed=true
        // Standard Room (seeded): base_price=350000, capacity=2,
        // breakfast_included=false, extra_bed_allowed=true

        $foUser = User::whereHas('role', function ($q) {
            $q->where('name', 'front_office');
        })->first();

        $response = $this->actingAs($foUser)->get(route('dashboard'));

        $response->assertStatus(200);

        // Price from Standard Room (Rp 350.000)
        $response->assertSee('350.000');

        // Capacity badge (all rooms have capacity 2)
        $response->assertSee('Kapasitas: 2 orang');

        // Superior/Studio/Suite/Connecting rooms have breakfast_included=true
        $response->assertSee('Breakfast Included');

        // Standard and Deluxe have extra_bed_allowed=true
        $response->assertSee('Extra Bed Tersedia');
    }

    public function test_fo_dashboard_hides_extra_bed_badge_when_not_allowed(): void
    {
        // Verify a room type without extra_bed_allowed does NOT show the badge
        // (All seeded room types have extra_bed_allowed=true, so we create one that doesn't)
        $roomType = RoomType::create([
            'name' => 'No Extra Bed Type',
            'description' => 'Test type',
            'base_price' => 400000.00,
            'capacity' => 1,
            'breakfast_included' => false,
            'breakfast_price' => 0.00,
            'extra_bed_allowed' => false,
            'extra_bed_price' => 0.00,
            'is_active' => true,
        ]);

        // Remove all other rooms and leave only this one so assertDontSee is reliable
        Room::query()->delete();
        Room::create([
            'room_number' => '999',
            'room_type_id' => $roomType->id,
            'floor' => 9,
            'status' => 'available',
        ]);

        $foUser = User::whereHas('role', function ($q) {
            $q->where('name', 'front_office');
        })->first();

        $response = $this->actingAs($foUser)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('400.000');
        $response->assertSee('Kapasitas: 1 orang');
        $response->assertDontSee('Breakfast Included');
        $response->assertDontSee('Extra Bed Tersedia');
    }
}
