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

    /**
     * List registrations for admin
     */
    public function listRegistrations(array $filters)
    {
        $query = CourseRegistration::with('course');

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('email', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('phone', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['course_id'])) {
            $query->where('course_id', $filters['course_id']);
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';

        $allowedSortFields = ['id', 'name', 'email', 'phone', 'status', 'created_at', 'course_id'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->latest();
        }

        $paginator = $query->paginate($filters['per_page'] ?? 15);

        $paginator->getCollection()->transform(function ($item) {
            $item->course_name = $item->course?->name;
            unset($item->course);
            return $item;
        });

        return $paginator;
    }

    /**
     * Get registration detail
     */
    public function getRegistration(int $id)
    {
        $registration = CourseRegistration::with('course')->findOrFail($id);
        $registration->course_name = $registration->course?->name;
        unset($registration->course);
        return $registration;
    }

    /**
     * Update registration status
     */
    public function updateStatus(int $id, string $status)
    {
        $registration = CourseRegistration::with('course')->findOrFail($id);
        $registration->update(['status' => $status]);
        $registration->course_name = $registration->course?->name;
        unset($registration->course);

        return $registration;
    }
}
