<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Super Admin
        User::updateOrCreate(
            ['phone' => '08000000001'],
            [
                'name'     => 'Super Admin',
                'email'    => 'superadmin@countryyoghurt.com',
                'phone'    => '08000000001',
                'role'     => 'super_admin',
                'password' => bcrypt('password'),
                'is_active'=> true,
            ]
        );

        // General Manager
        User::updateOrCreate(
            ['phone' => '08000000002'],
            [
                'name'     => 'General Manager',
                'email'    => 'gm@countryyoghurt.com',
                'phone'    => '08000000002',
                'role'     => 'general_manager',
                'password' => bcrypt('password'),
                'is_active'=> true,
            ]
        );

        // Production Manager
        User::updateOrCreate(
            ['phone' => '08000000003'],
            [
                'name'     => 'Production Manager',
                'email'    => 'production@countryyoghurt.com',
                'phone'    => '08000000003',
                'role'     => 'production_manager',
                'password' => bcrypt('password'),
                'is_active'=> true,
            ]
        );

        // Store Manager
        User::updateOrCreate(
            ['phone' => '08000000004'],
            [
                'name'     => 'Store Manager',
                'email'    => 'store@countryyoghurt.com',
                'phone'    => '08000000004',
                'role'     => 'store_manager',
                'password' => bcrypt('password'),
                'is_active'=> true,
            ]
        );

        // Sample Distributor
        User::updateOrCreate(
            ['phone' => '08000000005'],
            [
                'name'         => 'Demo Distributor',
                'email'        => 'distributor@demo.com',
                'phone'        => '08000000005',
                'role'         => 'distributor',
                'company_name' => 'Demo Distribution Ltd',
                'state'        => 'Lagos',
                'lga'          => 'Ikeja',
                'password'     => bcrypt('password'),
                'is_active'    => true,
            ]
        );
    }
}
