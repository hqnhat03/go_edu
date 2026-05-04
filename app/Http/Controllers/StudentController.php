<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\Student\CreateRequest;
use App\Http\Requests\Student\UpdateRequest;
use App\Services\CourseRegistrationService;
use App\Services\StudentService;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function __construct(
        private StudentService $studentService,
        private CourseRegistrationService $registrationService
    ) {
    }

    public function index(Request $request)
    {
        $students = $this->studentService->listStudent($request->all());
        return ApiResponse::success($students->items(), 'Lấy danh sách học sinh thành công', [
            'total' => $students->total(),
            'per_page' => $students->perPage(),
            'current_page' => $students->currentPage(),
            'last_page' => $students->lastPage()
        ]);
    }

    public function store(CreateRequest $request)
    {
        $data = $this->studentService->createStudent($request->validated());
        return ApiResponse::success($data, 'Tạo học sinh thành công', [], 201);
    }

    public function update(UpdateRequest $request, $id)
    {
        $data = $this->studentService->updateStudent($request->validated(), $id);
        return ApiResponse::success($data, 'Cập nhật học sinh thành công');
    }

    public function show($id)
    {
        $data = $this->studentService->getStudent($id);
        return ApiResponse::success($data);
    }

    public function destroy($id)
    {
        $data = $this->studentService->deleteStudent($id);
        return ApiResponse::success($data, 'Xóa học sinh thành công');
    }

    public function getAllStudent(Request $request)
    {
        $data = $this->studentService->getAllStudent($request);
        return ApiResponse::success($data, 'Lấy danh sách học sinh thành công');
    }

    public function enrollCourse(Request $request, $id)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
        ], [
            'course_id.required' => 'Vui lòng chọn khóa học',
            'course_id.exists' => 'Khóa học không tồn tại',
        ]);

        $registration = $this->registrationService->enroll((int) $id, (int) $request->course_id);

        return ApiResponse::success($registration, 'Thêm học sinh vào khóa học thành công');
    }
}
