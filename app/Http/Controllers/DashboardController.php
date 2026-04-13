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
    public function index()
    {
        $totalTeacher = Teacher::count();
        $totalStudent = Student::count();
        $totalCourse = Course::count();
        $totalNewStudent = Student::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        return ApiResponse::success([
            'total_teacher' => $totalTeacher,
            'total_student' => $totalStudent,
            'total_course' => $totalCourse,
            'total_new_student' => $totalNewStudent,
        ], 'Lấy thông tin dashboard thành công');
    }
}
