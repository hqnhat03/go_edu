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
    public function list(int $classId, array $params): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $this->authorizeClass($classId);

        $sortBy = $params['sort_by'] ?? 'lecture_number';
        $sortOrder = $params['sort_order'] ?? 'asc';
        $perPage = $params['per_page'] ?? 10;

        $query = Lecture::where('class_id', $classId);

        if (!empty($params['name'])) {
            $query->where('name', 'like', '%' . $params['name'] . '%');
        }

        if (!empty($params['status'])) {
            $query->where('status', $params['status']);
        }

        return $query->select('id', 'name', 'duration_time', 'lecture_number', 'status')
            ->orderBy($sortBy, $sortOrder)
            ->paginate($perPage);
    }

    /**
     * Danh sách buổi học của một lớp (dành cho Admin).
     */
    public function listByAdmin(int $classId, array $params): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $sortBy = $params['sort_by'] ?? 'lecture_number';
        $sortOrder = $params['sort_order'] ?? 'asc';
        $perPage = $params['per_page'] ?? 10;

        $query = Lecture::where('class_id', $classId);

        if (!empty($params['name'])) {
            $query->where('name', 'like', '%' . $params['name'] . '%');
        }

        if (!empty($params['status'])) {
            $query->where('status', $params['status']);
        }

        return $query->select('id', 'name', 'duration_time', 'lecture_number', 'status')
            ->orderBy($sortBy, $sortOrder)
            ->paginate($perPage);
    }

    /**
     * Lấy chi tiết buổi học.
     */
    public function get(int $lectureId): array
    {
        $lecture = $this->findOwnLecture($lectureId);
        return $lecture->only(['id', 'name', 'duration_time', 'lecture_number', 'document_url', 'video_url', 'description', 'status']);
    }

    /**
     * Tạo buổi học mới.
     */
    public function create(array $data, int $classId): array
    {
        $this->authorizeClass($classId);

        $lecture = Lecture::create([
            'class_id' => $classId,
            'name' => $data['name'],
            'lecture_number' => $data['lecture_number'],
            'duration_time' => $data['duration_time'],
            'document_url' => $data['document_url'] ?? null,
            'video_url' => $data['video_url'] ?? null,
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 'pending',
        ]);

        return $lecture->toArray();
    }

    /**
     * Tạo buổi học mới (dành cho Admin).
     */
    public function createByAdmin(array $data, int $classId): array
    {
        $lecture = Lecture::create([
            'class_id' => $classId,
            'name' => $data['name'],
            'lecture_number' => $data['lecture_number'],
            'duration_time' => $data['duration_time'],
            'document_url' => $data['document_url'] ?? null,
            'video_url' => $data['video_url'] ?? null,
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 'published',
        ]);

        return $lecture->toArray();
    }

    /**
     * Import bài giảng từ file CSV.
     */
    public function import($file, int $classId): array
    {
        $this->authorizeClass($classId);

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
                    'name' => trim($data['name'] ?? ''),
                    'lecture_number' => (int) ($data['lecture_number'] ?? 0),
                    'duration_time' => (int) ($data['duration_time'] ?? 0),
                    'document_url' => !empty($data['document_url']) ? trim($data['document_url']) : 'test',
                    'video_url' => trim($data['video_url'] ?? ''),
                    'description' => trim($data['description'] ?? ''),
                    'status' => 'pending',
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
     * Cập nhật buổi học (dành cho Admin).
     */
    public function updateByAdmin(array $data, int $lectureId): array
    {
        $lecture = Lecture::findOrFail($lectureId);

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
     * Xóa buổi học (dành cho Admin).
     */
    public function deleteByAdmin(int $lectureId): int
    {
        $lecture = Lecture::findOrFail($lectureId);
        $id = $lecture->id;
        $lecture->delete();
        return $id;
    }

    /**
     * Cập nhật trạng thái hàng loạt (dành cho Admin).
     */
    public function bulkUpdateStatus(array $ids, string $status): void
    {
        Lecture::whereIn('id', $ids)->update(['status' => $status]);
    }

    /**
     * Lấy chi tiết buổi học (dành cho Admin).
     */
    public function getByAdmin(int $lectureId): array
    {
        return Lecture::findOrFail($lectureId, [
            'id', 
            'name', 
            'duration_time', 
            'lecture_number', 
            'document_url', 
            'video_url', 
            'description', 
            'status'
        ])->toArray();
    }

    /**
     * Lấy buổi học và xác minh quyền sở hữu.
     */
    private function findOwnLecture(int $lectureId): Lecture
    {
        $lecture = Lecture::findOrFail($lectureId);
        $this->authorizeClass($lecture->class_id);
        
        return $lecture;
    }
}
