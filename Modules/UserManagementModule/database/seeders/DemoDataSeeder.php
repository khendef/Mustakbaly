<?php

namespace Modules\UserManagementModule\Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\LearningModule\Models\Course;
use Modules\LearningModule\Models\CourseInstructor;
use Modules\LearningModule\Models\CourseType;
use Modules\LearningModule\Models\Unit;
use Modules\LearningModule\Services\CourseTypeService;
use Modules\OrganizationsModule\Models\Organization;
use Modules\OrganizationsModule\Models\Program;
use Modules\UserManagementModule\Models\Instructor;
use Modules\UserManagementModule\Models\Student;
use Modules\UserManagementModule\Models\User;
use Modules\UserManagementModule\Models\Scopes\OrganizationScope;
use Spatie\Permission\Models\Role;

/**
 * Seeds demo data for development and testing.
 *
 * 1. Ensures superadmin exists (baraa@example.com / P@ssw0rd).
 * 2. Creates two instructors (one designated as primary for course assignment).
 * 3. Creates two students.
 * 4. Creates one course type (inactive), then activates it.
 * 5. Creates one organization.
 * 6. Creates one program linked to that organization.
 * 7. Creates one draft course linked to course type and program.
 * 8. Creates one unit for the course.
 * 9. Assigns the primary instructor to the course.
 */
class DemoDataSeeder extends Seeder
{
    private const SUPERADMIN_EMAIL = 'baraa@example.com';
    private const SUPERADMIN_PASSWORD = 'P@ssw0rd';

    public function run(): void
    {
        $this->command->info('Seeding demo data...');

        $superAdmin = $this->ensureSuperAdmin();
        $instructors = $this->createInstructors();
        $this->createStudents();
        $courseType = $this->createAndActivateCourseType();
        $organization = $this->createOrganization();
        $program = $this->createProgram($organization);
        $course = $this->createCourse($superAdmin, $courseType, $program);
        $this->createUnit($course);
        $this->assignPrimaryInstructorToCourse($course, $instructors['primary'], $superAdmin);

        $this->command->info('Demo data seeded successfully.');
    }

    /**
     * Ensure superadmin user exists and has the super-admin role.
     */
    private function ensureSuperAdmin(): User
    {
        $superAdminRole = Role::firstOrCreate(
            ['name' => 'super-admin', 'guard_name' => 'api'],
            ['name' => 'super-admin', 'guard_name' => 'api']
        );

        $user = User::firstOrCreate(
            ['email' => self::SUPERADMIN_EMAIL],
            [
                'name' => 'Baraa Super Admin',
                'email' => self::SUPERADMIN_EMAIL,
                'password' => self::SUPERADMIN_PASSWORD,
                'phone' => '+963991554887',
                'date_of_birth' => '1990-01-01',
                'gender' => 'male',
            ]
        );

        if (!$user->hasRole($superAdminRole)) {
            $user->assignRole($superAdminRole);
        }

        $this->command->info("Superadmin ready: {$user->email}");

        return $user;
    }

    /**
     * Create two instructors; first one is the "primary" for later course assignment.
     *
     * @return array{primary: User, secondary: User}
     */
    private function createInstructors(): array
    {
        $instructorRole = Role::firstOrCreate(
            ['name' => 'instructor', 'guard_name' => 'api'],
            ['name' => 'instructor', 'guard_name' => 'api']
        );

        $primaryUser = User::firstOrCreate(
            ['email' => 'instructor.primary@example.com'],
            [
                'name' => 'Primary Instructor',
                'email' => 'instructor.primary@example.com',
                'password' => 'P@ssw0rd',
                'phone' => '+963111000001',
                'date_of_birth' => '1990-05-15',
                'gender' => 'male',
            ]
        );
        $primaryUser->assignRole($instructorRole);
        Instructor::firstOrCreate(
            ['user_id' => $primaryUser->id],
            [
                'user_id' => $primaryUser->id,
                'specialization' => 'Software Engineering',
                'bio' => 'Primary instructor for demo courses.',
                'years_of_experience' => 5,
            ]
        );

        $secondaryUser = User::firstOrCreate(
            ['email' => 'instructor.secondary@example.com'],
            [
                'name' => 'Secondary Instructor',
                'email' => 'instructor.secondary@example.com',
                'password' => 'P@ssw0rd',
                'phone' => '+963111000002',
                'date_of_birth' => '1992-08-20',
                'gender' => 'female',
            ]
        );
        $secondaryUser->assignRole($instructorRole);
        Instructor::firstOrCreate(
            ['user_id' => $secondaryUser->id],
            [
                'user_id' => $secondaryUser->id,
                'specialization' => 'Data Science',
                'bio' => 'Secondary instructor for demo.',
                'years_of_experience' => 3,
            ]
        );

        $this->command->info('Created two instructors (one primary, one secondary).');

        return [
            'primary' => $primaryUser,
            'secondary' => $secondaryUser,
        ];
    }

    /**
     * Create two students.
     */
    private function createStudents(): void
    {
        $studentRole = Role::firstOrCreate(
            ['name' => 'student', 'guard_name' => 'api'],
            ['name' => 'student', 'guard_name' => 'api']
        );

        $students = [
            ['email' => 'student1@example.com', 'name' => 'Demo Student One', 'phone' => '+963999000001'],
            ['email' => 'student2@example.com', 'name' => 'Demo Student Two', 'phone' => '+963999000002'],
        ];

        foreach ($students as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => 'P@ssw0rd',
                    'phone' => $data['phone'],
                    'date_of_birth' => '1995-01-01',
                    'gender' => 'male',
                ]
            );
            $user->assignRole($studentRole);
            // DB column is education_level (migration); Student model fillable uses educational_level
            Model::unguard();
            try {
                Student::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'user_id' => $user->id,
                        'education_level' => \Modules\UserManagementModule\Enums\EducationalLevel::COLLAGE->value,
                        'country' => 'Syria',
                        'bio' => null,
                        'specialization' => 'Computer Science',
                        'joined_at' => now(),
                    ]
                );
            } finally {
                Model::reguard();
            }
        }

        $this->command->info('Created two students.');
    }

    /**
     * Create one course type as inactive, then activate it (simulating a second request).
     */
    private function createAndActivateCourseType(): CourseType
    {
        $courseType = CourseType::firstOrCreate(
            ['slug' => 'demo-course-type'],
            [
                'name' => ['en' => 'Demo Course Type', 'ar' => 'نوع الدورة التجريبية'],
                'slug' => 'demo-course-type',
                'description' => ['en' => 'For demo and testing.', 'ar' => 'للتجربة والعرض.'],
                'is_active' => false,
                'target_audience' => 'Developers',
            ]
        );

        if (!$courseType->is_active) {
            $service = app(CourseTypeService::class);
            $service->activate($courseType);
            $courseType->refresh();
            $this->command->info('Course type created as inactive, then activated.');
        } else {
            $this->command->info('Course type already exists and is active.');
        }

        return $courseType;
    }

    /**
     * Create one organization.
     */
    private function createOrganization(): Organization
    {
        $organization = Organization::firstOrCreate(
            ['slug' => 'demo-organization'],
            [
                'name' => ['en' => 'Demo Organization', 'ar' => 'المنظمة التجريبية'],
                'slug' => 'demo-organization',
                'email' => 'contact@demo-org.example.com',
                'phone' => '+963111222333',
                'address' => 'Damascus, Syria',
                'description' => ['en' => 'Demo organization for testing.', 'ar' => 'منظمة تجريبية للاختبار.'],
            ]
        );

        $this->command->info('Created one organization.');

        return $organization;
    }

    /**
     * Create one program linked to the given organization.
     * Program model does not have organization_id in fillable, so we set it explicitly.
     */
    private function createProgram(Organization $organization): Program
    {
        $program = Program::withoutGlobalScope(OrganizationScope::class)
            ->where('organization_id', $organization->id)
            ->where('title', 'Demo Program')
            ->first();

        if ($program) {
            $this->command->info('Created one program linked to organization.');
            return $program;
        }

        $program = new Program();
        $program->organization_id = $organization->id;
        $program->title = 'Demo Program';
        $program->description = 'Demo program for testing courses.';
        $program->objectives = 'Learn and test.';
        $program->status = 'in_progress';
        $program->required_budget = 10000.00;
        $program->total_funded_amount = 0;
        $program->save();

        $this->command->info('Created one program linked to organization.');

        return $program;
    }

    /**
     * Create one draft course linked to course type and program.
     */
    private function createCourse(User $createdBy, CourseType $courseType, Program $program): Course
    {
        $course = Course::firstOrCreate(
            ['slug' => 'demo-draft-course'],
            [
                'created_by' => $createdBy->id,
                'course_type_id' => $courseType->course_type_id,
                'program_id' => $program->id,
                'title' => ['en' => 'Demo Draft Course', 'ar' => 'دورة تجريبية مسودة'],
                'slug' => 'demo-draft-course',
                'description' => ['en' => 'A draft course for demo.', 'ar' => 'دورة مسودة للتجربة.'],
                'objectives' => null,
                'prerequisites' => null,
                'actual_duration_hours' => 10,
                'allocated_budget' => 0,
                'required_budget' => 0,
                'language' => 'en',
                'status' => 'draft',
                'min_score_to_pass' => 60,
                'is_offline_available' => false,
                'course_delivery_type' => 'self_paced',
                'difficulty_level' => 'beginner',
                'average_rating' => 0,
                'total_ratings' => 0,
                'published_at' => null,
            ]
        );

        $this->command->info('Created one draft course linked to course type and program.');

        return $course;
    }

    /**
     * Create one unit for the course.
     */
    private function createUnit(Course $course): void
    {
        Unit::firstOrCreate(
            [
                'course_id' => $course->course_id,
                'unit_order' => 1,
            ],
            [
                'course_id' => $course->course_id,
                'unit_order' => 1,
                'title' => ['en' => 'Introduction Unit', 'ar' => 'وحدة المقدمة'],
                'description' => ['en' => 'First unit of the demo course.', 'ar' => 'الوحدة الأولى من الدورة.'],
                'actual_duration_minutes' => 60,
            ]
        );

        $this->command->info('Created one unit for the course.');
    }

    /**
     * Assign the primary instructor to the course with is_primary = true.
     */
    private function assignPrimaryInstructorToCourse(Course $course, User $instructor, User $assignedBy): void
    {
        DB::table('course_instructor')->updateOrInsert(
            [
                'course_id' => $course->course_id,
                'instructor_id' => $instructor->id,
            ],
            [
                'course_id' => $course->course_id,
                'instructor_id' => $instructor->id,
                'is_primary' => true,
                'assigned_at' => now(),
                'assigned_by' => $assignedBy->id,
            ]
        );

        $this->command->info('Assigned primary instructor to course.');
    }
}
