<?php

namespace Database\Seeders;

use App\Models\RoomType;
use App\Models\Room;
use Illuminate\Database\Seeder;

class RoomTypeAndRoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Room Types
        $standard = RoomType::create([
            'name' => 'Standard Room',
            'description' => 'Kamar ekonomis dan paling dasar. Fasilitas: Tempat tidur single/queen, AC, TV, Wi-Fi, Kamar mandi dalam, Air mineral.',
            'base_price' => 350000.00,
            'capacity' => 2,
            'breakfast_included' => false,
            'breakfast_price' => 50000.00,
            'extra_bed_allowed' => true,
            'extra_bed_price' => 100000.00,
            'is_active' => true,
        ]);

        $deluxe = RoomType::create([
            'name' => 'Deluxe Room',
            'description' => 'Lebih luas dari standard, lebih nyaman. Fasilitas: Tempat tidur queen/king, AC, TV, Wi-Fi, Meja kerja, Kamar mandi lebih lengkap, Lemari pakaian.',
            'base_price' => 550000.00,
            'capacity' => 2,
            'breakfast_included' => false,
            'breakfast_price' => 50000.00,
            'extra_bed_allowed' => true,
            'extra_bed_price' => 150000.00,
            'is_active' => true,
        ]);

        $superior = RoomType::create([
            'name' => 'Superior Room',
            'description' => 'Level di atas standard dengan kenyamanan lebih baik. Fasilitas: Tempat tidur queen/king, AC, TV, Wi-Fi, Meja kerja, Amenities lebih lengkap.',
            'base_price' => 650000.00,
            'capacity' => 2,
            'breakfast_included' => true, // > Rp600.000 threshold
            'breakfast_price' => 0.00,
            'extra_bed_allowed' => true,
            'extra_bed_price' => 150000.00,
            'is_active' => true,
        ]);

        $studio = RoomType::create([
            'name' => 'Studio Room',
            'description' => 'Kamar modern dengan layout multifungsi. Fasilitas: Area tidur dan area duduk menyatu, AC, TV, Wi-Fi, Meja kerja, Kamar mandi dalam, Pantry kecil.',
            'base_price' => 800000.00,
            'capacity' => 2,
            'breakfast_included' => true, // > Rp600.000 threshold
            'breakfast_price' => 0.00,
            'extra_bed_allowed' => true,
            'extra_bed_price' => 200000.00,
            'is_active' => true,
        ]);

        $suite = RoomType::create([
            'name' => 'Suite Room',
            'description' => 'Kamar premium dengan ruang lebih luas. Fasilitas: Ruang tidur dan ruang duduk terpisah, AC, TV, Wi-Fi, Sofa, Kamar mandi premium, Amenities lengkap, Minibar.',
            'base_price' => 1200000.00,
            'capacity' => 3,
            'breakfast_included' => true, // > Rp600.000 threshold
            'breakfast_price' => 0.00,
            'extra_bed_allowed' => true,
            'extra_bed_price' => 250000.00,
            'is_active' => true,
        ]);

        $connecting = RoomType::create([
            'name' => 'Connecting Room',
            'description' => 'Dua kamar yang terhubung dengan pintu penghubung. Fasilitas: Dua kamar terpisah dengan pintu penghubung, AC, TV, Wi-Fi, Kamar mandi masing-masing.',
            'base_price' => 1500000.00,
            'capacity' => 4,
            'breakfast_included' => true, // > Rp600.000 threshold
            'breakfast_price' => 0.00,
            'extra_bed_allowed' => true,
            'extra_bed_price' => 300000.00,
            'is_active' => true,
        ]);

        // 2. Physical Rooms
        // Floor 1 (101 - 105)
        Room::create(['room_number' => '101', 'room_type_id' => $standard->id, 'floor' => 1, 'status' => 'available', 'notes' => 'Kamar Standard dekat lift']);
        Room::create(['room_number' => '102', 'room_type_id' => $standard->id, 'floor' => 1, 'status' => 'available', 'notes' => 'Kamar Standard standard view']);
        Room::create(['room_number' => '103', 'room_type_id' => $standard->id, 'floor' => 1, 'status' => 'available', 'notes' => 'Kamar Standard standard view']);
        Room::create(['room_number' => '104', 'room_type_id' => $deluxe->id, 'floor' => 1, 'status' => 'available', 'notes' => 'Kamar Deluxe city view']);
        Room::create(['room_number' => '105', 'room_type_id' => $deluxe->id, 'floor' => 1, 'status' => 'available', 'notes' => 'Kamar Deluxe garden view']);

        // Floor 2 (201 - 204)
        Room::create(['room_number' => '201', 'room_type_id' => $superior->id, 'floor' => 2, 'status' => 'available', 'notes' => 'Kamar Superior']);
        Room::create(['room_number' => '202', 'room_type_id' => $superior->id, 'floor' => 2, 'status' => 'available', 'notes' => 'Kamar Superior dengan balkon']);
        Room::create(['room_number' => '203', 'room_type_id' => $studio->id, 'floor' => 2, 'status' => 'available', 'notes' => 'Kamar Studio mini pantry']);
        Room::create(['room_number' => '204', 'room_type_id' => $studio->id, 'floor' => 2, 'status' => 'available', 'notes' => 'Kamar Studio city view']);

        // Floor 3 (301 - 304)
        Room::create(['room_number' => '301', 'room_type_id' => $suite->id, 'floor' => 3, 'status' => 'available', 'notes' => 'Kamar Suite VIP']);
        Room::create(['room_number' => '302', 'room_type_id' => $suite->id, 'floor' => 3, 'status' => 'available', 'notes' => 'Kamar Suite']);
        Room::create(['room_number' => '303', 'room_type_id' => $connecting->id, 'floor' => 3, 'status' => 'available', 'notes' => 'Connecting Room - Unit A']);
        Room::create(['room_number' => '304', 'room_type_id' => $connecting->id, 'floor' => 3, 'status' => 'available', 'notes' => 'Connecting Room - Unit B (Terhubung ke 303)']);
    }
}
