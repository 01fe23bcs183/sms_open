<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates the 6 user roles for the Smart School Management System
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'admin',
                'guard_name' => 'web',
            ],
            [
                'name' => 'teacher',
                'guard_name' => 'web',
            ],
            [
                'name' => 'student',
                'guard_name' => 'web',
            ],
            [
                'name' => 'parent',
                'guard_name' => 'web',
            ],
            [
                'name' => 'accountant',
                'guard_name' => 'web',
            ],
            [
                'name' => 'librarian',
                'guard_name' => 'web',
            ],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role['name']], $role);
        }
    }
}
