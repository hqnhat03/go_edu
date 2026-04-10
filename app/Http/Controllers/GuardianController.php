<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\Guardian\CreateRequest;
use App\Http\Requests\Guardian\UpdateRequest;
use App\Services\GuardianService;
use Illuminate\Http\Request;

class GuardianController extends Controller
{
    protected $guardianService;

    public function __construct(GuardianService $guardianService)
    {
        $this->guardianService = $guardianService;
    }

    public function listGuardian(Request $request)
    {
        $data = $this->guardianService->listGuardian($request);
        return ApiResponse::success($data, "Lấy danh sách người giám hộ thành công");
    }

    public function createGuardian(CreateRequest $request)
    {
        $data = $this->guardianService->createGuardian($request);
        return ApiResponse::success($data, "Thêm người giám hộ thành công");
    }

    public function getGuardian($id)
    {
        $data = $this->guardianService->getGuardian($id);
        return ApiResponse::success($data, "Lấy thông tin người giám hộ thành công");
    }

    public function updateGuardian(UpdateRequest $request, $id)
    {
        $data = $this->guardianService->updateGuardian($request, $id);
        return ApiResponse::success($data, "Cập nhật thông tin người giám hộ thành công");
    }

    public function deleteGuardian($id)
    {
        $data = $this->guardianService->deleteGuardian($id);
        return ApiResponse::success($data, "Xóa người giám hộ thành công");
    }
}
