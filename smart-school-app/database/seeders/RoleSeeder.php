<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates the 6 user roles for the Smart School Management System
     * Each role has a display name and description for better user experience
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'admin',
                'display_name' => 'Administrator',
                'description' => 'Full system access with all permissions. Can manage users, settings, and all modules.',
                'guard_name' => 'web',
            ],
            [
                'name' => 'teacher',
                'display_name' => 'Teacher',
                'description' => 'Can manage classes, attendance, exams, marks, and view student information.',
                'guard_name' => 'web',
            ],
            [
                'name' => 'student',
                'display_name' => 'Student',
                'description' => 'Can view own attendance, exam results, fees, and library information.',
                'guard_name' => 'web',
            ],
            [
                'name' => 'parent',
                'display_name' => 'Parent/Guardian',
                'description' => 'Can view children\'s attendance, exam results, fees, and communicate with teachers.',
                'guard_name' => 'web',
            ],
            [
                'name' => 'accountant',
                'display_name' => 'Accountant',
                'description' => 'Can manage fees, payments, expenses, income, and generate financial reports.',
                'guard_name' => 'web',
            ],
            [
                'name' => 'librarian',
                'display_name' => 'Librarian',
                'description' => 'Can manage library books, members, issue/return books, and track fines.',
                'guard_name' => 'web',
            ],
        ];

        foreach ($roles as $roleData) {
            $role = Role::firstOrCreate(
                ['name' => $roleData['name'], 'guard_name' => $roleData['guard_name']],
                $roleData
            );
            
            // Update display_name and description if role already exists
            if ($role->wasRecentlyCreated === false) {
                $role->update([
                    'display_name' => $roleData['display_name'] ?? null,
                    'description' => $roleData['description'] ?? null,
                ]);
            }
        }
    }
}
