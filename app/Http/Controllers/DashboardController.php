<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Course;
use App\Models\Student;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private \App\Services\ActivityLogService $activityLogService
    ) {}

    public function index()
    {
        $totalTeacher = Teacher::count();
        $totalStudent = Student::count();
        $totalCourse = Course::count();
        $totalNewStudent = Student::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        $studentGrowth = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthName = $date->translatedFormat('F');
            $month = $date->month;
            $year = $date->year;

            $count = Student::whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->count();

            $studentGrowth[] = [
                'label' => $monthName,
                'count' => $count
            ];
        }

        $recentActivities = $this->activityLogService->getRecentActivities(5);

        return ApiResponse::success([
            'total_teacher' => $totalTeacher,
            'total_student' => $totalStudent,
            'total_course' => $totalCourse,
            'total_new_student' => $totalNewStudent,
            'student_growth' => $studentGrowth,
            'recent_activities' => $recentActivities,
        ], 'Lấy thông tin dashboard thành công');
    }
}
