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
        $courses = $this->courseService->listCourse($request->all());
        return ApiResponse::success($courses, 'Lấy danh sách khóa học thành công');
    }

    public function show(Course $course): JsonResponse
    {
        $course->load('level', 'classRooms.schedules');

        return response()->json([
            'success' => true,
            'data' => new CourseResource($course),
        ]);
    }

    public function store(CreateRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $course = $this->courseService->create($request->validated());
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tạo khóa học thành công',
                'data' => new CourseResource($course),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Tạo khóa học thất bại',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(UpdateRequest $request, Course $course): JsonResponse
    {
        DB::beginTransaction();
        try {
            $course = $this->courseService->update($course, $request->validated());
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật khóa học thành công',
                'data' => new CourseResource($course),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Cập nhật khóa học thất bại',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Course $course): JsonResponse
    {
        DB::beginTransaction();
        try {
            $this->courseService->delete($course);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Xóa khóa học thành công',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Xóa khóa học thất bại',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
