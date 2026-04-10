<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\Subject\CreateRequest;
use App\Http\Requests\Subject\UpdateRequest;
use App\Services\SubjectService;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    private $subjectService;

    public function __construct(SubjectService $subjectService)
    {
        $this->subjectService = $subjectService;
    }

    public function listSubject()
    {
        $data = $this->subjectService->listSubject();
        return ApiResponse::success($data, 'Lấy danh sách môn học thành công');
    }

    public function createSubject(CreateRequest $request)
    {
        $data = $this->subjectService->createSubject($request);
        return ApiResponse::success($data, 'Tạo môn học thành công', [], 201);
    }

    public function updateSubject(UpdateRequest $request, $id)
    {
        $data = $this->subjectService->updateSubject($request, $id);
        return ApiResponse::success($data, 'Cập nhật môn học thành công');
    }

    public function deleteSubject($id)
    {
        $this->subjectService->deleteSubject($id);
        return ApiResponse::success(null, 'Xóa môn học thành công');
    }
}
