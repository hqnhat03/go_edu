<?php

namespace App\Services;

use App\Exceptions\UserException;
use App\Models\Guardian;
use App\Models\Student;
use App\Models\ClassSession;
use App\Models\ExamResult;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class GuardianPortalService
{
    /**
     * Lấy guardian record của user đang đăng nhập.
     */
    private function currentGuardian(): Guardian
    {
        $guardian = Guardian::where('user_id', Auth::id())->first();
        if (!$guardian) {
            throw new UserException('Không tìm thấy thông tin phụ huynh.');
        }
        return $guardian;
    }

    /**
     * Lấy danh sách học sinh mà phụ huynh này quản lý.
     */
    public function getMyStudents(): array
    {
        $guardian = $this->currentGuardian();

        return $guardian->students()
            ->with(['user:id,name,avatar,gender,date_of_birth'])
            ->get()
            ->map(function ($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->user->name ?? null,
                    'avatar' => $student->user->avatar ?? null,
                    'gender' => $student->user->gender ?? null,
                    'date_of_birth' => $student->user->date_of_birth ?? null,
                    'school' => $student->school,
                    'grade' => $student->grade,
                ];
            })
            ->toArray();
    }

    /**
     * Lấy thông tin chi tiết một học sinh.
     */
    public function getStudentDetail(int $studentId): array
    {
        $guardian = $this->currentGuardian();

        $student = $guardian->students()
            ->with(['user:id,name,avatar'])
            ->where('students.id', $studentId)
            ->first();

        if (!$student) {
            throw new UserException('Bạn không có quyền xem thông tin học sinh này.');
        }

        return [
            'id' => $student->id,
            'name' => $student->user->name ?? null,
            'avatar' => $student->user->avatar ?? null,
            'school' => $student->school,
            'grade' => $student->grade,
        ];
    }

    /**
     * Lấy thông tin profile của phụ huynh đang đăng nhập.
     */
    public function getProfile(): array
    {
        $guardian = $this->currentGuardian()->load('user');

        return [
            'id' => $guardian->id,
            'name' => $guardian->user->name,
            'email' => $guardian->user->email,
            'phone' => $guardian->user->phone,
            'avatar' => $guardian->user->avatar,
            'gender' => $guardian->user->gender,
            'date_of_birth' => $guardian->user->date_of_birth,
            'address' => $guardian->user->address,
        ];
    }

    /**
     * Cập nhật profile.
     */
    public function updateProfile(array $data): array
    {
        $guardian = $this->currentGuardian()->load('user');

        $guardian->user->update(array_filter([
            'name' => $data['name'] ?? null,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'gender' => $data['gender'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'avatar' => $data['avatar'] ?? null,
        ], fn($v) => !is_null($v)));

        return $this->getProfile();
    }

    /**
     * Lấy lịch học của một học sinh cụ thể.
     */
    public function getStudentSchedules(int $studentId, array $params): array
    {
        $guardian = $this->currentGuardian();

        // Kiểm tra xem phụ huynh có quyền xem học sinh này không
        $student = $guardian->students()->where('students.id', $studentId)->first();
        if (!$student) {
            throw new UserException('Bạn không có quyền xem thông tin học sinh này.');
        }

        $classIds = $student->classes()->pluck('class_rooms.id');
        $date = $params['date'] ?? now()->toDateString();

        return ClassSession::query()
            ->join('class_rooms', 'class_sessions.class_id', '=', 'class_rooms.id')
            ->join('courses', 'class_rooms.course_id', '=', 'courses.id')
            ->whereIn('class_sessions.class_id', $classIds)
            ->where('class_sessions.date', $date)
            ->select(
                'class_sessions.*',
                'class_rooms.class_code',
                'courses.name as course_name'
            )
            ->orderBy('class_sessions.start_time', 'asc')
            ->get()
            ->toArray();
    }

    /**
     * Lấy kết quả học tập của học sinh.
     */
    public function getStudentExamResults(int $studentId, array $params = []): array
    {
        $guardian = $this->currentGuardian();

        $student = $guardian->students()->where('students.id', $studentId)->first();
        if (!$student) {
            throw new UserException('Bạn không có quyền xem thông tin học sinh này.');
        }

        return $this->getStudentExamResultsList($student, null, $params);
    }

    /**
     * Logic chung để lấy danh sách kết quả bài kiểm tra (bao gồm cả các bài đã kết thúc nhưng chưa nộp).
     */
    private function getStudentExamResultsList(Student $student, int $limit = null, array $params = []): array
    {
        // 1. Get all classes the student is in
        $classIds = $student->classes()->pluck('class_rooms.id');

        // 2. Get all published exams for these classes
        $exams = \App\Models\Exam::whereIn('class_id', $classIds)
            ->where('status', 'published')
            ->with(['classRoom:id,class_code'])
            ->withSum('questions', 'score')
            ->get();

        // 3. Get actual results for this student
        $results = ExamResult::where('student_id', $student->id)
            ->get()
            ->keyBy('exam_id');

        $now = now();

        $allResults = $exams->map(function ($exam) use ($results, $now, $student) {
            $result = $results->get($exam->id);

            // Determine sorting date: prioritize submission time, then exam opening time, then creation time
            $sortDate = $result && $result->submitted_at 
                ? $result->submitted_at 
                : ($exam->open_at ?? $exam->created_at);

            $data = [
                'student_name' => $student->user->name ?? null,
                'id'           => $result->id ?? null,
                'exam_name'    => $exam->name,
                'class_code'   => $exam->classRoom->class_code ?? null,
                'total_score'  => (float) ($exam->questions_sum_score ?? 0),
                'open_at'      => $exam->open_at ? $exam->open_at->toDateTimeString() : null,
                'close_at'     => $exam->close_at ? $exam->close_at->toDateTimeString() : null,
            ];

            if ($result) {
                // Case 1: Already has a result in DB
                $data['score'] = (float) $result->score;
                $data['status'] = $result->status;
                $data['submitted_at'] = $result->submitted_at ? $result->submitted_at->toDateTimeString() : null;
            } else {
                // Case 2: No result yet
                $data['score'] = null;
                $data['submitted_at'] = null;

                if ($exam->close_at && $exam->close_at < $now) {
                    $data['status'] = 'missed'; // Deadline passed
                    $data['score'] = 0.0;
                } elseif ($exam->open_at && $exam->open_at > $now) {
                    $data['status'] = 'upcoming'; // Hasn't started yet
                } else {
                    $data['status'] = 'not_started'; // Open but hasn't taken it
                }
            }

            return $data;
        });

        // Xử lý sắp xếp động từ Frontend
        $sortField = $params['sort'] ?? 'open_at';
        $sortOrder = $params['order'] ?? 'desc';

        $allResults = $allResults->sort(function ($a, $b) use ($sortField, $sortOrder) {
            $valA = $a[$sortField] ?? '';
            $valB = $b[$sortField] ?? '';

            // 1. Sắp xếp số (cho score)
            if ($sortField === 'score') {
                $numA = (float) $valA;
                $numB = (float) $valB;
                if ($numA == $numB) return 0;
                return ($sortOrder === 'desc') ? ($numB <=> $numA) : ($numA <=> $numB);
            }

            // 2. Sắp xếp chuỗi/ngày tháng
            if ($sortOrder === 'desc') {
                return strcmp($valB, $valA);
            }
            return strcmp($valA, $valB);
        });

        if ($limit) {
            $allResults = $allResults->take($limit);
        }

        return $allResults->values()->toArray();
    }

    /**
     * Thống kê Dashboard cho phụ huynh.
     */
    public function getDashboardStats(): array
    {
        $guardian = $this->currentGuardian();

        // 1. Get all students managed by this guardian
        $students = $guardian->students()->with(['user:id,name,avatar'])->get();
        $studentIds = $students->pluck('id')->toArray();
        $totalStudents = count($studentIds);

        // 2. Get today's schedules
        $date = now()->toDateString();
        $todaySchedules = [];

        foreach ($students as $student) {
            $classIds = $student->classes()->pluck('class_rooms.id');

            $schedules = ClassSession::query()
                ->join('class_rooms', 'class_sessions.class_id', '=', 'class_rooms.id')
                ->join('courses', 'class_rooms.course_id', '=', 'courses.id')
                ->whereIn('class_sessions.class_id', $classIds)
                ->where('class_sessions.date', $date)
                ->select(
                    'class_sessions.*',
                    'class_rooms.class_code',
                    'courses.name as course_name'
                )
                ->orderBy('class_sessions.start_time', 'asc')
                ->get();

            foreach ($schedules as $schedule) {
                $todaySchedules[] = [
                    'student_name' => $student->user->name,
                    'class_code' => $schedule->class_code,
                    'course_name' => $schedule->course_name,
                    'start_time' => $schedule->start_time,
                    'end_time' => $schedule->end_time,
                    'meeting_url' => $schedule->meeting_url,
                ];
            }
        }

        usort($todaySchedules, function ($a, $b) {
            return strtotime($a['start_time']) - strtotime($b['start_time']);
        });

        $classesToday = count($todaySchedules);

        // 3. Get recent exam results
        $recentExamResults = [];
        foreach ($students as $student) {
            $results = $this->getStudentExamResultsList($student, 5);
            foreach ($results as $result) {
                $recentExamResults[] = $result;
            }
        }

        usort($recentExamResults, function ($a, $b) {
            $dateA = $a['submitted_at'] ?? ($a['open_at'] ?? '0000-00-00 00:00:00');
            $dateB = $b['submitted_at'] ?? ($b['open_at'] ?? '0000-00-00 00:00:00');
            return strcmp($dateB, $dateA);
        });

        $recentExamResults = array_slice($recentExamResults, 0, 5);
        $newExamResultsCount = count($recentExamResults);

        // 4. Students overview
        $studentsOverview = $students->map(function ($student) {
            return [
                'id' => $student->id,
                'name' => $student->user->name ?? null,
                'avatar' => $student->user->avatar ?? null,
                'school' => $student->school,
                'grade' => $student->grade,
            ];
        })->toArray();

        return [
            'stats' => [
                'total_students' => $totalStudents,
                'classes_today' => $classesToday,
                'new_exam_results' => $newExamResultsCount,
            ],
            'today_schedules' => $todaySchedules,
            'recent_exam_results' => $recentExamResults,
            'students_overview' => $studentsOverview,
        ];
    }

    /**
     * Lấy thống kê chi tiết của một học sinh.
     */
    public function getStudentStats(int $studentId): array
    {
        $guardian = $this->currentGuardian();

        // Kiểm tra quyền
        $student = $guardian->students()->where('students.id', $studentId)->first();
        if (!$student) {
            throw new UserException('Bạn không có quyền xem thông tin học sinh này.');
        }

        // 1. Thống kê chuyên cần (Từ bảng Stat tối ưu)
        $attendanceStats = \App\Models\StudentAttendanceStat::where('student_id', $studentId)
            ->selectRaw('SUM(total_sessions) as total, SUM(present_count) as present, SUM(late_count) as late, SUM(absent_count) as absent')
            ->first();

        $totalSessions = (int) ($attendanceStats->total ?? 0);
        $attendanceRate = $totalSessions > 0
            ? round(($attendanceStats->present / $totalSessions) * 100, 2)
            : 100;

        // 2. Thống kê điểm số
        $examStats = \App\Models\ExamResult::where('student_id', $studentId)
            ->selectRaw('COUNT(*) as total_exams, AVG(score) as avg_score')
            ->first();

        return [
            'attendance' => [
                'total_sessions' => $totalSessions,
                'present' => (int) ($attendanceStats->present ?? 0),
                'late' => (int) ($attendanceStats->late ?? 0),
                'absent' => (int) ($attendanceStats->absent ?? 0),
                'rate' => $attendanceRate
            ],
            'academic' => [
                'total_exams' => (int) ($examStats->total_exams ?? 0),
                'avg_score' => $examStats->avg_score ? round((float) $examStats->avg_score, 2) : 0,
            ]
        ];
    }

    /**
     * Lấy danh sách các phiên học của một học sinh cụ thể (dành cho phụ huynh).
     */
    public function getStudentSessions(int $studentId, array $params): array
    {
        $guardian = $this->currentGuardian();

        // Authorization: Chỉ cho phép Guardian truy cập nếu học sinh thuộc quyền quản lý của họ.
        $student = $guardian->students()->where('students.id', $studentId)->first();
        if (!$student) {
            throw new UserException('Bạn không có quyền xem thông tin học sinh này.');
        }

        $type = $params['type'] ?? 'upcoming';
        $today = now()->toDateString();
        $nowTime = now()->toTimeString();

        // 1. Lọc các sessions thuộc về các classes mà học sinh đang tham gia.
        $classIds = $student->classes()->pluck('class_rooms.id');

        $query = ClassSession::query()
            ->with([
                'classRoom.course:id,name,slug',
                'attendances' => function ($q) use ($studentId) {
                    // Quan trọng: Load quan hệ attendance nhưng chỉ lọc duy nhất bản ghi của học sinh đang được truy vấn.
                    $q->where('student_id', $studentId);
                }
            ])
            ->whereIn('class_id', $classIds);

        // 2. Lọc theo type:
        if ($type === 'upcoming') {
            // upcoming: date > today HOẶC (date == today VÀ start_time >= now). Sắp xếp ASC theo thời gian.
            $query->where(function ($q) use ($today, $nowTime) {
                $q->where('date', '>', $today)
                    ->orWhere(function ($q2) use ($today, $nowTime) {
                        $q2->where('date', $today)
                            ->where('start_time', '>=', $nowTime);
                    });
            })->orderBy('date', 'asc')->orderBy('start_time', 'asc');
        } else {
            // past: date < today HOẶC (date == today VÀ start_time < now). Sắp xếp DESC theo thời gian.
            $query->where(function ($q) use ($today, $nowTime) {
                $q->where('date', '<', $today)
                    ->orWhere(function ($q2) use ($today, $nowTime) {
                        $q2->where('date', $today)
                            ->where('start_time', '<', $nowTime);
                    });
            })->orderBy('date', 'desc')->orderBy('start_time', 'desc');
        }

        // Pagination
        $perPage = $params['per_page'] ?? 10;
        $paginated = $query->paginate($perPage);

        return [
            'data' => collect($paginated->items())->map(function ($session) {
                $attendance = $session->attendances->first();

                return [
                    'id' => $session->id,
                    'date' => $session->date,
                    'start_time' => $session->start_time,
                    'end_time' => $session->end_time,
                    'course_name' => $session->classRoom->course->name ?? null,
                    'class_code' => $session->classRoom->class_code ?? null,
                    'attendance' => $attendance ? [
                        'id' => $attendance->id,
                        'status' => $attendance->status,
                        'note' => $attendance->note,
                    ] : null
                ];
            })->toArray(),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ]
        ];
    }
}
