<?php

namespace Modules\LearningModule\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    protected string $name = 'LearningModule';

    /**
     * Called before routes are registered.
     *
     * Register any model bindings or pattern based filters.
     */
    public function boot(): void
    {
        parent::boot();

        // Configure route model binding for models with custom primary keys
        Route::bind('course', function ($value) {
            return \Modules\LearningModule\Models\Course::where('course_id', $value)->firstOrFail();
        });

        Route::bind('unit', function ($value) {
            return \Modules\LearningModule\Models\Unit::where('unit_id', $value)->firstOrFail();
        });

        Route::bind('lesson', function ($value) {
            return \Modules\LearningModule\Models\Lesson::where('lesson_id', $value)->firstOrFail();
        });

        Route::bind('enrollment', function ($value) {
            return \Modules\LearningModule\Models\Enrollment::where('enrollment_id', $value)->firstOrFail();
        });

        Route::bind('courseType', function ($value) {
            return \Modules\LearningModule\Models\CourseType::where('course_type_id', $value)->firstOrFail();
        });
    }

    /**
     * Define the routes for the application.
     */
    public function map(): void
    {
        $this->mapApiRoutes();
        $this->mapWebRoutes();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     */
    protected function mapWebRoutes(): void
    {
        Route::middleware('web')->group(module_path($this->name, '/routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     */
    protected function mapApiRoutes(): void
    {
        Route::middleware('api')->prefix('api')->name('api.')->group(module_path($this->name, '/routes/api.php'));
    }
}
