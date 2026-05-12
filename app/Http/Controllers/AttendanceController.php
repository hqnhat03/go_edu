<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Services\AttendanceService;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function __construct(
        private AttendanceService $service,
        private \App\Services\ActivityLogService $activityLogService
    ) {}

    /**
     * Lấy danh sách học sinh của buổi học để điểm danh.
     * GET /teacher/sessions/{sessionId}/students
     */
    public function getStudents($sessionId)
    {
        $data = $this->service->getStudents((int) $sessionId);
        return ApiResponse::success($data);
    }

    /**
     * Lưu thông tin điểm danh.
     * POST /teacher/sessions/{sessionId}/attendance
     */
    public function submitAttendance(Request $request, $sessionId)
    {
        $request->validate([
            'attendance' => 'required|array',
            'attendance.*.student_id' => 'required|integer|exists:students,id',
            'attendance.*.status' => 'required|in:present,absent,late',
            'attendance.*.note' => 'nullable|string',
        ]);

        $this->service->submitAttendance((int) $sessionId, $request->input('attendance'));

        $this->activityLogService->log(
            action: 'submit_attendance',
            subject: \App\Models\ClassSession::find($sessionId),
            description: "Đã nộp thông tin điểm danh cho buổi học",
            properties: ['attendance_count' => count($request->input('attendance'))]
        );

        return ApiResponse::success(null, 'Lưu thông tin điểm danh thành công');
    }
}
