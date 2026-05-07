<?php

namespace App\Services;

use App\Exceptions\UserException;
use App\Models\ClassSession;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\ExamResult;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class StudentPortalService
{
    /**
     * Lấy student record của user đang đăng nhập.
     */
    private function currentStudent(): Student
    {
        $student = Student::where('user_id', Auth::id())->first();
        if (!$student) {
            throw new UserException('Không tìm thấy thông tin học sinh.');
        }
        return $student;
    }

    // ─── Profile ────────────────────────────────────────────────────

    /**
     * Lấy thông tin profile của học sinh đang đăng nhập.
     */
    public function getProfile(): array
    {
        $student = $this->currentStudent()->load('user');

        return [
            ...$student->only(['id', 'student_type', 'school', 'grade', 'work', 'position']),
            ...$student->user->only(['name', 'email', 'phone', 'avatar', 'gender', 'date_of_birth', 'address']),
        ];
    }

    /**
     * Cập nhật profile (thông tin student + user).
     */
    public function updateProfile(array $data): array
    {
        $student = $this->currentStudent()->load('user');

        $student->user->update(array_filter([
            'name' => $data['name'] ?? null,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'gender' => $data['gender'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'avatar' => $data['avatar'] ?? null,
        ], fn($v) => !is_null($v)));

        $student->update(array_filter([
            'student_type' => $data['student_type'] ?? null,
            'school' => $data['school'] ?? null,
            'grade' => $data['grade'] ?? null,
            'work' => $data['work'] ?? null,
            'position' => $data['position'] ?? null,
        ], fn($v) => !is_null($v)));

        return $this->getProfile();
    }

    /**
     * Lịch học theo ngày.
     */
    public function getDailySchedules(array $params): array
    {
        $student = $this->currentStudent();
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
                'class_rooms.meeting_url',
                'courses.name as course_name'
            )
            ->orderBy('class_sessions.start_time', 'asc')
            ->get()
            ->toArray();
    }

    /**
     * Lịch học theo tuần.
     */
    public function getWeeklySchedules(array $params): array
    {
        $student = $this->currentStudent();
        $classIds = $student->classes()->pluck('class_rooms.id');

        $date = isset($params['date']) ? Carbon::parse($params['date']) : now();
        $startOfWeek = $date->copy()->startOfWeek()->toDateString();
        $endOfWeek = $date->copy()->endOfWeek()->toDateString();

        return ClassSession::query()
            ->join('class_rooms', 'class_sessions.class_id', '=', 'class_rooms.id')
            ->whereIn('class_sessions.class_id', $classIds)
            ->whereBetween('class_sessions.date', [$startOfWeek, $endOfWeek])
            ->select('class_sessions.*', 'class_rooms.class_code')
            ->orderBy('class_sessions.date', 'asc')
            ->orderBy('class_sessions.start_time', 'asc')
            ->get()
            ->toArray();
    }

    /**
     * Lấy danh sách lớp học sinh tham gia.
     */
    public function getMyClasses(): array
    {
        $student = $this->currentStudent();

        return $student->classes()
            ->with([
                'teachers:id,user_id',
                'teachers.user:id,name,avatar',
                'schedules',
                'course:id,name,image_url,subject_id',
                'course.subject:id,name'
            ])
            ->get()
            ->map(function ($class) {
                return [
                    'id' => $class->id,
                    'class_code' => $class->class_code,
                    'status' => $class->status,
                    'teachers' => $class->teachers->map(function ($teacher) {
                        return [
                            'id' => $teacher->id,
                            'name' => $teacher->user->name ?? null,
                            'avatar' => $teacher->user->avatar ?? null,
                        ];
                    }),
                    'schedules' => $class->schedules,
                    'course_name' => $class->course->name ?? null,
                    'image_url' => $class->course->image_url ?? null,
                    'subject_name' => $class->course->subject->name ?? null,
                ];
            })
            ->toArray();
    }

    /**
     * Lấy chi tiết lớp học.
     */
    public function getClassDetail(string $code): array
    {
        $student = $this->currentStudent();

        $class = $student->classes()
            ->where('class_code', $code)
            ->withCount(['students', 'teachers'])
            ->with([
                'teachers:id,user_id',
                'teachers.user:id,name,avatar',
                'schedules' => function ($query) {
                    $query->orderBy('day_of_week', 'asc');
                },
                'students.user:id,name,avatar',
            ])
            ->first();

        if (!$class) {
            throw new UserException('Không tìm thấy lớp học hoặc bạn không có quyền truy cập.');
        }

        return [
            'id' => $class->id,
            'class_code' => $class->class_code,
            'status' => $class->status,
            'meeting_url' => $class->meeting_url,
            'start_day' => $class->start_day,
            'end_day' => $class->end_day,
            'student_count' => $class->students_count,
            'teacher_count' => $class->teachers_count,
            'teachers' => $class->teachers->map(function ($teacher) {
                return [
                    'id' => $teacher->id,
                    'name' => $teacher->user->name ?? null,
                    'avatar' => $teacher->user->avatar ?? null,
                ];
            }),
            'schedules' => $class->schedules,
            'students' => $class->students->map(function ($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->user->name ?? null,
                    'avatar' => $student->user->avatar ?? null,
                ];
            })
        ];
    }

    /**
     * Lấy danh sách bài giảng của lớp.
     */
    public function getClassLectures(string $code): array
    {
        $student = $this->currentStudent();

        $class = $student->classes()
            ->where('class_code', $code)
            ->with([
                'lectures' => function ($query) {
                    $query->select('id', 'name', 'duration_time', 'lecture_number', 'class_id')
                        ->where('status', 'published')
                        ->orderBy('lecture_number', 'asc');
                }
            ])
            ->first();

        if (!$class) {
            throw new UserException('Không tìm thấy lớp học hoặc bạn không có quyền truy cập.');
        }

        return $class->lectures->map(function ($lecture) {
            return [
                'id' => $lecture->id,
                'name' => $lecture->name,
                'duration_time' => $lecture->duration_time,
                'lecture_number' => $lecture->lecture_number,
            ];
        })->toArray();
    }

    /**
     * Lấy chi tiết bài giảng.
     */
    public function getLectureDetail(string $code, int $id): array
    {
        $student = $this->currentStudent();

        $class = $student->classes()
            ->where('class_code', $code)
            ->first();

        if (!$class) {
            throw new UserException('Không tìm thấy lớp học hoặc bạn không có quyền truy cập.');
        }

        $lecture = $class->lectures()
            ->where('id', $id)
            ->where('status', 'published')
            ->first();

        if (!$lecture) {
            throw new UserException('Không tìm thấy bài giảng hoặc bài giảng chưa được công bố.');
        }

        return $lecture->toArray();
    }

    /**
     * Lấy danh sách bài kiểm tra của lớp.
     */
    public function getClassExams(string $code): array
    {
        $student = $this->currentStudent();

        $class = $student->classes()
            ->where('class_code', $code)
            ->first();

        if (!$class) {
            throw new UserException('Không tìm thấy lớp học hoặc bạn không có quyền truy cập.');
        }

        return Exam::query()
            ->where('class_id', $class->id)
            ->where('status', 'published')
            ->select('id', 'name', 'duration_minutes', 'open_at', 'close_at')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($exam) use ($student) {
                $result = ExamResult::where('exam_id', $exam->id)
                    ->where('student_id', $student->id)
                    ->first();

                return [
                    'id' => $exam->id,
                    'name' => $exam->name,
                    'duration_minutes' => $exam->duration_minutes,
                    'open_at' => $exam->open_at,
                    'close_at' => $exam->close_at,
                    'has_submitted' => $result !== null,
                    'result_id' => $result ? $result->id : null,
                    'result_status' => $result ? $result->status : null,
                    'score' => $result ? $result->score : null,
                ];
            })
            ->toArray();
    }

    /**
     * Lấy danh sách thông báo của lớp.
     */
    public function getClassAnnouncements(string $code): array
    {
        $student = $this->currentStudent();

        $class = $student->classes()
            ->where('class_code', $code)
            ->first();

        if (!$class) {
            throw new UserException('Không tìm thấy lớp học hoặc bạn không có quyền truy cập.');
        }

        return \App\Models\ClassAnnouncement::where('class_id', $class->id)
            ->with('teacher.user:id,name,avatar')
            ->orderBy('is_pinned', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Lấy danh sách câu hỏi của bài kiểm tra.
     */
    public function getExamQuestions(int $id): array
    {
        $student = $this->currentStudent();

        $exam = Exam::query()
            ->where('id', $id)
            ->where('status', 'published')
            ->with([
                'questions' => function ($query) {
                    $query->orderBy('order_number', 'asc');
                }
            ])
            ->first();

        if (!$exam) {
            throw new UserException('Không tìm thấy bài kiểm tra.');
        }

        // Kiểm tra xem học sinh có thuộc lớp này không
        $isEnrolled = $student->classes()->where('class_rooms.id', $exam->class_id)->exists();
        if (!$isEnrolled) {
            throw new UserException('Bạn không có quyền truy cập bài kiểm tra này.');
        }

        // Kiểm tra thời gian làm bài
        $now = now();
        if ($exam->open_at && $now->lt($exam->open_at)) {
            throw new UserException('Bài kiểm tra chưa mở. Thời gian mở: ' . $exam->open_at->format('H:i d/m/Y'));
        }

        if ($exam->close_at && $now->gt($exam->close_at)) {
            throw new UserException('Bài kiểm tra đã kết thúc vào lúc: ' . $exam->close_at->format('H:i d/m/Y'));
        }

        return [
            'exam' => [
                'id' => $exam->id,
                'name' => $exam->name,
                'duration_minutes' => $exam->duration_minutes,
                'open_at' => $exam->open_at,
                'close_at' => $exam->close_at,
            ],
            'questions' => $exam->questions->map(function ($q) {
                return [
                    'id' => $q->id,
                    'question' => $q->question,
                    'type' => $q->type,
                    'options' => $q->options,
                    'score' => $q->score,
                    'order_number' => $q->order_number,
                ];
            })
        ];
    }

    /**
     * Nộp bài kiểm tra.
     */
    public function submitExam(int $id, array $answers): array
    {
        $student = $this->currentStudent();

        $exam = Exam::query()
            ->where('id', $id)
            ->where('status', 'published')
            ->with('questions')
            ->first();

        if (!$exam) {
            throw new UserException('Không tìm thấy bài kiểm tra.');
        }

        // Kiểm tra quyền truy cập
        $isEnrolled = $student->classes()->where('class_rooms.id', $exam->class_id)->exists();
        if (!$isEnrolled) {
            throw new UserException('Bạn không có quyền nộp bài kiểm tra này.');
        }

        // Kiểm tra thời gian làm bài
        $now = now();
        if ($exam->open_at && $now->lt($exam->open_at)) {
            throw new UserException('Bài kiểm tra chưa mở.');
        }
        if ($exam->close_at && $now->gt($exam->close_at)) {
            throw new UserException('Bài kiểm tra đã kết thúc.');
        }

        // Kiểm tra xem đã nộp chưa
        $existingResult = ExamResult::where('exam_id', $id)
            ->where('student_id', $student->id)
            ->first();

        if ($existingResult) {
            throw new UserException('Bạn đã nộp bài kiểm tra này rồi.');
        }

        // Chấm bài & chuẩn bị dữ liệu chi tiết
        $totalScore = 0;
        $answerDetails = [];
        $hasEssay = false;

        foreach ($exam->questions as $question) {
            $studentAnswer = $answers[$question->id] ?? null;
            $correctAnswer = $question->correct_answer;
            $questionScore = 0;
            $isCorrect = null;

            if ($question->type === 'essay') {
                $hasEssay = true;
            } else {
                // Chấm tự động cho trắc nghiệm
                if ($studentAnswer !== null) {
                    // 1. So sánh trực tiếp giá trị (hoặc mảng giá trị nếu là multiple select)
                    if (is_array($correctAnswer)) {
                        if (is_array($studentAnswer)) {
                            sort($studentAnswer);
                            sort($correctAnswer);
                            if ($studentAnswer == $correctAnswer) {
                                $questionScore = $question->score;
                                $isCorrect = true;
                            } else {
                                $isCorrect = false;
                            }
                        }
                    } else {
                        // 2. So sánh chuỗi trực tiếp
                        if ((string) $studentAnswer === (string) $correctAnswer) {
                            $questionScore = $question->score;
                            $isCorrect = true;
                        } else {
                            $isCorrect = false;
                        }
                    }
                } else {
                    $isCorrect = false;
                }
                $totalScore += $questionScore;
            }

            $answerDetails[] = [
                'question_id' => $question->id,
                'answer_content' => is_array($studentAnswer) ? json_encode($studentAnswer) : $studentAnswer,
                'score' => $questionScore,
                'is_correct' => $isCorrect,
            ];
        }

        // Lưu kết quả
        return DB::transaction(function () use ($exam, $student, $answers, $totalScore, $hasEssay, $answerDetails, $now) {
            $result = ExamResult::create([
                'exam_id' => $exam->id,
                'student_id' => $student->id,
                'answers' => $answers,
                'score' => $totalScore,
                'status' => $hasEssay ? 'grading' : 'completed',
                'submitted_at' => $now,
            ]);

            $insertData = [];
            foreach ($answerDetails as $detail) {
                $detail['exam_result_id'] = $result->id;
                $detail['created_at'] = $now;
                $detail['updated_at'] = $now;

                if ($detail['is_correct'] === true) {
                    $detail['is_correct'] = DB::raw('true');
                } elseif ($detail['is_correct'] === false) {
                    $detail['is_correct'] = DB::raw('false');
                }

                $insertData[] = $detail;
            }
            if (!empty($insertData)) {
                DB::table('exam_answer_details')->insert($insertData);
            }

            return [
                'id' => $result->id,
                'score' => $result->score,
                'status' => $result->status,
                'submitted_at' => $result->submitted_at->toDateTimeString(),
            ];
        });
    }

    /**
     * Xem kết quả bài kiểm tra.
     */
    public function getExamResult(int $examId): array
    {
        $student = $this->currentStudent();

        $result = ExamResult::where('exam_id', $examId)
            ->where('student_id', $student->id)
            ->with(['exam', 'details.question'])
            ->first();

        if (!$result) {
            throw new UserException('Bạn chưa làm bài kiểm tra này hoặc không tìm thấy kết quả.');
        }

        return [
            'exam' => [
                'id' => $result->exam->id,
                'name' => $result->exam->name,
                'duration_minutes' => $result->exam->duration_minutes,
            ],
            'result' => [
                'id' => $result->id,
                'score' => $result->score,
                'status' => $result->status,
                'submitted_at' => $result->submitted_at->toDateTimeString(),
                'graded_at' => $result->graded_at ? $result->graded_at->toDateTimeString() : null,
            ],
            'details' => $result->details->map(function ($detail) {
                // Decode answer_content if it's a JSON string (for multiple choice)
                $answerContent = $detail->answer_content;
                if ($detail->question && ($detail->question->type === 'multiple_choice' || $detail->question->type === 'multiple_select')) {
                    $decoded = json_decode($answerContent, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $answerContent = $decoded;
                    }
                }

                $correctAnswer = $detail->question ? $detail->question->correct_answer : null;
                // Phòng trường hợp model cast không xử lý được JSON string thuần tuý
                if ($detail->question && is_string($correctAnswer) && ($detail->question->type === 'multiple_choice' || $detail->question->type === 'multiple_select')) {
                    $decodedCorrect = json_decode($correctAnswer, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $correctAnswer = $decodedCorrect;
                    }
                }

                return [
                    'id' => $detail->id,
                    'question' => $detail->question ? [
                        'id' => $detail->question->id,
                        'question' => $detail->question->question,
                        'type' => $detail->question->type,
                        'options' => $detail->question->options,
                        'correct_answer' => $correctAnswer,
                        'score' => $detail->question->score,
                        'order_number' => $detail->question->order_number,
                    ] : null,
                    'answer_content' => $answerContent,
                    'score' => $detail->score,
                    'is_correct' => $detail->is_correct,
                    'teacher_comment' => $detail->teacher_comment,
                ];
            })->sortBy(function ($detail) {
                return $detail['question'] ? $detail['question']['order_number'] : 9999;
            })->values()
        ];
    }

    /**
     * Lấy danh sách tất cả các bài kiểm tra đã có kết quả của học sinh.
     */
    public function getMyExamResults(): array
    {
        $student = $this->currentStudent();

        return ExamResult::where('student_id', $student->id)
            ->with(['exam:id,name,class_id', 'exam.classRoom:id,class_code'])
            ->orderBy('submitted_at', 'desc')
            ->get()
            ->map(function ($result) {
                return [
                    'id'           => $result->id,
                    'exam_id'      => $result->exam_id,
                    'exam_name'    => $result->exam->name ?? null,
                    'class_code'   => $result->exam->classRoom->class_code ?? null,
                    'score'        => $result->score,
                    'status'       => $result->status,
                    'submitted_at' => $result->submitted_at ? $result->submitted_at->toDateTimeString() : null,
                    'graded_at'    => $result->graded_at ? $result->graded_at->toDateTimeString() : null,
                ];
            })
            ->toArray();
    }
}
