<?php

namespace App\Services;

use App\Exceptions\UserException;
use App\Models\Course;
use App\Models\CourseRegistration;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CourseRegistrationService
{
    /**
     * Register a student for a course (invoked by client/student)
     */
    public function register(int $courseId, array $data)
    {
        $course = Course::where('id', $courseId)->where('status', 'published')->firstOrFail();

        return CourseRegistration::create([
            'course_id' => $course->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'status' => 'pending',
        ]);
    }

    /**
     * Enroll an existing student for a course (invoked by admin)
     */
    public function enroll(int $studentId, int $courseId)
    {
        $student = Student::findOrFail($studentId);
        $course = Course::findOrFail($courseId);

        // Check if student is already enrolled
        $isEnrolled = $student->courses()->where('course_id', $courseId)->exists();

        if ($isEnrolled) {
            throw new UserException("Học sinh này đã tham gia khóa học này rồi");
        }

        return DB::transaction(function () use ($student, $course) {
            // Add to many-to-many relationship
            $student->courses()->attach($course->id);
        });
    }
}
