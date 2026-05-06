<?php

namespace App\Services;

use App\Exceptions\UserException;
use App\Models\ClassAnnouncement;
use App\Models\ClassRoom;
use App\Models\Teacher;
use App\Notifications\ClassAnnouncementCreated;
use DB;
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
            ->map(function ($item) {
                $item->is_pinned = (bool)$item->is_pinned;
                return $item;
            })
            ->toArray();
    }

    /**
     * Tạo thông báo mới.
     */
    public function create(array $data, int $classId): array
    {
        $teacher = $this->currentTeacher();

        $announcement = ClassAnnouncement::create([
            'class_id' => $classId,
            'teacher_id' => $teacher->id,
            'title' => $data['title'],
            'content' => $data['content'],
            'is_pinned' => $data['is_pinned'] ? DB::raw('true') : DB::raw('false')
        ]);

        $announcement->load(['classRoom', 'teacher.user']);


        // Gửi thông báo cho toàn bộ học sinh trong lớp
        $students = ClassRoom::find($classId)->students()->with('user')->get();
        foreach ($students as $student) {
            if ($student->user) {
                $student->user->notify(new ClassAnnouncementCreated($announcement));
            }
        }

        $announcement = $announcement->fresh();
        $announcement->load(['teacher.user:id,name,avatar']);
        
        $result = $announcement->toArray();
        $result['is_pinned'] = (bool)$result['is_pinned'];

        return $result;
    }

    /**
     * Cập nhật thông báo.
     */
    public function update(array $data, int $announcementId): array
    {
        $teacher = $this->currentTeacher();
        $announcement = ClassAnnouncement::where('id', $announcementId)
            ->where('teacher_id', $teacher->id)
            ->firstOrFail();

        // Tạo mảng dữ liệu cần cập nhật
        $announcement->update([
            'title' => $data['title'] ?? $announcement->title,
            'content' => $data['content'] ?? $announcement->content,
            'is_pinned' => ($data['is_pinned'] ?? $announcement->is_pinned) ? DB::raw('true') : DB::raw('false'),
        ]);

        $announcement = $announcement->fresh();
        $announcement->load(['teacher.user:id,name,avatar']);
        
        $result = $announcement->toArray();
        $result['is_pinned'] = (bool)$result['is_pinned'];

        return $result;
    }

    /**
     * Xóa thông báo.
     */
    public function delete(int $announcementId): int
    {
        $teacher = $this->currentTeacher();
        $announcement = ClassAnnouncement::where('id', $announcementId)
            ->where('teacher_id', $teacher->id)
            ->firstOrFail();
        $id = $announcement->id;
        $announcement->delete();
        return $id;
    }
}
