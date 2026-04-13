<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\New\CreateRequest;
use App\Http\Requests\New\UpdateRequest;
use App\Services\NewService;
use Illuminate\Http\Request;

class NewController extends Controller
{
    private NewService $newService;

    public function __construct(NewService $newService)
    {
        $this->newService = $newService;
    }

    public function index(Request $request)
    {
        $data = $this->newService->getList($request->all());
        return ApiResponse::success($data, 'Lấy danh sách tin tức thành công');
    }

    public function store(CreateRequest $request)
    {
        $data = $this->newService->create($request->validated());
        return ApiResponse::success($data, "Tạo tin tức thành công", [], 201);
    }

    public function show($id)
    {
        $data = $this->newService->findById($id);
        return ApiResponse::success($data);
    }

    public function update(UpdateRequest $request, $id)
    {
        $data = $this->newService->update($request->validated(), $id);
        return ApiResponse::success($data, "Cập nhật tin tức thành công");
    }

    public function destroy($id)
    {
        $data = $this->newService->delete($id);
        return ApiResponse::success($data, "Xóa tin tức thành công");
    }
}
