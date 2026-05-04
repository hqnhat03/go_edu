<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Services\EvaluationService;
use Illuminate\Http\Request;

class EvaluationController extends Controller
{
    public function __construct(private EvaluationService $service) {}

    /** GET /teacher/classes/{classId}/evaluations */
    public function index($classId)
    {
        $data = $this->service->list((int) $classId);
        return ApiResponse::success($data);
    }

    /** POST /teacher/classes/{classId}/evaluations */
    public function store(Request $request, $classId)
    {
        $request->validate([
            'student_id'   => 'required|integer|exists:students,id',
            'type'         => 'nullable|in:midterm,final,behavior',
            'score'        => 'required|integer|min:0|max:100',
            'comment'      => 'nullable|string',
            'evaluated_at' => 'nullable|date',
        ]);

        $data = $this->service->create($request->all(), (int) $classId);
        return ApiResponse::success($data, 'Tạo đánh giá thành công', [], 201);
    }

    /** PUT /teacher/evaluations/{id} */
    public function update(Request $request, $id)
    {
        $request->validate([
            'type'         => 'nullable|in:midterm,final,behavior',
            'score'        => 'nullable|integer|min:0|max:100',
            'comment'      => 'nullable|string',
            'evaluated_at' => 'nullable|date',
        ]);

        $data = $this->service->update($request->all(), (int) $id);
        return ApiResponse::success($data, 'Cập nhật đánh giá thành công');
    }

    /** DELETE /teacher/evaluations/{id} */
    public function destroy($id)
    {
        $data = $this->service->delete((int) $id);
        return ApiResponse::success($data, 'Xóa đánh giá thành công');
    }
}
