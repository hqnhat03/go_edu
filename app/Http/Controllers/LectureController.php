<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Services\LectureService;
use Illuminate\Http\Request;

class LectureController extends Controller
{
    public function __construct(private LectureService $service) {}

    /** GET /teacher/classes/{classId}/lectures */
    public function index(Request $request, $classId)
    {
        $lectures = $this->service->list((int) $classId, $request->all());
        
        return ApiResponse::success($lectures->getCollection(), 'Lấy danh sách buổi học thành công', [
            'total' => $lectures->total(),
            'per_page' => $lectures->perPage(),
            'current_page' => $lectures->currentPage(),
            'last_page' => $lectures->lastPage(),
        ]);
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
            'status'         => 'nullable|in:pending',
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
            'status'         => 'nullable|in:pending',
        ]);

        $data = $this->service->update($request->all(), (int) $id);
        return ApiResponse::success($data, 'Cập nhật buổi học thành công');
    }

    /** GET /teacher/lectures/{id} */
    public function show($id)
    {
        $data = $this->service->get((int) $id); 
        return ApiResponse::success($data);
    }

    // ─── Admin Methods ──────────────────────────────────────────────────

    /** GET /admin/classes/{classId}/lectures */
    public function indexByAdmin(Request $request, $classId)
    {
        $lectures = $this->service->listByAdmin((int) $classId, $request->all());
        
        return ApiResponse::success($lectures->getCollection(), 'Lấy danh sách buổi học thành công', [
            'total' => $lectures->total(),
            'per_page' => $lectures->perPage(),
            'current_page' => $lectures->currentPage(),
            'last_page' => $lectures->lastPage(),
        ]);
    }

    /** GET /admin/lectures/{id} */
    public function showByAdmin($id)
    {
        $data = $this->service->getByAdmin((int) $id);
        return ApiResponse::success($data);
    }

    /** POST /admin/classes/{classId}/lectures */
    public function storeByAdmin(Request $request, $classId)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'lecture_number' => 'required|integer|min:1',
            'duration_time'  => 'required|integer|min:1',
            'document_url'   => 'nullable|string',
            'video_url'      => 'nullable|string',
            'description'    => 'nullable|string',
            'status'         => 'nullable|in:pending,published,rejected',
        ]);

        $data = $this->service->createByAdmin($request->all(), (int) $classId);
        return ApiResponse::success($data, 'Tạo buổi học thành công', [], 201);
    }

    /** PUT /admin/lectures/{id} */
    public function updateByAdmin(Request $request, $id)
    {
        $request->validate([
            'name'           => 'nullable|string|max:255',
            'lecture_number' => 'nullable|integer|min:1',
            'duration_time'  => 'nullable|integer|min:1',
            'document_url'   => 'nullable|string',
            'video_url'      => 'nullable|string',
            'description'    => 'nullable|string',
            'status'         => 'nullable|in:pending,published,rejected',
        ]);

        $data = $this->service->updateByAdmin($request->all(), (int) $id);
        return ApiResponse::success($data, 'Cập nhật buổi học thành công');
    }

    /** DELETE /admin/lectures/{id} */
    public function destroyByAdmin($id)
    {
        $data = $this->service->deleteByAdmin((int) $id);
        return ApiResponse::success($data, 'Xóa buổi học thành công');
    }

    /** POST /admin/lectures/bulk-status */
    public function bulkStatusByAdmin(Request $request)
    {
        $request->validate([
            'ids'    => 'required|array',
            'ids.*'  => 'integer|exists:lectures,id',
            'status' => 'required|in:pending,published,rejected',
        ]);

        $this->service->bulkUpdateStatus($request->ids, $request->status);
        return ApiResponse::success(null, 'Cập nhật trạng thái thành công');
    }
}
