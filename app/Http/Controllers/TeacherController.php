<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\Teacher\CreateRequest;
use App\Http\Requests\Teacher\UpdateRequest;
use App\Services\TeacherService;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    private TeacherService $teacherService;
    public function __construct(TeacherService $teacherService)
    {
        $this->teacherService = $teacherService;
    }

    public function index(Request $request)
    {
        $data = $this->teacherService->listTeacher($request->all());
        return ApiResponse::success($data);
    }

    public function store(CreateRequest $request)
    {
        $data = $this->teacherService->createTeacher($request->validated());
        return ApiResponse::success($data, "Tạo giáo viên thành công", [], 201);
    }

    public function update(UpdateRequest $request, $id)
    {
        $data = $this->teacherService->updateTeacher($request->validated(), $id);
        return ApiResponse::success($data, "Cập nhật giáo viên thành công");
    }

    public function show($id)
    {
        $data = $this->teacherService->getTeacher($id);
        return ApiResponse::success($data);
    }

    public function destroy($id)
    {
        $data = $this->teacherService->deleteTeacher($id);
        return ApiResponse::success($data, 'Xóa giáo viên thành công');
    }
}
