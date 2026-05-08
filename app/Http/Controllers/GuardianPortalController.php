<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Services\GuardianPortalService;
use Illuminate\Http\Request;

class GuardianPortalController extends Controller
{
    public function __construct(private GuardianPortalService $service)
    {
    }

    /** GET /guardian/profile */
    public function getProfile()
    {
        $data = $this->service->getProfile();
        return ApiResponse::success($data);
    }

    /** PUT /guardian/profile */
    public function updateProfile(Request $request)
    {
        $data = $this->service->updateProfile($request->all());
        return ApiResponse::success($data, 'Cập nhật thông tin thành công');
    }

    /** GET /guardian/students */
    public function myStudents()
    {
        $data = $this->service->getMyStudents();
        return ApiResponse::success($data);
    }

    /** GET /guardian/students/{id} */
    public function studentDetail($id)
    {
        $data = $this->service->getStudentDetail((int) $id);
        return ApiResponse::success($data);
    }

    /** GET /guardian/students/{id}/schedules */
    public function studentSchedules(Request $request, $id)
    {
        $data = $this->service->getStudentSchedules((int) $id, $request->all());
        return ApiResponse::success($data);
    }

    /** GET /guardian/students/{id}/exam-results */
    public function studentExamResults(Request $request, $id)
    {
        $data = $this->service->getStudentExamResults((int) $id, $request->all());
        return ApiResponse::success($data);
    }

    /** GET /guardian/dashboard/stats */
    public function dashboardStats()
    {
        $data = $this->service->getDashboardStats();
        return ApiResponse::success($data);
    }

    /** GET /guardian/students/{id}/stats */
    public function studentStats($id)
    {
        $data = $this->service->getStudentStats((int) $id);
        return ApiResponse::success($data);
    }

    /** GET /guardian/students/{id}/sessions */
    public function studentSessions(Request $request, $id)
    {
        $result = $this->service->getStudentSessions((int) $id, $request->all());
        return ApiResponse::success($result['data'], 'OK', $result['meta']);
    }
}
