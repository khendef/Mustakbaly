<?php

namespace Modules\UserManagementModule\Database\Seeders\RolesAndPermissions;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class StudentRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [

            'list-courses',
            'show-course',

            //unit permissions
            'list-units',
            'show-unit',

            //lesson permissions
            'list-lessons',
            'show-lesson',

            // quiz permissions
            'list-quiz',
            'show-quiz',

            //question permissions
            'list-questions',
            'show-question',

            //question option permissions
            'list-options',
            'show-option',

            //attempt permissions
            'create-attempt',
            'show-attempt',

            //answer permissions
            'create-answer',
            'update-answer',
            'delete-answer',
            'list-answers',
            'show-answer',
        ];

        $role = Role::firstOrCreate(['name' => 'student','guard_name'=>'api']);
        $role->syncPermissions($permissions);
    }
}
