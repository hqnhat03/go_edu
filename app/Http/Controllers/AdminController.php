<?php

namespace App\Http\Controllers;

use App\Exceptions\UserException;
use App\Helpers\ApiResponse;
use App\Http\Requests\Admin\CreateRequest;
use App\Http\Requests\Admin\UpdateRequest;
use App\Services\AdminService;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    private AdminService $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }

    public function index(Request $request)
    {
        $data = $this->adminService->listAdmin($request->all());
        return ApiResponse::success($data);
    }

    public function store(CreateRequest $request)
    {
        try {
            $data = $this->adminService->createAdmin($request->validated());
            return ApiResponse::success($data, 'Tạo admin thành công', [], 201);
        } catch (UserException $e) {
            return ApiResponse::error($e->getMessage(), [], 422);
        }
    }

    public function show($id)
    {
        try {
            $data = $this->adminService->getAdmin((int) $id);
            return ApiResponse::success($data);
        } catch (UserException $e) {
            return ApiResponse::error($e->getMessage(), [], 404);
        }
    }

    public function update(UpdateRequest $request, $id)
    {
        try {
            $data = $this->adminService->updateAdmin($request->validated(), (int) $id);
            return ApiResponse::success($data, 'Cập nhật admin thành công');
        } catch (UserException $e) {
            return ApiResponse::error($e->getMessage(), [], 404);
        }
    }

    public function destroy($id)
    {
        try {
            $this->adminService->deleteAdmin((int) $id);
            return ApiResponse::success(null, 'Xóa admin thành công');
        } catch (UserException $e) {
            return ApiResponse::error($e->getMessage(), [], 404);
        }
    }
}
