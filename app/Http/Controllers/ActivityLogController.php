<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function __construct(
        private ActivityLogService $activityLogService
    ) {
    }

    /**
     * Display a listing of the activity logs.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['action', 'search', 'start_date', 'end_date']);
        $perPage = $request->get('limit', 15);

        $data = $this->activityLogService->getPaginatedActivities($filters, $perPage);

        return ApiResponse::success($data->items(), 'Lấy danh sách hoạt động hệ thống thành công', [
            'current_page' => $data->currentPage(),
            'last_page' => $data->lastPage(),
            'per_page' => $data->perPage(),
            'total' => $data->total(),
        ]);
    }
}
