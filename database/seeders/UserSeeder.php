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
                'username' => 'admin',
                'phone'    => '08100000001',
                'password' => Hash::make('Password@123'),
                'is_active'=> true,
            ]
        );
        $admin->assignRole('Super Admin');

        // Demo user Dispatcher (Operasional Armada & Monitoring)
        $dispatcher = User::firstOrCreate(
            ['email' => 'dispatcher@simventra.id'],
            [
                'name'     => 'Dispatcher Operasional',
                'username' => 'dispatcher',
                'phone'    => '08100000002',
                'password' => Hash::make('Password@123'),
                'is_active'=> true,
            ]
        );
        $dispatcher->assignRole('Dispatcher');

        $this->command->info('✅ Users seeded successfully!');
        $this->command->table(
            ['Username', 'Email', 'Role', 'Password'],
            [
                ['admin', 'admin@simventra.id', 'Super Admin', 'Password@123'],
                ['dispatcher', 'dispatcher@simventra.id', 'Dispatcher', 'Password@123'],
            ]
        );
    }
}
