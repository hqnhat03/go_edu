<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\Level\CreateRequest;
use App\Http\Requests\Level\UpdateRequest;
use App\Services\LevelService;
use Illuminate\Http\Request;

class LevelController extends Controller
{
    private $levelService;
    public function __construct(LevelService $levelService)
    {
        $this->levelService = $levelService;
    }

    public function index(Request $request)
    {
        $params = $request->only([
            'level',
            'status',
            'education_level',
        ]);
        $levels = $this->levelService->listLevel($params);
        return ApiResponse::success($levels, "Lấy danh sách trình độ thành công");
    }

    public function store(CreateRequest $request)
    {
        $data = $this->levelService->createLevel($request->validated());
        return ApiResponse::success($data, "Tạo trình độ thành công");
    }

    public function update(UpdateRequest $request, $id)
    {
        $data = $this->levelService->updateLevel($request->validated(), $id);
        return ApiResponse::success($data, "Cập nhật trình độ thành công");
    }

    public function destroy($id)
    {
        $data = $this->levelService->deleteLevel($id);
        return ApiResponse::success($data, "Xóa trình độ thành công");
    }
}
