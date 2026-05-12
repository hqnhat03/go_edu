<?php

namespace App\Traits;

use App\Services\ActivityLogService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    protected static function bootLogsActivity()
    {
        foreach (static::getLogEvents() as $event) {
            static::$event(function (Model $model) use ($event) {
                $model->logActivity($event);
            });
        }
    }

    protected static function getLogEvents(): array
    {
        if (isset(static::$recordEvents)) {
            return static::$recordEvents;
        }

        return ['created', 'updated', 'deleted'];
    }

    public function logActivity(string $event)
    {
        $description = $this->getLogDescription($event);
        $properties = $this->getLogProperties($event);

        app(ActivityLogService::class)->log(
            action: $event,
            subject: $this,
            description: $description,
            properties: $properties,
            visibility: $this->getLogVisibility(),
            groupId: $this->getLogGroupId()
        );
    }

    protected function getLogDescription(string $event): string
    {
        $name = class_basename($this);
        $action = match ($event) {
            'created' => 'Đã tạo',
            'updated' => 'Đã cập nhật',
            'deleted' => 'Đã xóa',
            default => $event
        };

        $translatedName = match ($name) {
            'Student' => 'Học sinh',
            'Teacher' => 'Giáo viên',
            'Course' => 'Khóa học',
            'ClassRoom' => 'Lớp học',
            'Exam' => 'Kỳ thi',
            'Lecture' => 'Bài giảng',
            'Attendance' => 'Điểm danh',
            'User' => 'Người dùng',
            'Guardian' => 'Phụ huynh',
            'Payment' => 'Thanh toán',
            'CourseRegistration' => 'Đăng ký khóa học',
            default => $name
        };

        return "{$action} {$translatedName}";
    }

    protected function getLogProperties(string $event): array
    {
        $properties = [];

        if ($event === 'updated') {
            $properties['old'] = array_intersect_key($this->getRawOriginal(), $this->getDirty());
            $properties['new'] = $this->getDirty();
        } elseif ($event === 'created') {
            $properties['attributes'] = $this->toArray();
        }

        // Filter sensitive attributes
        $ignore = ['password', 'remember_token', 'created_at', 'updated_at', 'deleted_at'];
        if (isset($this->logAttributesToIgnore)) {
            $ignore = array_merge($ignore, $this->logAttributesToIgnore);
        }

        if (isset($properties['old'])) {
            $properties['old'] = array_diff_key($properties['old'], array_flip($ignore));
        }
        if (isset($properties['new'])) {
            $properties['new'] = array_diff_key($properties['new'], array_flip($ignore));
        }
        if (isset($properties['attributes'])) {
            $properties['attributes'] = array_diff_key($properties['attributes'], array_flip($ignore));
        }

        return $properties;
    }

    protected function getLogVisibility(): string
    {
        return 'admin';
    }

    protected function getLogGroupId(): ?int
    {
        return $this->class_id ?? $this->classroom_id ?? null;
    }
}
