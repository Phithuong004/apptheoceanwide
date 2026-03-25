<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Projects
            'projects.view', 'projects.create', 'projects.edit',
            'projects.delete', 'projects.archive',
            // Tasks
            'tasks.view', 'tasks.create', 'tasks.edit',
            'tasks.delete', 'tasks.assign',
            // Sprints
            'sprints.view', 'sprints.manage', 'sprints.start', 'sprints.complete',
            // HR
            'hr.view', 'hr.manage',
            // Finance
            'finance.view', 'finance.manage',
            // Reports
            'reports.view', 'reports.export',
            // Settings
            'settings.manage', 'workspace.manage',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // Super Admin
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdmin->syncPermissions(Permission::all());

        // Admin
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions(Permission::whereNotIn('name', ['settings.manage'])->get());

        // Manager
        $manager = Role::firstOrCreate(['name' => 'manager']);
        $manager->syncPermissions([
            'projects.view', 'projects.create', 'projects.edit',
            'tasks.view', 'tasks.create', 'tasks.edit', 'tasks.assign',
            'sprints.view', 'sprints.manage', 'sprints.start', 'sprints.complete',
            'hr.view', 'reports.view', 'reports.export', 'finance.view',
        ]);

        // Developer / Designer
        $member = Role::firstOrCreate(['name' => 'member']);
        $member->syncPermissions([
            'projects.view', 'tasks.view', 'tasks.create',
            'tasks.edit', 'sprints.view', 'reports.view',
        ]);

        // Guest
        $guest = Role::firstOrCreate(['name' => 'guest']);
        $guest->syncPermissions(['projects.view', 'tasks.view']);
    }
}
