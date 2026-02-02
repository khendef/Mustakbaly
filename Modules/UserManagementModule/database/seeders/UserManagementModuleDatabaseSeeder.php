<?php

namespace Modules\UserManagementModule\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\UserManagementModule\Database\Seeders\RolesAndPermissions\ManagerRoleSeeder;
use Modules\UserManagementModule\Database\Seeders\RolesAndPermissions\PermissionSeeder;
use Modules\UserManagementModule\Database\Seeders\RolesAndPermissions\SuperAdminRoleSeeder;

class UserManagementModuleDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $this->call([
        PermissionSeeder::class,
        SuperAdminRoleSeeder::class,
        ManagerRoleSeeder::class,
       ]);
    }
}
