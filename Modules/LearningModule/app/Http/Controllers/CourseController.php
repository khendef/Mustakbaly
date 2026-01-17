<?php

namespace Modules\LearningModule\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\CachesQueries;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\LearningModule\Http\Requests\Course\AssignInstructorRequest;
use Modules\LearningModule\Http\Requests\Course\ChangeStatusCourseRequest;
use Modules\LearningModule\Http\Requests\Course\RemoveInstructorRequest;
use Modules\LearningModule\Http\Requests\Course\SetPrimaryInstructorRequest;
use Modules\LearningModule\Http\Requests\Course\StoreCourseRequest;
use Modules\LearningModule\Http\Requests\Course\UpdateCourseRequest;
use Modules\LearningModule\Http\Resources\CourseResource;
use Modules\LearningModule\Models\Course;
use Modules\LearningModule\Services\CourseInstructorService;
use Modules\LearningModule\Services\CourseService;

/**
 * Controller for managing courses.
 * Handles HTTP requests and delegates business logic to CourseService.
 * Follows SOLID principles: Single Responsibility, Dependency Inversion.
 */
class CourseController extends Controller
{
    use CachesQueries;
    /**
     * Course service instance.
     *
     * @var CourseService
     */
    protected CourseService $courseService;

    /**
     * Course instructor service instance.
     *
     * @var CourseInstructorService
     */
    protected CourseInstructorService $courseInstructorService;

    /**
     * Create a new controller instance.
     *
     * @param CourseService $courseService
     * @param CourseInstructorService $courseInstructorService
     */
    public function __construct(CourseService $courseService, CourseInstructorService $courseInstructorService)
    {
        $this->courseService = $courseService;
        $this->courseInstructorService = $courseInstructorService;
    }

    /**
     * Display a listing of courses.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Generate cache key based on query parameters
            $cacheKey = 'courses.index.' . md5($request->getQueryString());

            $courses = $this->remember($cacheKey, 900, function () use ($request) {
                $query = Course::query();
                return $query
                    ->filterByRequest($request)
                    ->withRelations()
                    ->ordered()
                    ->paginateFromRequest($request)
                    ->through(fn($course) => new CourseResource($course));
            }, ['courses']);

            return $this->successResponse($courses, 'Courses retrieved successfully.');
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve courses.', null, null, $e);
        }
    }

    /**
     * Store a newly created course.
     *
     * @param StoreCourseRequest $request
     * @return JsonResponse
     */
    public function store(StoreCourseRequest $request): JsonResponse
    {
        try {
            $course = $this->courseService->create($request->validated());
            $course->load(['courseType', 'creator']);

            return $this->createdResponse(
                new CourseResource($course),
                'Course created successfully.'
            );
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to create course.', null, null, $e);
        }
    }

    /**
     * Display the specified course.
     *
     * @param Course $course
     * @return JsonResponse
     */
    public function show(Course $course): JsonResponse
    {
        try {
            $cacheKey = "course.{$course->course_id}";

            $courseData = $this->remember($cacheKey, 1800, function () use ($course) {
                $course->load(['courseType', 'instructors', 'creator', 'units']);
                return new CourseResource($course);
            }, ['courses', "course.{$course->course_id}"]);

            return $this->successResponse(
                $courseData,
                'Course retrieved successfully.'
            );
        } catch (Exception $e) {
            return $this->serverErrorResponse('Course not found.', null, null, $e);
        }
    }

    /**
     * Update the specified course.
     *
     * @param UpdateCourseRequest $request
     * @param Course $course
     * @return JsonResponse
     */
    public function update(UpdateCourseRequest $request, Course $course): JsonResponse
    {
        try {
            $updatedCourse = $this->courseService->update($course, $request->validated());
            $updatedCourse->load(['courseType', 'creator']);

            return $this->successResponse(
                new CourseResource($updatedCourse),
                'Course updated successfully.'
            );
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to update course.', null, null, $e);
        }
    }

    /**
     * Remove the specified course.
     *
     * @param Course $course
     * @return JsonResponse
     */
    public function destroy(Course $course): JsonResponse
    {
        try {
            $this->courseService->delete($course);

            return $this->successResponse(null, 'Course deleted successfully.');
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to delete course.', null, null, $e);
        }
    }

    /**
     * Publish the specified course.
     *
     * @param Course $course
     * @return JsonResponse
     */
    public function publish(Course $course): JsonResponse
    {
        try {
            $publishedCourse = $this->courseService->publish($course);

            return $this->successResponse(
                new CourseResource($publishedCourse),
                'Course published successfully.'
            );
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to publish course.', null, null, $e);
        }
    }

    /**
     * Unpublish the specified course.
     *
     * @param Course $course
     * @return JsonResponse
     */
    public function unpublish(Course $course): JsonResponse
    {
        try {
            $unpublishedCourse = $this->courseService->unpublish($course);

            return $this->successResponse(
                new CourseResource($unpublishedCourse),
                'Course unpublished successfully.'
            );
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to unpublish course.', null, null, $e);
        }
    }

    /**
     * Change the status of the specified course.
     *
     * @param ChangeStatusCourseRequest $request
     * @param Course $course
     * @return JsonResponse
     */
    public function changeStatus(ChangeStatusCourseRequest $request, Course $course): JsonResponse
    {
        try {
            $updatedCourse = $this->courseService->changeStatus($course, $request->validated()['status']);

            return $this->successResponse(
                new CourseResource($updatedCourse),
                'Course status changed successfully.'
            );
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to change course status.', null, null, $e);
        }
    }

    /**
     * Get course duration.
     *
     * @param Course $course
     * @return JsonResponse
     */
    public function getDuration(Course $course): JsonResponse
    {
        try {
            $duration = $this->courseService->getDuration($course);

            return $this->successResponse(
                [
                    'course_id' => $course->course_id,
                    'duration_hours' => $duration,
                    'actual_duration_hours' => $course->actual_duration_hours,
                ],
                'Course duration retrieved successfully.'
            );
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve course duration.', null, null, $e);
        }
    }

    /**
     * Check if course can be published.
     *
     * @param Course $course
     * @return JsonResponse
     */
    public function checkPublishability(Course $course): JsonResponse
    {
        try {
            $isPublishable = $this->courseService->isPublishable($course);
            $reasons = $isPublishable ? [] : $this->courseService->getUnpublishabilityReasons($course);

            return $this->successResponse(
                [
                    'is_publishable' => $isPublishable,
                    'reasons' => $reasons,
                ],
                'Publishability check completed.'
            );
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to check publishability.', null, null, $e);
        }
    }

    /**
     * Get courses available for enrollment.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function enrollable(Request $request): JsonResponse
    {
        try {
            // Generate cache key based on query parameters
            $cacheKey = 'courses.enrollable.' . md5($request->getQueryString());

            $courses = $this->remember($cacheKey, 900, function () use ($request) {
                return Course::query()
                    ->enrollable()
                    ->filterByRequest($request)
                    ->withRelations()
                    ->ordered()
                    ->paginateFromRequest($request)
                    ->through(fn($course) => new CourseResource($course));
            }, ['courses', 'courses.enrollable']);

            return $this->successResponse($courses, 'Enrollable courses retrieved successfully.');
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve enrollable courses.', null, null, $e);
        }
    }

    /**
     * Get courses by instructor.
     *
     * @param Request $request
     * @param int $instructorId
     * @return JsonResponse
     */
    public function byInstructor(Request $request, int $instructorId): JsonResponse
    {
        try {
            $courses = Course::query()
                ->byInstructor($instructorId)
                ->filterByRequest($request)
                ->withRelations()
                ->ordered()
                ->paginateFromRequest($request)
                ->through(fn($course) => new CourseResource($course));

            return $this->successResponse($courses, 'Instructor courses retrieved successfully.');
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve instructor courses.', null, null, $e);
        }
    }

    /**
     * Assign an instructor to a course.
     *
     * @param AssignInstructorRequest $request
     * @param Course $course
     * @return JsonResponse
     */
    public function assignInstructor(AssignInstructorRequest $request, Course $course): JsonResponse
    {
        try {
            $assignment = $this->courseInstructorService->assign(
                $course,
                $request->input('instructor_id'),
                $request->input('is_primary', false)
            );

            $course->load('instructors');

            return $this->createdResponse(
                new CourseResource($course),
                'Instructor assigned to course successfully.'
            );
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to assign instructor to course.', null, null, $e);
        }
    }

    /**
     * Remove an instructor from a course.
     *
     * @param RemoveInstructorRequest $request
     * @param Course $course
     * @return JsonResponse
     */
    public function removeInstructor(RemoveInstructorRequest $request, Course $course): JsonResponse
    {
        try {
            $this->courseInstructorService->remove($course, $request->input('instructor_id'));

            $course->load('instructors');

            return $this->successResponse(
                new CourseResource($course),
                'Instructor removed from course successfully.'
            );
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to remove instructor from course.', null, null, $e);
        }
    }

    /**
     * Set an instructor as primary for the course.
     *
     * @param SetPrimaryInstructorRequest $request
     * @param Course $course
     * @return JsonResponse
     */
    public function setPrimaryInstructor(SetPrimaryInstructorRequest $request, Course $course): JsonResponse
    {
        try {
            $this->courseInstructorService->setPrimary($course, $request->input('instructor_id'));

            $course->load('instructors');

            return $this->successResponse(
                new CourseResource($course),
                'Instructor set as primary successfully.'
            );
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to set primary instructor.', null, null, $e);
        }
    }

    /**
     * Unset primary flag for an instructor.
     *
     * @param RemoveInstructorRequest $request
     * @param Course $course
     * @return JsonResponse
     */
    public function unsetPrimaryInstructor(RemoveInstructorRequest $request, Course $course): JsonResponse
    {
        try {
            $this->courseInstructorService->unsetPrimary($course, $request->input('instructor_id'));

            $course->load('instructors');

            return $this->successResponse(
                new CourseResource($course),
                'Primary instructor flag removed successfully.'
            );
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to unset primary instructor.', null, null, $e);
        }
    }

    /**
     * Get all instructors for a specific course.
     *
     * @param Request $request
     * @param Course $course
     * @return JsonResponse
     */
    public function getInstructors(Request $request, Course $course): JsonResponse
    {
        try {
            $instructors = $this->courseInstructorService->getInstructors($course);

            $instructorsData = $instructors->map(function ($courseInstructor) {
                return [
                    'id' => $courseInstructor->instructor_id,
                    'name' => $courseInstructor->instructor->name ?? null,
                    'email' => $courseInstructor->instructor->email ?? null,
                    'is_primary' => $courseInstructor->is_primary,
                    'assigned_at' => $courseInstructor->assigned_at?->toDateTimeString(),
                    'assigned_by' => $courseInstructor->assignedBy ? [
                        'id' => $courseInstructor->assignedBy->user_id,
                        'name' => $courseInstructor->assignedBy->name,
                    ] : null,
                ];
            });

            return $this->successResponse(
                [
                    'course_id' => $course->course_id,
                    'course_title' => $course->title,
                    'instructors' => $instructorsData,
                    'instructors_count' => $instructorsData->count(),
                ],
                'Course instructors retrieved successfully.'
            );
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve course instructors.', null, null, $e);
        }
    }
}
