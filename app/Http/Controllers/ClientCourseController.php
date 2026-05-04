<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Services\CourseService;
use Illuminate\Http\Request;

class ClientCourseController extends Controller
{
    private CourseService $courseService;
    private \App\Services\CourseRegistrationService $registrationService;

    public function __construct(
        CourseService $courseService,
        \App\Services\CourseRegistrationService $registrationService
    ) {
        $this->courseService = $courseService;
        $this->registrationService = $registrationService;
    }

    /**
     * Lấy danh sách khóa học (dành cho học sinh/khách, không yêu cầu đăng nhập)
     */
    public function index(Request $request)
    {
        $courses = $this->courseService->getPublicList($request->all());

        return ApiResponse::success($courses->items(), 'Lấy danh sách khóa học thành công', [
            'total' => $courses->total(),
            'per_page' => $courses->perPage(),
            'current_page' => $courses->currentPage(),
            'last_page' => $courses->lastPage()
        ]);
    }

    /**
     * Lấy thông tin chi tiết một khóa học theo slug
     */
    public function show($slug)
    {
        $course = $this->courseService->findPublicBySlug($slug);

        return ApiResponse::success($course, 'Lấy chi tiết khóa học thành công');
    }

    /**
     * Đăng ký khóa học cho học sinh
     */
    public function register(Request $request, $courseId)
    {
        $payload = $request->all();

        $registration = $this->registrationService->register(
            (int) $courseId,
            $payload
        );

        return ApiResponse::success($registration, 'Đăng ký khóa học thành công. Vui lòng chờ quản trị viên xác nhận.');
    }
}
