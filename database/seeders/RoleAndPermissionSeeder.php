<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Define Permissions
        $permissions = [
            // Admin Specific
            'manage_users' => 'Manage system users/staff',
            'manage_roles' => 'Manage roles and permissions',
            'manage_room_types' => 'Manage room types and pricing',
            'manage_rooms' => 'Manage physical rooms',
            'manage_food_items' => 'Manage FnB menu items',
            'manage_payment_methods' => 'Manage active payment methods',
            'manage_charge_types' => 'Manage billing charge types',
            'manage_hotel_settings' => 'Manage global hotel settings',
            'view_reports' => 'View all operational and financial reports',

            // Front Office Specific
            'manage_guests' => 'Manage guest profiles',
            'manage_reservations' => 'Manage room bookings/reservations',
            'process_checkin' => 'Process guest check-in',
            'process_checkout' => 'Process guest checkout',
            'view_room_status' => 'View real-time room statuses',
            'create_housekeeping_requests' => 'Create stayover or checkout cleaning requests',
            'create_laundry_requests' => 'Create guest laundry requests',
            'create_fnb_orders' => 'Place FnB orders for guests',
            'manage_payments' => 'Record and manage payments',
            'view_invoices' => 'View and print invoices',

            // Housekeeping Specific
            'manage_housekeeping_requests' => 'View, accept, and process cleaning requests',
            'manage_room_inspections' => 'Input room inspection details and damage logs',
            'manage_laundry_requests' => 'Process laundry requests',
            'update_room_status' => 'Manually update room statuses',
            'view_cleaning_history' => 'View historical records of cleaning',
            'view_inspection_history' => 'View historical room inspection records',

            // FnB Specific
            'manage_fnb_orders' => 'View and process incoming food & beverage orders',
            'view_order_history' => 'View historical records of FnB orders',
        ];

        $permissionModels = [];
        foreach ($permissions as $name => $description) {
            $permissionModels[$name] = Permission::create([
                'name' => $name,
                'description' => $description,
            ]);
        }

        // 2. Define Roles
        $roles = [
            'admin' => 'Administrator with full access to all features and settings.',
            'front_office' => 'Front Office staff managing guests, reservations, and billing.',
            'housekeeping' => 'Housekeeping staff managing room conditions, requests, and laundry.',
            'fnb' => 'Food and Beverage staff managing food/drink orders and menus.',
            'manager' => 'Hotel Manager with read-only access to master data/settings and full access to operational views and reports.',
        ];

        $roleModels = [];
        foreach ($roles as $name => $description) {
            $roleModels[$name] = Role::create([
                'name' => $name,
                'description' => $description,
            ]);
        }

        // 3. Assign Permissions to Roles
        // Admin gets everything
        $roleModels['admin']->permissions()->sync(array_values(array_map(fn($p) => $p->id, $permissionModels)));

        // Front Office Permissions
        $foPermissions = [
            'view_reports', // Read-only dashboard access
            'manage_guests',
            'manage_reservations',
            'process_checkin',
            'process_checkout',
            'view_room_status',
            'create_housekeeping_requests',
            'create_laundry_requests',
            'create_fnb_orders',
            'manage_payments',
            'view_invoices',
        ];
        $foIds = array_map(fn($name) => $permissionModels[$name]->id, $foPermissions);
        $roleModels['front_office']->permissions()->sync($foIds);

        // Housekeeping Permissions
        $hkPermissions = [
            'view_room_status',
            'manage_housekeeping_requests',
            'manage_room_inspections',
            'manage_laundry_requests',
            'update_room_status',
            'view_cleaning_history',
            'view_inspection_history',
        ];
        $hkIds = array_map(fn($name) => $permissionModels[$name]->id, $hkPermissions);
        $roleModels['housekeeping']->permissions()->sync($hkIds);

        // FnB Permissions
        $fnbPermissions = [
            'manage_fnb_orders',
            'view_order_history',
        ];
        $fnbIds = array_map(fn($name) => $permissionModels[$name]->id, $fnbPermissions);
        $roleModels['fnb']->permissions()->sync($fnbIds);

        // Manager Permissions (All operational permissions)
        $managerPermissions = array_unique(array_merge(
            $foPermissions,
            $hkPermissions,
            $fnbPermissions
        ));
        $managerIds = array_map(fn($name) => $permissionModels[$name]->id, $managerPermissions);
        $roleModels['manager']->permissions()->sync($managerIds);
    }
}
