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
     * Each permission has a display name and module for better organization
     */
    public function run(): void
    {
        // Module definitions with display names
        $modules = [
            'students' => [
                'display_name' => 'Student Management',
                'actions' => ['view', 'create', 'edit', 'delete'],
            ],
            'teachers' => [
                'display_name' => 'Teacher Management',
                'actions' => ['view', 'create', 'edit', 'delete'],
            ],
            'parents' => [
                'display_name' => 'Parent Management',
                'actions' => ['view', 'create', 'edit', 'delete'],
            ],
            'classes' => [
                'display_name' => 'Class Management',
                'actions' => ['view', 'create', 'edit', 'delete'],
            ],
            'sections' => [
                'display_name' => 'Section Management',
                'actions' => ['view', 'create', 'edit', 'delete'],
            ],
            'subjects' => [
                'display_name' => 'Subject Management',
                'actions' => ['view', 'create', 'edit', 'delete'],
            ],
            'attendance' => [
                'display_name' => 'Attendance Management',
                'actions' => ['view', 'create', 'edit', 'delete'],
            ],
            'exams' => [
                'display_name' => 'Examination Management',
                'actions' => ['view', 'create', 'edit', 'delete'],
            ],
            'marks' => [
                'display_name' => 'Marks Management',
                'actions' => ['view', 'create', 'edit', 'delete'],
            ],
            'fees' => [
                'display_name' => 'Fee Management',
                'actions' => ['view', 'create', 'edit', 'delete', 'collect'],
            ],
            'library' => [
                'display_name' => 'Library Management',
                'actions' => ['view', 'create', 'edit', 'delete', 'issue'],
            ],
            'transport' => [
                'display_name' => 'Transport Management',
                'actions' => ['view', 'create', 'edit', 'delete'],
            ],
            'hostel' => [
                'display_name' => 'Hostel Management',
                'actions' => ['view', 'create', 'edit', 'delete'],
            ],
            'notices' => [
                'display_name' => 'Notice Management',
                'actions' => ['view', 'create', 'edit', 'delete'],
            ],
            'events' => [
                'display_name' => 'Event Management',
                'actions' => ['view', 'create', 'edit', 'delete'],
            ],
            'reports' => [
                'display_name' => 'Reports',
                'actions' => ['view', 'generate', 'export'],
            ],
            'settings' => [
                'display_name' => 'System Settings',
                'actions' => ['view', 'edit'],
            ],
            'users' => [
                'display_name' => 'User Management',
                'actions' => ['view', 'create', 'edit', 'delete'],
            ],
            'roles' => [
                'display_name' => 'Role Management',
                'actions' => ['view', 'create', 'edit', 'delete'],
            ],
            'permissions' => [
                'display_name' => 'Permission Management',
                'actions' => ['view', 'assign'],
            ],
        ];

        // Action display names
        $actionDisplayNames = [
            'view' => 'View',
            'create' => 'Create',
            'edit' => 'Edit',
            'delete' => 'Delete',
            'generate' => 'Generate',
            'export' => 'Export',
            'assign' => 'Assign',
            'collect' => 'Collect',
            'issue' => 'Issue',
        ];

        foreach ($modules as $module => $config) {
            foreach ($config['actions'] as $action) {
                $displayName = ($actionDisplayNames[$action] ?? ucfirst($action)) . ' ' . $config['display_name'];
                Permission::firstOrCreate(
                    [
                        'name' => "{$module}.{$action}",
                        'guard_name' => 'web',
                    ],
                    [
                        'name' => "{$module}.{$action}",
                        'guard_name' => 'web',
                    ]
                );
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
                'students.view', 'fees.view', 'fees.create', 'fees.edit', 'fees.collect',
                'reports.view', 'reports.generate', 'reports.export',
            ];
            $accountantRole->givePermissionTo($accountantPermissions);
        }

        $librarianRole = Role::where('name', 'librarian')->first();
        if ($librarianRole) {
            $librarianPermissions = [
                'students.view', 'library.view', 'library.create', 'library.edit', 'library.delete', 'library.issue',
            ];
            $librarianRole->givePermissionTo($librarianPermissions);
        }
    }
}
