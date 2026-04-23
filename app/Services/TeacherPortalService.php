<?php

namespace App\Services;

use App\Exceptions\UserException;
use App\Models\ClassRoom;
use App\Models\ClassSession;
use App\Models\Teacher;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class TeacherPortalService
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

    // ─── Profile ────────────────────────────────────────────────────

    /**
     * Lấy thông tin profile của giáo viên đang đăng nhập.
     */
    public function getProfile(): array
    {
        $teacher = $this->currentTeacher()->load('user');

        return [
            ...$teacher->only(['id', 'expertise', 'experience', 'nationality', 'target_student', 'bio']),
            ...$teacher->user->only(['name', 'email', 'phone', 'avatar', 'gender', 'date_of_birth', 'address', 'status']),
        ];
    }

    /**
     * Cập nhật profile (thông tin teacher + user).
     */
    public function updateProfile(array $data): array
    {
        $teacher = $this->currentTeacher()->load('user');

        $teacher->user->update(array_filter([
            'name' => $data['name'] ?? null,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'gender' => $data['gender'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'avatar' => $data['avatar'] ?? null,
        ], fn($v) => !is_null($v)));

        $teacher->update(array_filter([
            'nationality' => $data['nationality'] ?? null,
            'expertise' => $data['expertise'] ?? null,
            'experience' => $data['experience'] ?? null,
            'target_student' => $data['target_student'] ?? null,
            'bio' => $data['bio'] ?? null,
        ], fn($v) => !is_null($v)));

        return $this->getProfile();
    }

    // ─── Schedules ──────────────────────────────────────────────────

    /**
     * Lịch dạy theo ngày.
     */
    public function getDailySchedules(array $params): array
    {
        $teacher = $this->currentTeacher();
        $classIds = $teacher->teachingClasses()->pluck('class_rooms.id');

        $date = $params['date'] ?? now()->toDateString();

        return ClassSession::query()
            ->join('class_rooms', 'class_sessions.class_id', '=', 'class_rooms.id')
            ->whereIn('class_sessions.class_id', $classIds)
            ->where('class_sessions.date', $date)
            ->select('class_sessions.*', 'class_rooms.class_code', 'class_rooms.meeting_url')
            ->orderBy('class_sessions.start_time', 'asc')
            ->get()
            ->toArray();
    }

    /**
     * Lịch dạy theo tuần.
     */
    public function getWeeklySchedules(array $params): array
    {
        $teacher = $this->currentTeacher();
        $classIds = $teacher->teachingClasses()->pluck('class_rooms.id');

        $date = isset($params['date']) ? Carbon::parse($params['date']) : now();
        $startOfWeek = $date->copy()->startOfWeek()->toDateString();
        $endOfWeek = $date->copy()->endOfWeek()->toDateString();

        return ClassSession::query()
            ->join('class_rooms', 'class_sessions.class_id', '=', 'class_rooms.id')
            ->whereIn('class_sessions.class_id', $classIds)
            ->whereBetween('class_sessions.date', [$startOfWeek, $endOfWeek])
            ->select('class_sessions.*', 'class_rooms.class_code')
            ->orderBy('class_sessions.date', 'asc')
            ->orderBy('class_sessions.start_time', 'asc')
            ->get()
            ->toArray();
    }

    // ─── Classes ────────────────────────────────────────────────────

    /**
     * Danh sách lớp giáo viên đang phụ trách.
     */
    public function myClasses(array $param): array
    {
        $teacher = $this->currentTeacher();

        $query = $teacher->teachingClasses()
            ->with(['course:id,name', 'schedules'])
            ->withCount('students');

        if (!empty($param['status'])) {
            $query->where('class_rooms.status', $param['status']);
        }
        if (!empty($param['search'])) {
            $query->where('class_rooms.class_code', 'like', '%' . $param['search'] . '%');
        }

        return $query->get()->map(fn($cls) => [
            'id' => $cls->id,
            'class_code' => $cls->class_code,
            'start_day' => $cls->start_day,
            'end_day' => $cls->end_day,
            'max_student' => $cls->max_student,
            'status' => $cls->status,
            'course_name' => $cls->course->name ?? null,
            'students_count' => $cls->students_count,
            'schedules' => $cls->schedules,
        ])->toArray();
    }

    /**
     * Chi tiết một lớp mà giáo viên phụ trách.
     */
    public function classDetail(int $classId): array
    {
        $teacher = $this->currentTeacher();

        $class = $teacher->teachingClasses()
            ->with([
                'course:id,name',
                'schedules',
                'teachers.user:id,name,avatar',
                'students.user:id,name,avatar',
            ])
            ->withCount('students')
            ->where('class_rooms.id', $classId)
            ->first();

        if (!$class) {
            throw new UserException('Không tìm thấy lớp học hoặc bạn không có quyền truy cập.');
        }

        return [
            'id' => $class->id,
            'class_code' => $class->class_code,
            'start_day' => $class->start_day,
            'end_day' => $class->end_day,
            'max_student' => $class->max_student,
            'student_count' => $class->students_count,
            'meeting_url' => $class->meeting_url,
            'status' => $class->status,
            'course_name' => $class->course->name ?? '',
            'schedules' => $class->schedules->map(fn($s) => [
                'id' => $s->id,
                'day_of_week' => $s->day_of_week,
                'start_time' => $s->start_time,
                'end_time' => $s->end_time,
            ])->toArray(),
            'teachers' => $class->teachers->map(fn($t) => [
                'id' => $t->id,
                'name' => $t->user->name ?? '',
                'avatar' => $t->user->avatar ?? '',
            ])->toArray(),
            'students' => $class->students->map(fn($s) => [
                'id' => $s->id,
                'name' => $s->user->name ?? '',
                'avatar' => $s->user->avatar ?? '',
            ])->toArray(),
        ];
    }

    /**
     * Xóa một buổi học.
     */
    public function deleteSession(int $sessionId): bool
    {
        $teacher = $this->currentTeacher();
        $classIds = $teacher->teachingClasses()->pluck('class_rooms.id')->toArray();

        $session = ClassSession::where('id', $sessionId)
            ->whereIn('class_id', $classIds)
            ->first();

        if (!$session) {
            throw new UserException('Không tìm thấy buổi học hoặc bạn không có quyền xóa.');
        }

        return $session->delete();
    }

    /**
     * Thống kê dashboard cho giáo viên.
     */
    public function getDashboardStats(): array
    {
        $teacher = $this->currentTeacher();
        $classIds = $teacher->teachingClasses()->pluck('class_rooms.id');

        $totalClasses = $classIds->count();

        // Tổng số học sinh (unique) từ các lớp giáo viên dạy
        $totalStudents = \DB::table('class_students')
            ->whereIn('class_id', $classIds)
            ->distinct('student_id')
            ->count('student_id');

        $startOfWeek = now()->startOfWeek()->toDateString();
        $endOfWeek = now()->endOfWeek()->toDateString();

        $sessionsThisWeek = ClassSession::whereIn('class_id', $classIds)
            ->whereBetween('date', [$startOfWeek, $endOfWeek])
            ->count();

        // Tạm thời để giá trị giả lập cho tỉ lệ chuyên cần
        $attendanceRate = 97.8;

        return [
            'total_classes' => $totalClasses,
            'total_students' => $totalStudents,
            'sessions_per_week' => $sessionsThisWeek,
            'attendance_rate' => $attendanceRate,
        ];
    }
}

