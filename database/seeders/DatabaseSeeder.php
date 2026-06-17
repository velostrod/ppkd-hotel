<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleAndPermissionSeeder::class,
            UserSeeder::class,
            RoomTypeAndRoomSeeder::class,
            FoodSeeder::class,
            PaymentAndChargeSeeder::class,
            HotelSettingSeeder::class,
        ]);
    }
}
