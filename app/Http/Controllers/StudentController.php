<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\Student\CreateRequest;
use App\Http\Requests\Student\UpdateRequest;
use App\Services\StudentService;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function __construct(private StudentService $studentService)
    {
    }

    public function index(Request $request)
    {
        $data = $this->studentService->listStudent($request->all());
        return ApiResponse::success($data);
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
}
