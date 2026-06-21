<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        $foRole = Role::where('name', 'front_office')->first();
        $hkRole = Role::where('name', 'housekeeping')->first();
        $fnbRole = Role::where('name', 'fnb')->first();
        $managerRole = Role::where('name', 'manager')->first();

        // 1. Admin
        User::create([
            'name' => 'Administrator Kejora',
            'email' => 'admin@ppkdhotel.com',
            'password' => Hash::make('jayajaya'),
            'role_id' => $adminRole->id,
            'status' => 'active',
        ]);

        // 2. FO
        User::create([
            'name' => 'Front Office Staff',
            'email' => 'fo@ppkdhotel.com',
            'password' => Hash::make('jayajaya'),
            'role_id' => $foRole->id,
            'status' => 'active',
        ]);

        // 3. HK
        User::create([
            'name' => 'Housekeeping Staff',
            'email' => 'hk@ppkdhotel.com',
            'password' => Hash::make('jayajaya'),
            'role_id' => $hkRole->id,
            'status' => 'active',
        ]);

        // 4. FnB
        User::create([
            'name' => 'Food & Beverage Staff',
            'email' => 'fnb@ppkdhotel.com',
            'password' => Hash::make('jayajaya'),
            'role_id' => $fnbRole->id,
            'status' => 'active',
        ]);

        // 5. Manager
        User::create([
            'name' => 'Manager Hotel',
            'email' => 'manager@ppkdhotel.com',
            'password' => Hash::make('jayajaya'),
            'role_id' => $managerRole->id,
            'status' => 'active',
        ]);
    }
}
