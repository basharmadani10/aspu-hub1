<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        $rolesAndPermissions = config('roles_permissions.roles_and_permissions');

        DB::transaction(function () use ($rolesAndPermissions) {
            $allPermissions = collect($rolesAndPermissions)->flatten()->unique();
            foreach ($allPermissions as $permission) {
                Permission::findOrCreate($permission);
            }

            foreach ($rolesAndPermissions as $roleName => $permissions) {
                $role = Role::findOrCreate($roleName);
                $role->syncPermissions($permissions);
            }
        });

        $this->command->info('Roles and permissions have been set up successfully.');
    }
}