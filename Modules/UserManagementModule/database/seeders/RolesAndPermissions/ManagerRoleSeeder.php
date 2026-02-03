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

            // quiz permissions
            'create-quiz',
            'update-quiz',
            'delete-quiz',
            'list-quiz',
            'show-quiz',

            //question permissions
            'create-question',
            'update-question',
            'delete-question',
            'list-questions',
            'show-question',

            //question option permissions
            'create-option',
            'update-option',
            'delete-option',
            'list-options',
            'show-option',

            //attempt permissions
            'create-attempt',
            'update-attempt',
            'delete-attempt',
            'list-attempts',
            'show-attempt',

            //answer permissions
            'create-answer',
            'update-answer',
            'delete-answer',
            'list-answers',
            'show-answer',

        ];
        $role = Role::firstOrCreate(['name' => 'manager','guard_name'=>'api']);
        $role->syncPermissions($permissions);
    }
}
