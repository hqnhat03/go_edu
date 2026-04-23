<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\Course\CreateRequest;
use App\Http\Requests\Course\UpdateRequest;
use App\Http\Resources\Course\CourseResource;
use App\Models\Course;
use App\Services\CourseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourseController extends Controller
{
    private CourseService $courseService;

    public function __construct(CourseService $courseService)
    {
        $this->courseService = $courseService;
    }

    public function index(Request $request)
    {
        $courses = $this->courseService->getList($request->all());
        return ApiResponse::success($courses, 'Lấy danh sách khóa học thành công');
    }

    public function show($id)
    {
        $course = $this->courseService->findById($id);
        return ApiResponse::success($course, 'Lấy thông tin khóa học thành công');
    }

    public function store(CreateRequest $request): JsonResponse
    {
        $course = $this->courseService->create($request->validated());
        return ApiResponse::success($course, 'Tạo khóa học thành công');

    }

    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $course = $this->courseService->update($request->validated(), $id);
        return ApiResponse::success($course, 'Cập nhật khóa học thành công');
    }

    public function destroy(int $id): JsonResponse
    {
        $course = $this->courseService->delete($id);
        return ApiResponse::success($course, 'Xóa khóa học thành công');
    }
    public function getStudents(Request $request, int $id): JsonResponse
    {
        $students = $this->courseService->getStudents($id, $request->all());
        return ApiResponse::success($students, 'Lấy danh sách học sinh thành công');
    }
}
