<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\Level\CreateRequest;
use App\Http\Requests\Level\UpdateRequest;
use App\Http\Resources\LevelResource;
use App\Models\Level;
use App\Services\LevelService;
use Illuminate\Http\Request;

class LevelController extends Controller
{
    private $levelService;
    public function __construct(LevelService $levelService)
    {
        $this->levelService = $levelService;
    }

    public function listLevel()
    {
        $data = $this->levelService->listLevel();
        return ApiResponse::success($data, "Lấy danh sách trình độ thành công");
    }

    public function createLevel(CreateRequest $request)
    {
        $data = $this->levelService->createLevel($request);
        return ApiResponse::success($data, "Tạo trình độ thành công");
    }

    public function updateLevel(UpdateRequest $request, $id)
    {
        $data = $this->levelService->updateLevel($request, $id);
        return ApiResponse::success($data, "Cập nhật trình độ thành công");
    }

    public function deleteLevel($id)
    {
        $data = $this->levelService->deleteLevel($id);
        return ApiResponse::success($data, "Xóa trình độ thành công");
    }
}
