<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\Teacher\CreateRequest;
use App\Http\Requests\Teacher\UpdateRequest;
use App\Services\TeacherService;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function __construct(private TeacherService $teacherService)
    {
    }

    public function createTeacher(CreateRequest $request)
    {
        $data = $this->teacherService->createTeacher($request);
        return ApiResponse::success($data, "Tạo giáo viên thành công", [], 201);
    }

    public function listTeacher()
    {
        $data = $this->teacherService->listTeacher();
        return ApiResponse::success($data);
    }

    public function editTeacher(UpdateRequest $request, $id)
    {
        $data = $this->teacherService->updateTeacher($request, $id);
        return ApiResponse::success($data, "Sửa giáo viên thành công");
    }

    public function getTeacher($id)
    {
        $data = $this->teacherService->getTeacher($id);
        return ApiResponse::success($data);
    }

    public function deleteTeacher($id)
    {
        $this->teacherService->deleteTeacher($id);
        return ApiResponse::success(null, 'Xóa giáo viên thành công');
    }
}
