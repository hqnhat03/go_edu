<?php

namespace App\Services;

use App\Exceptions\UserException;
use App\Models\ClassAnnouncement;
use App\Models\Teacher;
use Illuminate\Support\Facades\Auth;

class AnnouncementService
{
    private function currentTeacher(): Teacher
    {
        $teacher = Teacher::where('user_id', Auth::id())->first();
        if (!$teacher) {
            throw new UserException('Không tìm thấy thông tin giáo viên.');
        }
        return $teacher;
    }

    /**
     * Danh sách thông báo của lớp (ghim lên đầu).
     */
    public function list(int $classId): array
    {
        return ClassAnnouncement::where('class_id', $classId)
            ->with('teacher.user:id,name,avatar')
            ->orderBy('is_pinned', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Tạo thông báo mới.
     */
    public function create(array $data, int $classId): array
    {
        $teacher = $this->currentTeacher();

        $announcement = ClassAnnouncement::create([
            'class_id'   => $classId,
            'teacher_id' => $teacher->id,
            'title'      => $data['title'],
            'content'    => $data['content'],
            'is_pinned'  => $data['is_pinned'] ?? false,
        ]);

        return $announcement->load('teacher.user')->toArray();
    }

    /**
     * Cập nhật thông báo.
     */
    public function update(array $data, int $announcementId): array
    {
        $teacher      = $this->currentTeacher();
        $announcement = ClassAnnouncement::where('id', $announcementId)
            ->where('teacher_id', $teacher->id)
            ->firstOrFail();

        $announcement->update(array_filter([
            'title'     => $data['title']     ?? null,
            'content'   => $data['content']   ?? null,
            'is_pinned' => $data['is_pinned'] ?? null,
        ], fn($v) => !is_null($v)));

        return $announcement->fresh()->toArray();
    }

    /**
     * Xóa thông báo.
     */
    public function delete(int $announcementId): int
    {
        $teacher      = $this->currentTeacher();
        $announcement = ClassAnnouncement::where('id', $announcementId)
            ->where('teacher_id', $teacher->id)
            ->firstOrFail();
        $id = $announcement->id;
        $announcement->delete();
        return $id;
    }
}
