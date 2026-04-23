<?php

namespace App\Services;

use App\Exceptions\UserException;
use App\Models\ClassRoom;
use App\Models\Lecture;
use App\Models\Teacher;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LectureService
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
     * Kiểm tra giáo viên có quyền quản lý lớp này không.
     */
    private function authorizeClass(int $classId): ClassRoom
    {
        $teacher = $this->currentTeacher();
        $class = $teacher->teachingClasses()->where('class_rooms.id', $classId)->first();
        if (!$class) {
            throw new UserException('Bạn không có quyền truy cập lớp học này.');
        }
        return $class;
    }

    // ─── CRUD ───────────────────────────────────────────────────────

    /**
     * Danh sách buổi học của một lớp.
     */
    public function list(int $classId): array
    {
        $this->authorizeClass($classId);

        return Lecture::where('class_id', $classId)
            ->orderBy('lecture_number')
            ->get()
            ->toArray();
    }

    /**
     * Tạo buổi học mới.
     */
    public function create(array $data, int $classId): array
    {
        $this->authorizeClass($classId);
        $teacher = $this->currentTeacher();

        $lecture = Lecture::create([
            'class_id' => $classId,
            'teacher_id' => $teacher->id,
            'name' => $data['name'],
            'lecture_number' => $data['lecture_number'],
            'duration_time' => $data['duration_time'],
            'document_url' => $data['document_url'] ?? null,
            'video_url' => $data['video_url'] ?? null,
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 'draft',
        ]);

        return $lecture->toArray();
    }

    /**
     * Import bài giảng từ file CSV.
     */
    public function import($file, int $classId): array
    {
        $this->authorizeClass($classId);
        $teacher = $this->currentTeacher();

        $path = $file->getRealPath();
        $handle = fopen($path, 'r');
        if (!$handle) {
            throw new UserException('Không thể mở file CSV.');
        }

        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);
            throw new UserException('File CSV trống hoặc không đúng định dạng.');
        }

        $headers = array_map('trim', $headers);
        $lectures = [];

        try {
            DB::beginTransaction();

            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) < count($headers)) {
                    continue;
                }

                $data = array_combine($headers, array_slice($row, 0, count($headers)));

                $lecture = Lecture::create([
                    'class_id' => $classId,
                    'teacher_id' => $teacher->id,
                    'name' => trim($data['name'] ?? ''),
                    'lecture_number' => (int) ($data['lecture_number'] ?? 0),
                    'duration_time' => (int) ($data['duration_time'] ?? 0),
                    'document_url' => !empty($data['document_url']) ? trim($data['document_url']) : 'test',
                    'video_url' => trim($data['video_url'] ?? ''),
                    'description' => trim($data['description'] ?? ''),
                    'status' => 'published',
                ]);

                $lectures[] = $lecture->toArray();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            fclose($handle);
            throw $e;
        }

        fclose($handle);
        return $lectures;
    }

    /**
     * Cập nhật buổi học.
     */
    public function update(array $data, int $lectureId): array
    {
        $lecture = $this->findOwnLecture($lectureId);

        $lecture->update(array_filter([
            'name' => $data['name'] ?? null,
            'lecture_number' => $data['lecture_number'] ?? null,
            'duration_time' => $data['duration_time'] ?? null,
            'document_url' => $data['document_url'] ?? null,
            'video_url' => $data['video_url'] ?? null,
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? null,
        ], fn($v) => !is_null($v)));

        return $lecture->fresh()->toArray();
    }

    /**
     * Xóa buổi học.
     */
    public function delete(int $lectureId): int
    {
        $lecture = $this->findOwnLecture($lectureId);
        $id = $lecture->id;
        $lecture->delete();
        return $id;
    }

    /**
     * Lấy buổi học và xác minh quyền sở hữu.
     */
    private function findOwnLecture(int $lectureId): Lecture
    {
        $teacher = $this->currentTeacher();
        $lecture = Lecture::where('id', $lectureId)
            ->where('teacher_id', $teacher->id)
            ->first();

        if (!$lecture) {
            throw new UserException('Không tìm thấy buổi học hoặc bạn không có quyền chỉnh sửa.');
        }
        return $lecture;
    }
}
