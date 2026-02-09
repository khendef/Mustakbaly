<?php

namespace Modules\UserManagementModule\Database\Seeders\RolesAndPermissions;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            //role permissions
            'create-roles',
            'update-roles',
            'delete-roles',
            'list-roles',
            'show-roles',
            //users permissions (all users accounts, managers accounts)
            'create-user',
            'update-user',
            'delete-user',
            'list-users',
            'show-user',
            'list-permissions',

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

            //donor permissions
            'create-donor',
            'update-donor',
            'delete-donor',
            'list-donors',
            'show-donor',

            //organization permissions
            'create-organization',
            'update-organization',
            'delete-organization',
            'list-organizations',
            'show-organization',

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
            'publish-course',
            'unpublish-course',
            'change-course-status',
            'assign-instructor',
            'remove-instructor',
            'set-primary-instructor',

            //course categories permissions
            'create-category',
            'update-category',
            'delete-category',
            'list-categories',
            'show-category',

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

            // enrollment permissions
            'create-enrollment',
            'update-enrollment',
            'delete-enrollment',
            'list-enrollments',
            'show-enrollment',
            'change-enrollment-status',
            'override-enrollment-final-grade',

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

            //certificate permissions
            'create-certificate',
            'update-certificate',
            'delete-certificate',
            'list-certificates',
            'show-certificate',

        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'api'],
                ['name' => $permission, 'guard_name' => 'api']
            );
        }
    }
}
