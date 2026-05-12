<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogService
{
    /**
     * Log a system activity.
     *
     * @param string $action
     * @param mixed $subject The object being acted upon
     * @param string $description Human readable description
     * @param array $properties Extra data (old/new values)
     * @param string $visibility Who can see this (admin, teacher, student, parent, public)
     * @param int|null $groupId Scope ID (e.g. classroom_id)
     * @param mixed $causer The actor (defaults to currently logged in user)
     * @return ActivityLog
     */
    public function log(
        string $action,
        $subject = null,
        string $description = null,
        array $properties = [],
        string $visibility = 'admin',
        int $groupId = null,
        $causer = null
    ) {
        $causer = $causer ?? Auth::user();

        $subjectId = null;
        $subjectType = null;

        if (is_object($subject)) {
            $subjectId = $subject->id ?? null;
            $subjectType = get_class($subject);
        } elseif (is_array($subject)) {
            $subjectId = $subject['id'] ?? null;
        }

        return ActivityLog::create([
            'causer_id' => $causer ? $causer->id : null,
            'causer_type' => $causer ? get_class($causer) : null,
            'action' => $action,
            'subject_id' => $subjectId,
            'subject_type' => $subjectType,
            'visibility' => $visibility,
            'group_id' => $groupId,
            'description' => $description,
            'properties' => $properties,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * Lấy danh sách hoạt động có phân trang và lọc cho Admin.
     */
    public function getPaginatedActivities(array $filters = [], int $perPage = 15)
    {
        $query = ActivityLog::with(['causer', 'subject'])->latest();

        // Lọc theo hành động
        if (!empty($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        // Lọc theo đối tượng tác động (subject_type)
        if (!empty($filters['subject_type'])) {
            $query->where('subject_type', 'LIKE', '%' . $filters['subject_type']);
        }

        // Lọc theo người thực hiện (causer_id & causer_type)
        if (!empty($filters['causer_id'])) {
            $query->where('causer_id', $filters['causer_id']);
        }
        if (!empty($filters['causer_type'])) {
            $query->where('causer_type', 'LIKE', '%' . $filters['causer_type']);
        }

        // Tìm kiếm theo mô tả
        if (!empty($filters['search'])) {
            $query->where('description', 'LIKE', '%' . $filters['search'] . '%');
        }

        // Lọc theo ngày bắt đầu
        if (!empty($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }

        // Lọc theo ngày kết thúc
        if (!empty($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        return $query->paginate($perPage)
            ->through(function ($log) {
                // Thử lấy tên của đối tượng tác động
                $subjectName = null;
                if ($log->subject) {
                    $subjectName = $log->subject->name
                        ?? $log->subject->title
                        ?? $log->subject->full_name
                        ?? $log->subject->label
                        ?? $log->subject->class_code
                        ?? null;
                }

                return [
                    'id' => $log->id,
                    'action' => $this->translateAction($log->action),
                    'action_raw' => $log->action,
                    'description' => $this->translateDescription($log->description, $log->action),
                    'subject_name' => $subjectName,
                    'subject_type' => $log->subject_type ? class_basename($log->subject_type) : null,
                    'causer_name' => $log->causer->name ?? 'Hệ thống',
                    'causer_type' => $log->causer_type ? class_basename($log->causer_type) : null,
                    'causer_avatar' => $log->causer->avatar ?? null,
                    'ip_address' => $log->ip_address,
                    'user_agent' => $log->user_agent,
                    'created_at' => $log->created_at->toDateTimeString(),
                    'created_at_human' => $log->created_at->locale('vi')->diffForHumans(),
                ];
            });
    }

    /**
     * Lấy danh sách hoạt động gần đây cho Dashboard.
     */
    public function getRecentActivities(int $limit = 10)
    {
        return ActivityLog::with(['causer', 'subject'])
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function ($log) {
                // Thử lấy tên của đối tượng tác động
                $subjectName = null;
                if ($log->subject) {
                    $subjectName = $log->subject->name
                        ?? $log->subject->title
                        ?? $log->subject->full_name
                        ?? $log->subject->label
                        ?? $log->subject->class_code
                        ?? null;
                }

                return [
                    'id' => $log->id,
                    'action' => $this->translateAction($log->action),
                    'description' => $this->translateDescription($log->description, $log->action),
                    'subject_name' => $subjectName,
                    'subject_type' => $log->subject_type ? class_basename($log->subject_type) : null,
                    'causer_name' => $log->causer->name ?? 'Hệ thống',
                    'causer_avatar' => $log->causer->avatar ?? null,
                    'created_at' => $log->created_at->locale('vi')->diffForHumans(),
                ];
            });
    }

    private function translateAction(string $action): string
    {
        return match ($action) {
            'login' => 'đăng nhập',
            'logout' => 'đăng xuất',
            'created' => 'tạo mới',
            'updated' => 'cập nhật',
            'deleted' => 'xóa',
            'change_password' => 'đổi mật khẩu',
            'submit_attendance' => 'điểm danh',
            'sync_questions' => 'cập nhật câu hỏi',
            'grade_exam' => 'chấm điểm',
            default => $action,
        };
    }

    private function translateDescription(string $description, string $action): string
    {
        // Dịch các câu log cũ hoặc log có cấu trúc cố định
        $translations = [
            'User logged in' => 'Người dùng đã đăng nhập',
            'User logged out' => 'Người dùng đã đăng xuất',
            'User changed their password' => 'Người dùng đã đổi mật khẩu',
            'Submitted attendance for session' => 'Đã gửi thông tin điểm danh cho buổi học',
            'Synced questions for exam' => 'Đã cập nhật danh sách câu hỏi cho bài kiểm tra',
            'Graded exam result' => 'Đã chấm điểm kết quả bài kiểm tra',
        ];

        if (isset($translations[$description])) {
            return $translations[$description];
        }

        // Xử lý các câu log tự động cũ "Model was event"
        if (preg_match('/^(\w+) was (\w+)$/', $description, $matches)) {
            $model = $matches[1];
            $event = $matches[2];

            $actionStr = match ($event) {
                'created' => 'Đã tạo',
                'updated' => 'Đã cập nhật',
                'deleted' => 'Đã xóa',
                default => $event
            };

            $modelStr = match ($model) {
                'Student' => 'Học sinh',
                'Teacher' => 'Giáo viên',
                'Course' => 'Khóa học',
                'ClassRoom' => 'Lớp học',
                'Exam' => 'Kỳ thi',
                'Lecture' => 'Bài giảng',
                'Attendance' => 'Điểm danh',
                'User' => 'Người dùng',
                'Guardian' => 'Phụ huynh',
                default => $model
            };

            return "{$actionStr} {$modelStr}";
        }

        return $description;
    }
}
