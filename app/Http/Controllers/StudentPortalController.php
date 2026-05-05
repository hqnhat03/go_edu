<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Services\StudentPortalService;
use Illuminate\Http\Request;

class StudentPortalController extends Controller
{
    public function __construct(private StudentPortalService $service) {}

    /** GET /student/profile */
    public function getProfile()
    {
        $data = $this->service->getProfile();
        return ApiResponse::success($data);
    }

    /** PUT /student/profile */
    public function updateProfile(Request $request)
    {
        $data = $this->service->updateProfile($request->all());
        return ApiResponse::success($data, 'Cập nhật thông tin thành công');
    }

    /** GET /student/schedules/day */
    public function dailySchedules(Request $request)
    {
        $data = $this->service->getDailySchedules($request->all());
        return ApiResponse::success($data);
    }

    /** GET /student/schedules/week */
    public function weeklySchedules(Request $request)
    {
        $data = $this->service->getWeeklySchedules($request->all());
        return ApiResponse::success($data);
    }

    /** GET /student/classes */
    public function myClasses()
    {
        $data = $this->service->getMyClasses();
        return ApiResponse::success($data);
    }

    /** GET /student/classes/{code} */
    public function classDetail($code)
    {
        $data = $this->service->getClassDetail($code);
        return ApiResponse::success($data);
    }

    /** GET /student/classes/{code}/lectures */
    public function classLectures($code)
    {
        $data = $this->service->getClassLectures($code);
        return ApiResponse::success($data);
    }

    /** GET /student/classes/{code}/lectures/{id} */
    public function lectureDetail($code, $id)
    {
        $data = $this->service->getLectureDetail($code, (int)$id);
        return ApiResponse::success($data);
    }

    /** GET /student/classes/{code}/exams */
    public function classExams($code)
    {
        $data = $this->service->getClassExams($code);
        return ApiResponse::success($data);
    }

    /** GET /student/exams/{id}/questions */
    public function examQuestions($id)
    {
        $data = $this->service->getExamQuestions((int)$id);
        return ApiResponse::success($data);
    }

    /** 
     * POST /student/exams/{id}/submit
     * Sample Body:
     * {
     *   "answers": {
     *     "9be75737-1473-4556-91af-b06226c637ef": "A",
     *     "9be75737-1473-4556-91af-b06226c637f0": ["A", "B"],
     *     "9be75737-1473-4556-91af-b06226c637f1": "Nội dung câu trả lời tự luận"
     *   }
     * }
     */
    public function submitExam(Request $request, $id)
    {
        $data = $this->service->submitExam((int)$id, $request->get('answers', []));
        return ApiResponse::success($data, 'Nộp bài thi thành công.');
    }

    /** GET /student/exams/{id}/result */
    public function examResult($id)
    {
        $data = $this->service->getExamResult((int)$id);
        return ApiResponse::success($data);
    }

    /** GET /student/exams/results */
    public function myExamResults()
    {
        $data = $this->service->getMyExamResults();
        return ApiResponse::success($data);
    }
}
