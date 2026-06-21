<?php

namespace Database\Seeders;

use App\Models\HotelSetting;
use Illuminate\Database\Seeder;

class HotelSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        HotelSetting::create([
            'name' => 'PPKD Hootel',
            'address' => 'Jl. Raya Kejora No. 88, Yogyakarta, Indonesia',
            'phone' => '+62 274 555-8888',
            'tax_rate' => 10.00,
            'service_charge_rate' => 5.00,
            'breakfast_threshold' => 600000.00,
            'invoice_prefix' => 'INV',
            'booking_prefix' => 'BK',
        ]);
    }
}
