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

    public function index(Request $request)
    {
        $data = $this->subjectService->listSubject($request->all());
        return ApiResponse::success($data, 'Lấy danh sách môn học thành công');
    }

    public function store(CreateRequest $request)
    {
        $data = $this->subjectService->createSubject($request->validated());
        return ApiResponse::success($data, 'Tạo môn học thành công', [], 201);
    }

    public function update(UpdateRequest $request, $id)
    {
        $data = $this->subjectService->updateSubject($request->validated(), $id);
        return ApiResponse::success($data, 'Cập nhật môn học thành công');
    }

    public function destroy($id)
    {
        $data = $this->subjectService->deleteSubject($id);
        return ApiResponse::success($data, 'Xóa môn học thành công');
    }
}
