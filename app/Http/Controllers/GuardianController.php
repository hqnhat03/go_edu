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
        $guardians = $this->guardianService->listGuardian($request->all());
        return ApiResponse::success($guardians->getCollection()->map(function ($guardian) {
            return [
                'id' => $guardian->id,
                'name' => $guardian->name, // from joined users table
                'email' => $guardian->email,
                'phone' => $guardian->phone,
                'status' => $guardian->status,
                'avatar' => $guardian->avatar,
                'gender' => $guardian->gender,
                'address' => $guardian->address,
                'date_of_birth' => $guardian->date_of_birth,
                'students' => $guardian->students->map(function ($student) {
                    return [
                        'id' => $student->id,
                        'name' => $student->user->name,
                    ];
                })
            ];
        }), "Lấy danh sách người giám hộ thành công", [
            'total' => $guardians->total(),
            'per_page' => $guardians->perPage(),
            'current_page' => $guardians->currentPage(),
            'last_page' => $guardians->lastPage()
        ]);
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
