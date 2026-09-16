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
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // =====================
        // ROLES
        // =====================

        // Super Admin – akses penuh (bypass semua permission check)
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $superAdmin->syncPermissions(Permission::all());

        // Manajemen / QMS Reviewer – read-only dashboard + laporan
        $manajemen = Role::firstOrCreate(['name' => 'Manajemen']);
        $manajemen->syncPermissions([
            'view dashboard',
            'view employees',
            'view vehicles',
            'view employee documents',
            'view vehicle documents',
            'view reminders',
            'view reports',
            'export reports',
        ]);

        // HR/Personalia – kelola karyawan
        $hr = Role::firstOrCreate(['name' => 'HR']);
        $hr->syncPermissions([
            'view dashboard',
            'view employees',
            'create employees',
            'edit employees',
            'view employee documents',
            'upload employee documents',
            'delete employee documents',
            'view reminders',
            'view reports',
        ]);

        // Fleet / Control Tower Officer – kelola kendaraan & sopir
        $fleet = Role::firstOrCreate(['name' => 'Fleet Officer']);
        $fleet->syncPermissions([
            'view dashboard',
            'view employees',
            'view vehicles',
            'create vehicles',
            'edit vehicles',
            'view vehicle documents',
            'upload vehicle documents',
            'view reminders',
            'view reports',
        ]);

        // Maintenance Officer – kelola perawatan kendaraan
        $maintenance = Role::firstOrCreate(['name' => 'Maintenance Officer']);
        $maintenance->syncPermissions([
            'view dashboard',
            'view vehicles',
            'edit vehicles',
            'view vehicle documents',
            'upload vehicle documents',
            'view reminders',
        ]);

        // Security Officer – keamanan
        $security = Role::firstOrCreate(['name' => 'Security Officer']);
        $security->syncPermissions([
            'view dashboard',
            'view employees',
            'view vehicles',
        ]);

        // Sopir – akses terbatas
        $sopir = Role::firstOrCreate(['name' => 'Sopir']);
        $sopir->syncPermissions([
            'view dashboard',
        ]);

        // Auditor – read-only seluruh data (audit)
        $auditor = Role::firstOrCreate(['name' => 'Auditor']);
        $auditor->syncPermissions([
            'view dashboard',
            'view employees',
            'view vehicles',
            'view employee documents',
            'view vehicle documents',
            'view reminders',
            'view reports',
            'view activity logs',
        ]);

        $this->command->info('✅ Roles & Permissions seeded successfully!');
    }
}
