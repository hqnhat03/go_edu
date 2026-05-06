<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\ClassRoom\CreateRequest;
use App\Http\Requests\ClassRoom\UpdateRequest;
use App\Http\Requests\ClassRoomRequest;
use App\Services\ClassRoomService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClassRoomController extends Controller
{
    private $classRoomService;

    public function __construct(ClassRoomService $classRoomService)
    {
        $this->classRoomService = $classRoomService;
    }

    public function index(Request $request)
    {
        $data = $this->classRoomService->getList($request->all());
        return ApiResponse::success($data, 'Lấy danh sách lớp học thành công');
    }

    public function store(CreateRequest $request)
    {
        $data = $this->classRoomService->create($request->validated());
        return ApiResponse::success($data, 'Tạo lớp học thành công');
    }

    public function show(int $id)
    {
        $data = $this->classRoomService->findById($id);
        return ApiResponse::success($data, 'Lấy thông tin lớp học thành công');
    }

    public function update(UpdateRequest $request, int $id)
    {
        $data = $this->classRoomService->update($request->validated(), $id);
        return ApiResponse::success($data, 'Cập nhật lớp học thành công');
    }

    public function destroy(int $id)
    {
        $data = $this->classRoomService->delete($id);
        return ApiResponse::success($data, 'Xóa lớp học thành công');
    }
    public function assignStudents(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:students,id',
        ]);
        $data = $this->classRoomService->assignStudents($id, $request->student_ids);
        return ApiResponse::success($data, 'Thêm học sinh vào lớp thành công');
    }

    public function removeStudents(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:students,id',
        ]);
        $data = $this->classRoomService->removeStudents($id, $request->student_ids);
        return ApiResponse::success($data, 'Xóa học sinh khỏi lớp thành công');
    }
}
