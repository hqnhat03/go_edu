<?php

namespace App\Services;

use App\Exceptions\UserException;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\ExamResult;
use App\Models\ExamAnswerDetail;
use App\Models\Teacher;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExamService
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
     * Lấy exam và xác minh quyền sở hữu.
     */
    private function findOwnExam(int $examId): Exam
    {
        $teacher = $this->currentTeacher();
        $exam = Exam::where('id', $examId)->where('teacher_id', $teacher->id)->first();
        if (!$exam) {
            throw new UserException('Không tìm thấy bài kiểm tra hoặc bạn không có quyền chỉnh sửa.');
        }
        return $exam;
    }

    // ─── Exam CRUD ──────────────────────────────────────────────────

    /**
     * Danh sách bài kiểm tra của một lớp.
     */
    public function list(array $data): array
    {
        $teacher = $this->currentTeacher();

        $query = Exam::where('teacher_id', $teacher->id);

        if (isset($data['class_id'])) {
            $query->where('class_id', $data['class_id']);
        }

        return $query->with(['classRoom' => function ($q) {
            $q->select('id', 'class_code', 'course_id')->with('course:id,name');
        }])
            ->withCount('questions')
            ->withCount('results')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($exam) {
                $data = $exam->toArray();
                $data['class'] = [
                    'class_code' => $exam->classRoom->class_code ?? null,
                    'course_name' => $exam->classRoom->course->name ?? null,
                ];
                unset($data['class_room']);
                return $data;
            })
            ->toArray();
    }

    /**
     * Lấy danh sách bài kiểm tra của một lớp.
     */
    public function getByClass(int $classId): array
    {
        $teacher = $this->currentTeacher();

        $classRoom = \App\Models\ClassRoom::findOrFail($classId);
        $totalStudents = $classRoom->students()->count();

        $exams = Exam::where('teacher_id', $teacher->id)
            ->where('class_id', $classId)
            ->withCount('questions')
            ->withCount('results')
            ->orderBy('created_at', 'desc')
            ->get()
            ->makeHidden(['class_id', 'teacher_id', 'duration_minutes', 'open_at', 'close_at'])
            ->map(function ($exam) use ($classRoom) {
                $data = $exam->toArray();
                $data['class_code'] = $classRoom->class_code;
                return $data;
            })
            ->toArray();

        return [
            'total_students' => $totalStudents,
            'exams' => $exams,
        ];
    }

    /**
     * Chi tiết bài kiểm tra kèm câu hỏi.
     */
    public function detail(int $examId): array
    {
        $exam = $this->findOwnExam($examId);
        $exam->load(['questions', 'classRoom' => function ($q) {
            $q->select('id', 'class_code', 'course_id')->with('course:id,name');
        }]);
        $data = $exam->toArray();
        $data['class'] = [
            'class_code' => $exam->classRoom->class_code ?? null,
            'course_name' => $exam->classRoom->course->name ?? null,
        ];
        unset($data['class_room']);
        return $data;
    }

    /**
     * Tạo bài kiểm tra mới (có thể tạo kèm câu hỏi).
     */
    public function create(array $data): array
    {
        $teacher = $this->currentTeacher();

        $exam = Exam::create([
            'class_id' => $data['class_id'],
            'teacher_id' => $teacher->id,
            'name' => $data['name'],
            'duration_minutes' => $data['duration_minutes'] ?? 60,
            'open_at' => $data['open_at'] ?? null,
            'close_at' => $data['close_at'] ?? null,
            'status' => $data['status'] ?? 'draft',
        ]);
        return $exam->toArray();
    }

    /**
     * Cập nhật thông tin bài kiểm tra.
     */
    public function update(array $data, int $examId): array
    {
        $exam = $this->findOwnExam($examId);

        $exam->update(array_filter([
            'name' => $data['name'] ?? null,
            'duration_minutes' => $data['duration_minutes'] ?? null,
            'open_at' => $data['open_at'] ?? null,
            'close_at' => $data['close_at'] ?? null,
            'status' => $data['status'] ?? null,
        ], fn($v) => !is_null($v)));

        return $exam->fresh()->load('questions')->toArray();
    }

    /**
     * Xóa bài kiểm tra (cascade xóa câu hỏi và kết quả).
     */
    public function delete(int $examId): int
    {
        $exam = $this->findOwnExam($examId);
        $id = $exam->id;
        $exam->delete();
        return $id;
    }

    // ─── Questions ──────────────────────────────────────────────────



    /**
     * Lấy danh sách câu hỏi của bài kiểm tra.
     */
    public function getQuestions(int $examId): array
    {
        $exam = $this->findOwnExam($examId);
        return $exam->questions()->get()->toArray();
    }

    /**
     * Đồng bộ danh sách câu hỏi.
     */
    public function syncQuestions(array $questions, int $examId): array
    {
        $exam = $this->findOwnExam($examId);

        return DB::transaction(function () use ($exam, $questions) {
            $questionIds = [];

            foreach ($questions as $qData) {
                $question = ExamQuestion::updateOrCreate(
                    ['id' => $qData['id'], 'exam_id' => $exam->id],
                    [
                        'question' => $qData['question'],
                        'type' => $qData['type'] ?? 'multiple_choice',
                        'options' => $qData['options'] ?? null,
                        'correct_answer' => $qData['correct_answer'] ?? null,
                        'score' => $qData['score'] ?? 1,
                        'order_number' => $qData['order_number'] ?? 1,
                    ]
                );
                $questionIds[] = $question->id;
            }

            // Xóa các câu hỏi không còn trong danh sách
            ExamQuestion::where('exam_id', $exam->id)
                ->whereNotIn('id', $questionIds)
                ->delete();

            return $exam->fresh()->load('questions')->toArray();
        });
    }



    // ─── Results & Grading ──────────────────────────────────────────

    /**
     * Danh sách kết quả bài kiểm tra.
     */
    public function results(int $examId): array
    {
        $exam = $this->findOwnExam($examId);

        return ExamResult::where('exam_id', $exam->id)
            ->with('student.user:id,name,avatar,email')
            ->get()
            ->toArray();
    }

    /**
     * Danh sách tất cả học sinh trong lớp kèm kết quả bài làm (nếu có).
     */
    public function getStudents(int $examId): array
    {
        $exam = $this->findOwnExam($examId);

        // Tính tổng điểm tối đa của bài kiểm tra
        $totalScore = (float) $exam->questions()->sum('score');

        // Lấy tất cả học sinh của lớp
        $students = $exam->classRoom->students()
            ->with(['user:id,name,avatar,email'])
            ->get();

        // Lấy kết quả đã nộp của các học sinh cho bài kiểm tra này
        $results = ExamResult::where('exam_id', $exam->id)
            ->get()
            ->keyBy('student_id');

        $studentData = $students->map(function ($student) use ($results) {
            $result = $results->get($student->id);
            return [
                'id' => $student->id,
                'name' => $student->user->name,
                'avatar' => $student->user->avatar,
                'email' => $student->user->email,
                'result_id' => $result?->id,
                'score' => $result?->score,
                'status' => $result?->status ?? 'not_started',
                'submitted_at' => $result?->submitted_at,
            ];
        })->toArray();

        return [
            'total_score' => $totalScore,
            'students' => $studentData,
        ];
    }

    /**
     * Chấm điểm bài kiểm tra (bao gồm chấm tự luận từng câu).
     */
    public function grade(array $data, int $resultId): array
    {
        $teacher = $this->currentTeacher();

        $result = ExamResult::whereHas('exam', fn($q) => $q->where('teacher_id', $teacher->id))
            ->findOrFail($resultId);

        return DB::transaction(function () use ($data, $result, $teacher) {
            // 1. Cập nhật điểm chi tiết từng câu nếu có (thường dùng cho câu tự luận)
            if (isset($data['answers']) && is_array($data['answers'])) {
                foreach ($data['answers'] as $ans) {
                    ExamAnswerDetail::where('exam_result_id', $result->id)
                        ->where('question_id', $ans['question_id'])
                        ->update([
                            'score' => $ans['score'],
                            'teacher_comment' => $ans['teacher_comment'] ?? null,
                            'is_correct' => DB::raw($ans['score'] > 0 ? 'true' : 'false'), // Đánh dấu là đúng nếu có điểm
                        ]);
                }
            }

            // 2. Tính toán lại tổng điểm
            // Nếu frontend gửi lên score cụ thể thì dùng luôn, không thì cộng dồn từ details
            $totalScore = $data['score'] ?? $result->details()->sum('score');

            // 3. Cập nhật kết quả bài thi
            $result->update([
                'score' => $totalScore,
                'status' => 'completed',
                'graded_by' => $teacher->id,
                'graded_at' => now(),
            ]);

            return $result->fresh()->load(['student.user', 'details.question'])->toArray();
        });
    }

    /**
     * Lấy toàn bộ câu trả lời của một học sinh trong một bài kiểm tra.
     */
    public function studentAnswers(int $examId, int $studentId): array
    {
        $exam = $this->findOwnExam($examId);

        $result = ExamResult::where('exam_id', $exam->id)
            ->where('student_id', $studentId)
            ->with(['details.question'])
            ->first();

        if (!$result) {
            throw new UserException('Không tìm thấy kết quả làm bài của học sinh này.');
        }

        return $result->toArray();
    }

    // ─── Helpers ────────────────────────────────────────────────────


}
