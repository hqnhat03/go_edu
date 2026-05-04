<?php

namespace App\Services;

use App\Exceptions\UserException;
use App\Models\ClassAnnouncement;
use App\Models\StudentEvaluation;
use App\Models\Teacher;
use Illuminate\Support\Facades\Auth;

class EvaluationService
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
     * Danh sách đánh giá học sinh trong lớp.
     */
    public function list(int $classId): array
    {
        return StudentEvaluation::where('class_id', $classId)
            ->with('student.user:id,name,avatar,email')
            ->with('teacher.user:id,name,avatar')
            ->orderBy('evaluated_at', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Tạo đánh giá học sinh.
     */
    public function create(array $data, int $classId): array
    {
        $teacher = $this->currentTeacher();

        $evaluation = StudentEvaluation::create([
            'class_id'     => $classId,
            'student_id'   => $data['student_id'],
            'teacher_id'   => $teacher->id,
            'type'         => $data['type'] ?? 'midterm',
            'score'        => $data['score'],
            'comment'      => $data['comment'] ?? null,
            'evaluated_at' => $data['evaluated_at'] ?? now()->toDateString(),
        ]);

        return $evaluation->load(['student.user', 'teacher.user'])->toArray();
    }

    /**
     * Cập nhật đánh giá học sinh.
     */
    public function update(array $data, int $evaluationId): array
    {
        $teacher    = $this->currentTeacher();
        $evaluation = StudentEvaluation::where('id', $evaluationId)
            ->where('teacher_id', $teacher->id)
            ->firstOrFail();

        $evaluation->update(array_filter([
            'score'        => $data['score']        ?? null,
            'comment'      => $data['comment']      ?? null,
            'type'         => $data['type']         ?? null,
            'evaluated_at' => $data['evaluated_at'] ?? null,
        ], fn($v) => !is_null($v)));

        return $evaluation->fresh()->toArray();
    }

    /**
     * Xóa đánh giá.
     */
    public function delete(int $evaluationId): int
    {
        $teacher    = $this->currentTeacher();
        $evaluation = StudentEvaluation::where('id', $evaluationId)
            ->where('teacher_id', $teacher->id)
            ->firstOrFail();
        $id = $evaluation->id;
        $evaluation->delete();
        return $id;
    }
}
