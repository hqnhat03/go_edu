<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Services\ExamService;
use App\Http\Requests\Exam\CreateRequest;
use App\Http\Requests\Exam\UpdateRequest;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public function __construct(private ExamService $service)
    {
    }

    /** GET /teacher/exams */
    public function index(Request $request)
    {
        $data = $this->service->list($request->all());
        return ApiResponse::success($data);
    }

    /** GET /teacher/classes/{classId}/exams */
    public function getByClass($classId)
    {
        $data = $this->service->getByClass((int) $classId);
        return ApiResponse::success($data);
    }

    /** POST /teacher/exams */
    public function store(CreateRequest $request)
    {
        $data = $this->service->create($request->validated());
        return ApiResponse::success($data, 'Tạo bài kiểm tra thành công', [], 201);
    }

    /** GET /teacher/exams/{id} */
    public function show($id)
    {
        $data = $this->service->detail((int) $id);
        return ApiResponse::success($data);
    }

    /** PUT /teacher/exams/{id} */
    public function update(UpdateRequest $request, $id)
    {
        $data = $this->service->update($request->validated(), (int) $id);
        return ApiResponse::success($data, 'Cập nhật bài kiểm tra thành công');
    }

    /** DELETE /teacher/exams/{id} */
    public function destroy($id)
    {
        $data = $this->service->delete((int) $id);
        return ApiResponse::success($data, 'Xóa bài kiểm tra thành công');
    }

    // ─── Questions ──────────────────────────────────────────────────

    /** GET /teacher/exams/{examId}/questions */
    public function getQuestions($examId)
    {
        $data = $this->service->getQuestions((int) $examId);
        return ApiResponse::success($data);
    }

    /** PUT /teacher/exams/{examId}/questions */
    public function syncQuestions(Request $request, $examId)
    {
        $request->validate([
            'questions' => 'present|array',
            'questions.*.id' => 'required|uuid',
            'questions.*.question' => 'required|string',
            'questions.*.type' => 'nullable|in:multiple_choice,essay',
            'questions.*.options' => 'nullable|array',
            'questions.*.correct_answer' => 'nullable|string',
            'questions.*.score' => 'nullable|numeric|min:0',
            'questions.*.order_number' => 'nullable|integer|min:0',
        ]);

        $data = $this->service->syncQuestions($request->input('questions'), (int) $examId);
        return ApiResponse::success($data, 'Cập nhật danh sách câu hỏi thành công');
    }



    // ─── Results & Grading ──────────────────────────────────────────

    /** GET /teacher/exams/{id}/results */
    public function results($id)
    {
        $data = $this->service->results((int) $id);
        return ApiResponse::success($data);
    }

    /** GET /teacher/exams/{id}/students */
    public function getStudents($id)
    {
        $data = $this->service->getStudents((int) $id);
        return ApiResponse::success($data);
    }

    /** GET /teacher/exams/{examId}/students/{studentId}/answers */
    public function studentAnswers($examId, $studentId)
    {
        $data = $this->service->studentAnswers((int) $examId, (int) $studentId);
        return ApiResponse::success($data);
    }

    /** PUT /teacher/results/{id}/grade */
    public function grade(Request $request, $id)
    {
        $request->validate([
            'score' => 'nullable|numeric|min:0',
            'answers' => 'nullable|array',
            'answers.*.question_id' => 'required|uuid|exists:exam_questions,id',
            'answers.*.score' => 'required|numeric|min:0',
            'answers.*.teacher_comment' => 'nullable|string',
        ]);

        $data = $this->service->grade($request->all(), (int) $id);
        return ApiResponse::success($data, 'Chấm điểm thành công');
    }
}
