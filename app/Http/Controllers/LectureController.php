<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Services\LectureService;
use Illuminate\Http\Request;

class LectureController extends Controller
{
    public function __construct(private LectureService $service) {}

    /** GET /teacher/classes/{classId}/lectures */
    public function index($classId)
    {
        $data = $this->service->list((int) $classId);
        return ApiResponse::success($data);
    }

    /** POST /teacher/classes/{classId}/lectures */
    public function store(Request $request, $classId)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'lecture_number' => 'required|integer|min:1',
            'duration_time'  => 'required|integer|min:1',
            'document_url'   => 'nullable|string',
            'video_url'      => 'nullable|string',
            'description'    => 'nullable|string',
            'status'         => 'nullable|in:draft,published',
        ]);

        $data = $this->service->create($request->all(), (int) $classId);
        return ApiResponse::success($data, 'Tạo buổi học thành công', [], 201);
    }

    /** POST /teacher/classes/{classId}/lectures/import */
    public function import(Request $request, $classId)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
        ]);

        $data = $this->service->import($request->file('file'), (int) $classId);
        return ApiResponse::success($data, 'Import bài giảng thành công');
    }

    /** PUT /teacher/lectures/{id} */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name'           => 'nullable|string|max:255',
            'lecture_number' => 'nullable|integer|min:1',
            'duration_time'  => 'nullable|integer|min:1',
            'document_url'   => 'nullable|string',
            'video_url'      => 'nullable|string',
            'description'    => 'nullable|string',
            'status'         => 'nullable|in:draft,published',
        ]);

        $data = $this->service->update($request->all(), (int) $id);
        return ApiResponse::success($data, 'Cập nhật buổi học thành công');
    }

    /** DELETE /teacher/lectures/{id} */
    public function destroy($id)
    {
        $data = $this->service->delete((int) $id);
        return ApiResponse::success($data, 'Xóa buổi học thành công');
    }
}
