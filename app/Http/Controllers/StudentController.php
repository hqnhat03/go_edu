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

    public function createStudent(CreateRequest $request)
    {
        $data = $this->studentService->createStudent($request);
        return ApiResponse::success($data, "Tạo học sinh thành công", [], 201);
    }

    public function listStudent()
    {
        $data = $this->studentService->listStudent();
        return ApiResponse::success($data);
    }

    public function editStudent(UpdateRequest $request, $id)
    {
        $data = $this->studentService->updateStudent($request, $id);
        return ApiResponse::success($data, "Sửa học sinh thành công");
    }

    public function getStudent($id)
    {
        $data = $this->studentService->getStudent($id);
        return ApiResponse::success($data);
    }

    public function deleteStudent($id)
    {
        $this->studentService->deleteStudent($id);
        return ApiResponse::success(null, 'Xóa học sinh thành công');
    }
}
