<?php

namespace App\Services;

use App\Exceptions\UserException;
use App\Models\Attendance;
use App\Models\ClassSession;
use App\Models\Teacher;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    /**
     * Lấy teacher record của user đang đăng nhập.
     */
    private function currentTeacher(): Teacher
    {
        $teacher = Teacher::where('user_id', Auth::id())->first();
        if (!$teacher) {
            throw new UserException('Không tìm thấy thông tin giáo viên.');
        }
        return $teacher;
    }

    /**
     * Kiểm tra giáo viên có quyền quản lý buổi học này không.
     */
    private function authorizeSession(int $sessionId): ClassSession
    {
        $teacher = $this->currentTeacher();
        $classIds = $teacher->teachingClasses()->pluck('class_rooms.id')->toArray();

        $session = ClassSession::where('id', $sessionId)
            ->whereIn('class_id', $classIds)
            ->first();

        if (!$session) {
            throw new UserException('Không tìm thấy buổi học hoặc bạn không có quyền điểm danh.');
        }
        return $session;
    }

    /**
     * Lấy danh sách học sinh của một buổi học để điểm danh.
     * Trả về danh sách học sinh kèm trạng thái điểm danh (nếu có).
     */
    public function getStudents(int $sessionId): array
    {
        $session = $this->authorizeSession($sessionId);

        // Lấy tất cả học sinh trong lớp của buổi học này
        $students = DB::table('class_students')
            ->join('students', 'class_students.student_id', '=', 'students.id')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->where('class_students.class_id', $session->class_id)
            ->select('students.id', 'users.name', 'users.avatar')
            ->get();

        // Lấy dữ liệu điểm danh đã lưu
        $attendances = Attendance::where('class_session_id', $sessionId)
            ->get()
            ->keyBy('student_id');

        $session->load('classRoom');

        return [
            'session' => [
                'id' => $session->id,
                'class_code' => $session->classRoom->class_code ?? 'N/A',
                'date' => $session->date,
                'start_time' => $session->start_time,
                'end_time' => $session->end_time,
            ],
            'students' => $students->map(function ($student) use ($attendances) {
                $attendance = $attendances->get($student->id);
                return [
                    'student_id' => $student->id,
                    'name' => $student->name,
                    'avatar' => $student->avatar,
                    'status' => $attendance ? $attendance->status : null,
                    'note' => $attendance ? $attendance->note : null,
                ];
            })->toArray()
        ];
    }

    /**
     * Lưu thông tin điểm danh.
     * data: [ ['student_id' => 1, 'status' => 'present', 'note' => '...'], ... ]
     */
    public function submitAttendance(int $sessionId, array $data): void
    {
        $session = $this->authorizeSession($sessionId);

        DB::transaction(function () use ($sessionId, $data) {
            foreach ($data as $item) {
                Attendance::updateOrCreate(
                    [
                        'class_session_id' => $sessionId,
                        'student_id' => $item['student_id'],
                    ],
                    [
                        'status' => $item['status'],
                        'note' => $item['note'] ?? null,
                    ]
                );
            }
        });
    }
}
