<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Services\TeacherPortalService;
use Illuminate\Http\Request;

class TeacherPortalController extends Controller
{
    public function __construct(private TeacherPortalService $service)
    {
    }

    /** GET /teacher/profile */
    public function getProfile()
    {
        $data = $this->service->getProfile();
        return ApiResponse::success($data);
    }

    /** PUT /teacher/profile */
    public function updateProfile(Request $request)
    {
        $data = $this->service->updateProfile($request->all());
        return ApiResponse::success($data, 'Cập nhật thông tin thành công');
    }

    /** GET /teacher/schedules/day */
    public function dailySchedules(Request $request)
    {
        $data = $this->service->getDailySchedules($request->all());
        return ApiResponse::success($data);
    }

    /** GET /teacher/schedules/week */
    public function weeklySchedules(Request $request)
    {
        $data = $this->service->getWeeklySchedules($request->all());
        return ApiResponse::success($data);
    }

    /** GET /teacher/classes */
    public function myClasses(Request $request)
    {
        $data = $this->service->myClasses($request->all());
        return ApiResponse::success($data);
    }

    /** GET /teacher/classes/{id} */
    public function classDetail($id)
    {
        $data = $this->service->classDetail((int) $id);
        return ApiResponse::success($data);
    }

    /** DELETE /teacher/schedules/sessions/{id} */
    public function deleteSession($id)
    {
        $this->service->deleteSession((int) $id);
        return ApiResponse::success(null, 'Xóa buổi học thành công');
    }

    /** GET /teacher/dashboard/stats */
    public function dashboardStats()
    {
        $data = $this->service->getDashboardStats();
        return ApiResponse::success($data);
    }
}


