<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use App\Models\ChargeType;
use Illuminate\Database\Seeder;

class PaymentAndChargeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Payment Methods
        PaymentMethod::create(['name' => 'Cash', 'code' => 'cash', 'is_active' => true]);
        PaymentMethod::create(['name' => 'Transfer Bank', 'code' => 'transfer_bank', 'is_active' => true]);
        PaymentMethod::create(['name' => 'QRIS', 'code' => 'qris', 'is_active' => true]);
        PaymentMethod::create(['name' => 'Credit Card', 'code' => 'credit_card', 'is_active' => true]);

        // 2. Charge Types
        ChargeType::create(['name' => 'Extra Bed', 'code' => 'extra_bed', 'base_amount' => 150000.00, 'is_active' => true]);
        ChargeType::create(['name' => 'Damage/Loss', 'code' => 'damage', 'base_amount' => 0.00, 'is_active' => true]);
        ChargeType::create(['name' => 'Laundry Service', 'code' => 'laundry', 'base_amount' => 0.00, 'is_active' => true]);
        ChargeType::create(['name' => 'Minibar Consumables', 'code' => 'minibar', 'base_amount' => 0.00, 'is_active' => true]);
        ChargeType::create(['name' => 'Late Checkout Charge', 'code' => 'late_checkout', 'base_amount' => 0.00, 'is_active' => true]);
        ChargeType::create(['name' => 'Food & Beverage Order', 'code' => 'fnb', 'base_amount' => 0.00, 'is_active' => true]);
    }
}
