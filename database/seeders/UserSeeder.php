<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@simventra.id'],
            [
                'name'     => 'Super Administrator',
                'phone'    => '08100000001',
                'password' => Hash::make('Password@123'),
                'is_active'=> true,
            ]
        );
        $admin->assignRole('Super Admin');

        // Demo user HR
        $hr = User::firstOrCreate(
            ['email' => 'hr@simventra.id'],
            [
                'name'     => 'Staff HR',
                'phone'    => '08100000002',
                'password' => Hash::make('Password@123'),
                'is_active'=> true,
            ]
        );
        $hr->assignRole('HR');

        // Demo user Fleet
        $fleet = User::firstOrCreate(
            ['email' => 'fleet@simventra.id'],
            [
                'name'     => 'Fleet Officer',
                'phone'    => '08100000003',
                'password' => Hash::make('Password@123'),
                'is_active'=> true,
            ]
        );
        $fleet->assignRole('Fleet Officer');

        $this->command->info('✅ Users seeded successfully!');
        $this->command->table(
            ['Email', 'Role', 'Password'],
            [
                ['admin@simventra.id', 'Super Admin', 'Password@123'],
                ['hr@simventra.id', 'HR', 'Password@123'],
                ['fleet@simventra.id', 'Fleet Officer', 'Password@123'],
            ]
        );
    }
}
