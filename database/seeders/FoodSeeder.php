<?php

namespace Database\Seeders;

use App\Models\FoodCategory;
use App\Models\FoodItem;
use Illuminate\Database\Seeder;

class FoodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $makanan = FoodCategory::create(['name' => 'Makanan']);
        $minuman = FoodCategory::create(['name' => 'Minuman']);
        $snack = FoodCategory::create(['name' => 'Snack']);
        $dessert = FoodCategory::create(['name' => 'Dessert']);

        // Makanan
        FoodItem::create(['food_category_id' => $makanan->id, 'name' => 'Nasi Goreng Kejora', 'price' => 45000.00, 'description' => 'Nasi goreng khas Hotel Kejora dengan telur mata sapi, ayam goreng, acar, dan kerupuk.', 'is_available' => true]);
        FoodItem::create(['food_category_id' => $makanan->id, 'name' => 'Mie Goreng Spesial', 'price' => 40000.00, 'description' => 'Mie goreng dengan potongan bakso, sosis, ayam, sayuran, dan telur mata sapi.', 'is_available' => true]);
        FoodItem::create(['food_category_id' => $makanan->id, 'name' => 'Sop Buntut Premium', 'price' => 85000.00, 'description' => 'Sup buntut sapi pilihan dengan wortel, kentang, dan kuah kaldu rempah hangat.', 'is_available' => true]);
        FoodItem::create(['food_category_id' => $makanan->id, 'name' => 'Ayam Goreng Kalasan', 'price' => 55000.00, 'description' => 'Ayam goreng bumbu manis Kalasan yang empuk disajikan dengan sambal dan lalapan.', 'is_available' => true]);

        // Minuman
        FoodItem::create(['food_category_id' => $minuman->id, 'name' => 'Americano', 'price' => 25000.00, 'description' => 'Kopi hitam murni dingin atau hangat menggunakan biji kopi espresso blend.', 'is_available' => true]);
        FoodItem::create(['food_category_id' => $minuman->id, 'name' => 'Es Teh Manis', 'price' => 15000.00, 'description' => 'Es teh manis segar pelepas dahaga.', 'is_available' => true]);
        FoodItem::create(['food_category_id' => $minuman->id, 'name' => 'Jus Jeruk Segar', 'price' => 30000.00, 'description' => 'Jus dari jeruk peras segar alami kaya vitamin C.', 'is_available' => true]);
        FoodItem::create(['food_category_id' => $minuman->id, 'name' => 'Jus Alpukat Premium', 'price' => 35000.00, 'description' => 'Jus alpukat kental dengan topping kental manis coklat.', 'is_available' => true]);

        // Snack
        FoodItem::create(['food_category_id' => $snack->id, 'name' => 'French Fries', 'price' => 25000.00, 'description' => 'Kentang goreng renyah disajikan dengan saus sambal dan mayones.', 'is_available' => true]);
        FoodItem::create(['food_category_id' => $snack->id, 'name' => 'Singkong Keju', 'price' => 20000.00, 'description' => 'Singkong goreng merekah yang gurih dengan taburan keju cheddar parut.', 'is_available' => true]);

        // Dessert
        FoodItem::create(['food_category_id' => $dessert->id, 'name' => 'Pisang Bakar Coklat Keju', 'price' => 30000.00, 'description' => 'Pisang bakar manis bertabur meses coklat dan keju parut.', 'is_available' => true]);
        FoodItem::create(['food_category_id' => $dessert->id, 'name' => 'Es Krim Vanilla Cup', 'price' => 25000.00, 'description' => 'Tiga scoop es krim vanilla premium dengan wafer roll.', 'is_available' => true]);
    }
}
