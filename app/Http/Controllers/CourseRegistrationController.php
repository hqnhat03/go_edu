<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Services\CourseRegistrationService;
use Illuminate\Http\Request;

class CourseRegistrationController extends Controller
{
    public function __construct(
        private CourseRegistrationService $registrationService
    ) {
    }

    public function index(Request $request)
    {
        $data = $this->registrationService->listRegistrations($request->all());
        return ApiResponse::success($data->items(), 'Lấy danh sách đăng ký thành công', [
            'total' => $data->total(),
            'per_page' => $data->perPage(),
            'current_page' => $data->currentPage(),
            'last_page' => $data->lastPage()
        ]);
    }

    public function show($id)
    {
        $data = $this->registrationService->getRegistration($id);
        return ApiResponse::success($data, 'Lấy chi tiết đăng ký thành công');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,completed,cancelled',
        ]);

        $data = $this->registrationService->updateStatus($id, $request->status);
        return ApiResponse::success($data, 'Cập nhật trạng thái đăng ký thành công');
    }
}
