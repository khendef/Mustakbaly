<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Modules\LearningModule\Models\Course;

class UniqueCourseTypeCourseNameRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {

        $course = Course::where('title->en', $value)->first();
        if ($course) {
            $fail('The course name has already been taken.');
        }
    }
}
