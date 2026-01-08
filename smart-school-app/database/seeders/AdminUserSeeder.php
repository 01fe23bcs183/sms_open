<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates the default admin user for initial login with full system access
     * 
     * Default credentials:
     * Email: admin@smartschool.com (or ADMIN_EMAIL env var)
     * Password: Set via ADMIN_PASSWORD env var (required for production)
     */
    public function run(): void
    {
        $adminEmail = env('ADMIN_EMAIL', 'admin@smartschool.com');
        $adminPassword = env('ADMIN_PASSWORD', env('APP_ENV') === 'production' ? null : 'password123');
        
        if (empty($adminPassword)) {
            throw new \RuntimeException('ADMIN_PASSWORD environment variable must be set in production');
        }
        
        $admin = User::firstOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'Admin User',
                'first_name' => 'Admin',
                'last_name' => 'User',
                'email' => $adminEmail,
                'password' => Hash::make($adminPassword),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        // Assign admin role
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole && !$admin->hasRole('admin')) {
            $admin->assignRole($adminRole);
        }

        // Assign all permissions directly to admin user for redundancy
        $allPermissions = Permission::all();
        if ($allPermissions->count() > 0) {
            $admin->syncPermissions($allPermissions);
        }
    }
}
