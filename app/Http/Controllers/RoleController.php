<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Services\RoleService;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    private $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    public function listRole()
    {
        $data = RoleService::listRole();
        return ApiResponse::success($data, 'Lấy danh sách vai trò thành công');
    }

    public function createRole(Request $request)
    {
        $data = RoleService::createRole($request);
        return ApiResponse::success($data, 'Tạo vai trò thành công');
    }

    public function updateRole(Request $request, $id)
    {
        $data = RoleService::updateRole($request, $id);
        return ApiResponse::success($data, 'Cập nhật vai trò thành công');
    }

    public function deleteRole($id)
    {
        $data = RoleService::deleteRole($id);
        return ApiResponse::success($data, 'Xóa vai trò thành công');

    }
}

