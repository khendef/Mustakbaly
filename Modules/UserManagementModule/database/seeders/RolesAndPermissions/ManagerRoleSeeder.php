<?php

namespace Modules\UserManagementModule\Database\Seeders\RolesAndPermissions;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class ManagerRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [

            //students permissions 
            'create-student',
            'update-student',
            'delete-student',
            'list-students',
            'show-student',

            //instructor permissions
            'create-instructor',
            'update-instructor',
            'delete-instructor',
            'list-instructors',
            'show-instructor',

            //auditor permissions
            'create-auditor',
            'update-auditor',
            'delete-auditor',
            'list-auditors',
            'show-auditor',

            //program permissions
            'create-program',
            'update-program',
            'delete-program',
            'list-programs',
            'show-program',

            //course permissions
            'create-course',
            'update-course',
            'delete-course',
            'list-courses',
            'show-course',

            //unit permissions
            'create-unit',
            'update-unit',
            'delete-unit',
            'list-units',
            'show-unit',

            //lesson permissions
            'create-lesson',
            'update-lesson',
            'delete-lesson',
            'list-lessons',
            'show-lesson',

        ];
        $role = Role::firstOrCreate(['name' => 'manager','guard_name'=>'api']);
        $role->syncPermissions($permissions);
    }
}
