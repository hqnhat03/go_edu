<?php

namespace App\Services;

use App\Exceptions\UserException;
use App\Models\ClassAnnouncement;
use App\Models\StudentEvaluation;
use App\Models\Teacher;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
     * Trả về tất cả học sinh trong lớp kèm đánh giá (nếu có).
     */
    public function list(int $classId): array
    {
        $this->currentTeacher(); // Kiểm tra quyền giáo viên

        // Lấy tất cả học sinh trong lớp
        $students = DB::table('class_students')
            ->join('students', 'class_students.student_id', '=', 'students.id')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->where('class_students.class_id', $classId)
            ->select('students.id', 'users.name', 'users.avatar', 'users.email')
            ->get();

        // Lấy danh sách đánh giá đã có trong lớp này
        $evaluations = StudentEvaluation::where('class_id', $classId)
            ->with(['teacher.user:id,name,avatar'])
            ->get()
            ->keyBy('student_id');

        return $students->map(function ($student) use ($evaluations) {
            $eval = $evaluations->get($student->id);
            return [
                'id'           => $eval->id ?? null,
                'rating'       => $eval->rating ?? null,
                'comment'      => $eval->comment ?? null,
                'created_at'   => $eval ? $eval->created_at->format('Y-m-d\TH:i:s\Z') : null,
                'is_evaluated' => (bool)$eval,
                'student'      => [
                    'id'     => $student->id,
                    'name'   => $student->name,
                    'avatar' => $student->avatar,
                    'email'  => $student->email,
                ],
                'teacher'      => $eval ? [
                    'id'     => $eval->teacher->id,
                    'name'   => $eval->teacher->user->name ?? null,
                    'avatar' => $eval->teacher->user->avatar ?? null,
                ] : null,
            ];
        })->toArray();
    }

    /**
     * Tạo đánh giá học sinh.
     */
    public function create(array $data, int $classId): array
    {
        $teacher = $this->currentTeacher();

        // Kiểm tra xem đã đánh giá chưa
        $exists = StudentEvaluation::where('class_id', $classId)
            ->where('student_id', $data['student_id'])
            ->exists();

        if ($exists) {
            throw new UserException('Học sinh này đã được đánh giá trong lớp này.');
        }

        $evaluation = StudentEvaluation::create([
            'class_id'   => $classId,
            'student_id' => $data['student_id'],
            'teacher_id' => $teacher->id,
            'rating'     => $data['rating'],
            'comment'    => $data['comment'] ?? null,
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
            'rating'  => $data['rating']  ?? null,
            'comment' => $data['comment'] ?? null,
        ], fn($v) => !is_null($v)));

        return $evaluation->fresh()->load(['student.user', 'teacher.user'])->toArray();
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
