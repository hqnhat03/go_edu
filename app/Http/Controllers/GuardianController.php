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

    public function index(Request $request)
    {
        $data = $this->guardianService->listGuardian($request->all());
        return ApiResponse::success($data, "Lấy danh sách người giám hộ thành công");
    }

    public function store(CreateRequest $request)
    {
        $data = $this->guardianService->createGuardian($request->validated());
        return ApiResponse::success($data, "Thêm người giám hộ thành công");
    }

    public function show($id)
    {
        $data = $this->guardianService->getGuardian($id);
        return ApiResponse::success($data, "Lấy thông tin người giám hộ thành công");
    }

    public function update(UpdateRequest $request, $id)
    {
        $data = $this->guardianService->updateGuardian($request->validated(), $id);
        return ApiResponse::success($data, "Cập nhật thông tin người giám hộ thành công");
    }

    public function destroy($id)
    {
        $data = $this->guardianService->deleteGuardian($id);
        return ApiResponse::success($data, "Xóa người giám hộ thành công");
    }
}
