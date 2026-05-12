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
    private \App\Services\ActivityLogService $activityLogService;

    public function __construct(AdminService $adminService, \App\Services\ActivityLogService $activityLogService)
    {
        $this->adminService = $adminService;
        $this->activityLogService = $activityLogService;
    }

    public function index(Request $request)
    {
        $admins = $this->adminService->listAdmin($request->all());
        
        return ApiResponse::success($admins->getCollection()->map(function ($admin) {
            return [
                ...$admin->only(['id', 'name', 'email', 'phone', 'avatar', 'status']),
                'roles' => $admin->roles->pluck('name')
            ];
        }), 'Lấy danh sách admin thành công', [
            'total' => $admins->total(),
            'per_page' => $admins->perPage(),
            'current_page' => $admins->currentPage(),
            'last_page' => $admins->lastPage()
        ]);
    }

    public function store(CreateRequest $request)
    {
        try {
            $user = $this->adminService->createAdmin($request->validated());
            
            $this->activityLogService->log(
                action: 'create_admin',
                subject: $user,
                description: "Created a new admin account: " . ($user->name ?? $user->email)
            );

            return ApiResponse::success($this->adminService->formatAdmin($user), 'Tạo admin thành công', [], 201);
        } catch (UserException $e) {
            return ApiResponse::error($e->getMessage(), [], 422);
        }
    }

    public function show($id)
    {
        try {
            $user = $this->adminService->getAdmin((int) $id);
            return ApiResponse::success($this->adminService->formatAdmin($user));
        } catch (UserException $e) {
            return ApiResponse::error($e->getMessage(), [], 404);
        }
    }

    public function update(UpdateRequest $request, $id)
    {
        try {
            $user = $this->adminService->updateAdmin($request->validated(), (int) $id);
            
            $this->activityLogService->log(
                action: 'update_admin',
                subject: $user,
                description: "Updated admin account: " . ($user->name ?? $user->email)
            );

            return ApiResponse::success($this->adminService->formatAdmin($user), 'Cập nhật admin thành công');
        } catch (UserException $e) {
            return ApiResponse::error($e->getMessage(), [], 404);
        }
    }

    public function destroy($id)
    {
        try {
            $admin = $this->adminService->deleteAdmin((int) $id);
            
            $this->activityLogService->log(
                action: 'delete_admin',
                subject: $admin,
                description: "Deleted admin account: " . ($admin->name ?? $admin->email)
            );

            return ApiResponse::success(null, 'Xóa admin thành công');
        } catch (UserException $e) {
            return ApiResponse::error($e->getMessage(), [], 404);
        }
    }

    public function getProfile()
    {
        try {
            $user = $this->adminService->getProfile();
            return ApiResponse::success($this->adminService->formatAdmin($user));
        } catch (UserException $e) {
            return ApiResponse::error($e->getMessage(), [], 404);
        }
    }

    public function updateProfile(Request $request)
    {
        try {
            $user = $this->adminService->updateProfile($request->all());
            
            return ApiResponse::success($this->adminService->formatAdmin($user), 'Cập nhật hồ sơ thành công');
        } catch (UserException $e) {
            return ApiResponse::error($e->getMessage(), [], 400);
        }
    }
}
