<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates the default admin user for initial login
     */
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@smartschool.com'],
            [
                'name' => 'Administrator',
                'email' => 'admin@smartschool.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole && !$admin->hasRole('admin')) {
            $admin->assignRole($adminRole);
        }
    }
}
