<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Services\AnnouncementService;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function __construct(private AnnouncementService $service) {}

    /** GET /teacher/classes/{classId}/announcements */
    public function index($classId)
    {
        $data = $this->service->list((int) $classId);
        return ApiResponse::success($data);
    }

    /** POST /teacher/classes/{classId}/announcements */
    public function store(Request $request, $classId)
    {
        $request->validate([
            'title'     => 'required|string|max:255',
            'content'   => 'required|string',
            'is_pinned' => 'nullable|boolean',
        ]);

        $data = $this->service->create($request->all(), (int) $classId);
        return ApiResponse::success($data, 'Đăng thông báo thành công', [], 201);
    }

    /** PUT /teacher/announcements/{id} */
    public function update(Request $request, $id)
    {
        $request->validate([
            'title'     => 'nullable|string|max:255',
            'content'   => 'nullable|string',
            'is_pinned' => 'nullable|boolean',
        ]);

        $data = $this->service->update($request->all(), (int) $id);
        return ApiResponse::success($data, 'Cập nhật thông báo thành công');
    }

    /** DELETE /teacher/announcements/{id} */
    public function destroy($id)
    {
        $data = $this->service->delete((int) $id);
        return ApiResponse::success($data, 'Xóa thông báo thành công');
    }
}
