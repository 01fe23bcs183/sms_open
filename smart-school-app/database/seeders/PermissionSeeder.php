<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates permissions for all modules and assigns them to roles
     */
    public function run(): void
    {
        $modules = [
            'students' => ['view', 'create', 'edit', 'delete'],
            'teachers' => ['view', 'create', 'edit', 'delete'],
            'parents' => ['view', 'create', 'edit', 'delete'],
            'classes' => ['view', 'create', 'edit', 'delete'],
            'sections' => ['view', 'create', 'edit', 'delete'],
            'subjects' => ['view', 'create', 'edit', 'delete'],
            'attendance' => ['view', 'create', 'edit', 'delete'],
            'exams' => ['view', 'create', 'edit', 'delete'],
            'marks' => ['view', 'create', 'edit', 'delete'],
            'fees' => ['view', 'create', 'edit', 'delete'],
            'library' => ['view', 'create', 'edit', 'delete'],
            'transport' => ['view', 'create', 'edit', 'delete'],
            'hostel' => ['view', 'create', 'edit', 'delete'],
            'notices' => ['view', 'create', 'edit', 'delete'],
            'events' => ['view', 'create', 'edit', 'delete'],
            'reports' => ['view', 'generate', 'export'],
            'settings' => ['view', 'edit'],
            'users' => ['view', 'create', 'edit', 'delete'],
            'roles' => ['view', 'create', 'edit', 'delete'],
            'permissions' => ['view', 'assign'],
        ];

        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                Permission::firstOrCreate([
                    'name' => "{$module}.{$action}",
                    'guard_name' => 'web',
                ]);
            }
        }

        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo(Permission::all());
        }

        $teacherRole = Role::where('name', 'teacher')->first();
        if ($teacherRole) {
            $teacherPermissions = [
                'students.view', 'classes.view', 'sections.view', 'subjects.view',
                'attendance.view', 'attendance.create', 'attendance.edit',
                'exams.view', 'marks.view', 'marks.create', 'marks.edit',
                'notices.view', 'events.view',
            ];
            $teacherRole->givePermissionTo($teacherPermissions);
        }

        $studentRole = Role::where('name', 'student')->first();
        if ($studentRole) {
            $studentPermissions = [
                'attendance.view', 'exams.view', 'marks.view', 'fees.view',
                'library.view', 'notices.view', 'events.view',
            ];
            $studentRole->givePermissionTo($studentPermissions);
        }

        $parentRole = Role::where('name', 'parent')->first();
        if ($parentRole) {
            $parentPermissions = [
                'students.view', 'attendance.view', 'exams.view', 'marks.view',
                'fees.view', 'notices.view', 'events.view',
            ];
            $parentRole->givePermissionTo($parentPermissions);
        }

        $accountantRole = Role::where('name', 'accountant')->first();
        if ($accountantRole) {
            $accountantPermissions = [
                'students.view', 'fees.view', 'fees.create', 'fees.edit',
                'reports.view', 'reports.generate', 'reports.export',
            ];
            $accountantRole->givePermissionTo($accountantPermissions);
        }

        $librarianRole = Role::where('name', 'librarian')->first();
        if ($librarianRole) {
            $librarianPermissions = [
                'students.view', 'library.view', 'library.create', 'library.edit', 'library.delete',
            ];
            $librarianRole->givePermissionTo($librarianPermissions);
        }
    }
}
