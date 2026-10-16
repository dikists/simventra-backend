<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // =====================
        // PERMISSIONS (granular)
        // =====================
        $permissions = [
            // Dashboard
            'view dashboard',

            // Employees
            'view employees',
            'create employees',
            'edit employees',
            'delete employees',
            'view employee documents',
            'upload employee documents',
            'delete employee documents',

            // Vehicles
            'view vehicles',
            'create vehicles',
            'edit vehicles',
            'delete vehicles',
            'view vehicle documents',
            'upload vehicle documents',
            'delete vehicle documents',

            // Reminders
            'view reminders',
            'manage reminders',

            // Activity Logs
            'view activity logs',

            // Users & Roles
            'view users',
            'create users',
            'edit users',
            'delete users',
            'manage roles',

            // Reports
            'view reports',
            'export reports',

            // Maintenance Kendaraan
            'view maintenance',
            'manage maintenance',

            // Log Insiden & Keamanan
            'view incidents',
            'manage incidents',

            // Komplain Pelanggan
            'view complaints',
            'manage complaints',

            // Tinjauan Manajemen (QMS)
            'view reviews',
            'manage reviews',

            // Audit Mode & Kepatuhan
            'view audit mode',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // =====================
        // ROLES
        // =====================

        // 1. Super Admin – akses penuh seluruh sistem (termasuk kelola user & pengaturan)
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $superAdmin->syncPermissions(Permission::all());

        // 2. Dispatcher – operasional monitoring armada, kendaraan, sopir, reminder, & maintenance
        $dispatcher = Role::firstOrCreate(['name' => 'Dispatcher']);
        $dispatcher->syncPermissions([
            'view dashboard',
            'view employees',
            'create employees',
            'edit employees',
            'delete employees',
            'view employee documents',
            'upload employee documents',
            'delete employee documents',
            'view vehicles',
            'create vehicles',
            'edit vehicles',
            'delete vehicles',
            'view vehicle documents',
            'upload vehicle documents',
            'delete vehicle documents',
            'view reminders',
            'manage reminders',
            'view reports',
            'export reports',
            'view maintenance',
            'manage maintenance',
            'view incidents',
            'manage incidents',
            'view complaints',
            'manage complaints',
            'view reviews',
            'manage reviews',
            'view activity logs',
        ]);

        // 3. Sopir – pengemudi armada (akses aplikasi mobile PWA & dashboard sopir)
        $sopir = Role::firstOrCreate(['name' => 'Sopir']);
        $sopir->syncPermissions([
            'view dashboard',
        ]);

        $this->command->info('✅ Roles & Permissions seeded successfully (Super Admin, Dispatcher, Sopir)!');
    }
}
