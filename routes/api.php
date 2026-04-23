<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClassRoomController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\FilterController;
use App\Http\Controllers\GuardianController;
use App\Http\Controllers\LectureController;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\NewController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SkillLevelController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\TeacherPortalController;
use App\Http\Controllers\StudentPortalController;
use App\Http\Controllers\ClientCourseController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\StorageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('/auth')->group(function () {
    Route::middleware(['check.domain'])->post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
    Route::post('/resend-verification', [AuthController::class, 'resendVerification']);
    Route::post('/refresh-token', [AuthController::class, 'refreshToken']);

    Route::middleware(['auth:api'])->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
    });
});


Route::middleware(['auth:api'])->prefix('/admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::prefix('/teachers')->group(function () {
        Route::middleware(['permission:teacher_list'])->get('/', [TeacherController::class, 'index']);
        Route::middleware(['permission:teacher_create'])->post('/', [TeacherController::class, 'store']);
        Route::middleware(['permission:teacher_detail'])->get('/{id}', [TeacherController::class, 'show']);
        Route::middleware(['permission:teacher_edit'])->put('/{id}', [TeacherController::class, 'update']);
        Route::middleware(['permission:teacher_delete'])->delete('/{id}', [TeacherController::class, 'destroy']);
    });

    Route::prefix('/students')->group(function () {
        Route::middleware(['permission:student_list'])->get('/all-students', [StudentController::class, 'getAllStudent']);
        Route::middleware(['permission:student_list'])->get('/', [StudentController::class, 'index']);
        Route::middleware(['permission:student_create'])->post('/', [StudentController::class, 'store']);
        Route::middleware(['permission:student_detail'])->get('/{id}', [StudentController::class, 'show']);
        Route::middleware(['permission:student_edit'])->put('/{id}', [StudentController::class, 'update']);
        Route::middleware(['permission:student_edit'])->post('/{id}/enroll-course', [StudentController::class, 'enrollCourse']);
        Route::middleware(['permission:student_delete'])->delete('/{id}', [StudentController::class, 'destroy']);
    });

    Route::prefix('/subjects')->group(function () {
        Route::middleware(['permission:subject_list'])->get('/', [SubjectController::class, 'index']);
        Route::middleware(['permission:subject_create'])->post('/', [SubjectController::class, 'store']);
        Route::middleware(['permission:subject_edit'])->put('/{id}', [SubjectController::class, 'update']);
        Route::middleware(['permission:subject_delete'])->delete('/{id}', [SubjectController::class, 'destroy']);
        Route::middleware(['permission:subject_list'])->get('/categories', [SubjectController::class, 'getAllCategory']);
    });

    Route::prefix('/levels')->group(function () {
        Route::middleware(['permission:level_list'])->get('/', [LevelController::class, 'index']);
        Route::middleware(['permission:level_create'])->post('/', [LevelController::class, 'store']);
        Route::middleware(['permission:level_edit'])->put('/{id}', [LevelController::class, 'update']);
        Route::middleware(['permission:level_delete'])->delete('/{id}', [LevelController::class, 'destroy']);
        Route::middleware(['permission:level_list'])->get('/education-levels', [LevelController::class, 'getAllEducationLevel']);
    });

    Route::prefix('/roles')->group(function () {
        Route::middleware(['permission:role_list'])->get('/', [RoleController::class, 'listRole']);
        Route::middleware(['permission:role_create'])->post('/', [RoleController::class, 'createRole']);
        Route::middleware(['permission:role_edit'])->put('/{id}', [RoleController::class, 'updateRole']);
        Route::middleware(['permission:role_delete'])->delete('/{id}', [RoleController::class, 'deleteRole']);
    });

    Route::prefix('/guardians')->group(function () {
        Route::middleware(['permission:guardian_list'])->get('/', [GuardianController::class, 'index']);
        Route::middleware(['permission:guardian_create'])->post('/', [GuardianController::class, 'store']);
        Route::middleware(['permission:guardian_detail'])->get('/{id}', [GuardianController::class, 'show']);
        Route::middleware(['permission:guardian_edit'])->put('/{id}', [GuardianController::class, 'update']);
        Route::middleware(['permission:guardian_delete'])->delete('/{id}', [GuardianController::class, 'destroy']);
    });

    Route::prefix('/courses')->group(function () {
        Route::middleware(['permission:course_list'])->get('/', [CourseController::class, 'index']);
        Route::middleware(['permission:course_create'])->post('/', [CourseController::class, 'store']);
        Route::middleware(['permission:course_detail'])->get('/{id}', [CourseController::class, 'show']);
        Route::middleware(['permission:course_edit'])->put('/{id}', [CourseController::class, 'update']);
        Route::middleware(['permission:course_delete'])->delete('/{id}', [CourseController::class, 'destroy']);
        Route::middleware(['permission:course_detail'])->get('/{id}/students', [CourseController::class, 'getStudents']);
    });

    Route::prefix('/classes')->group(function () {
        Route::middleware(['permission:class_list'])->get('/', [ClassRoomController::class, 'index']);
        Route::middleware(['permission:class_create'])->post('/', [ClassRoomController::class, 'store']);
        Route::middleware(['permission:class_detail'])->get('/{id}', [ClassRoomController::class, 'show']);
        Route::middleware(['permission:class_edit'])->put('/{id}', [ClassRoomController::class, 'update']);
        Route::middleware(['permission:class_delete'])->delete('/{id}', [ClassRoomController::class, 'destroy']);
        Route::middleware(['permission:class_edit'])->post('/{id}/assign-students', [ClassRoomController::class, 'assignStudents']);
    });

    Route::prefix('/news')->group(function () {
        Route::middleware(['permission:news_list'])->get('/', [NewController::class, 'index']);
        Route::middleware(['permission:news_create'])->post('/', [NewController::class, 'store']);
        Route::middleware(['permission:news_detail'])->get('/{id}', [NewController::class, 'show']);
        Route::middleware(['permission:news_edit'])->put('/{id}', [NewController::class, 'update']);
        Route::middleware(['permission:news_delete'])->delete('/{id}', [NewController::class, 'destroy']);
    });

    Route::prefix('/admins')->group(function () {
        Route::middleware(['permission:admin_list'])->get('/', [AdminController::class, 'index']);
        Route::middleware(['permission:admin_create'])->post('/', [AdminController::class, 'store']);
        Route::middleware(['permission:admin_detail'])->get('/{id}', [AdminController::class, 'show']);
        Route::middleware(['permission:admin_edit'])->put('/{id}', [AdminController::class, 'update']);
        Route::middleware(['permission:admin_delete'])->delete('/{id}', [AdminController::class, 'destroy']);
    });
});



// Route::get('/filters', [FilterController::class, 'index']);

// Common endpoints
Route::prefix('/common')->group(function () {
    Route::get('/levels', [LevelController::class, 'getPublishedLevel']);
    Route::get('/subjects', [SubjectController::class, 'getPublishedSubject']);
    Route::get('/categories', [SubjectController::class, 'getAllCategory']);
});

// Public / Client endpoints (No login required)
Route::prefix('/student')->group(function () {
    Route::get('/courses', [ClientCourseController::class, 'index']);
    Route::get('/courses/{slug}', [ClientCourseController::class, 'show']);
    Route::post('/courses/{id}/register', [ClientCourseController::class, 'register']);
});

// ─── Student Portal ──────────────────────────────────────────────────────────
Route::middleware(['auth:api', 'role:student'])->prefix('/student')->group(function () {
    // Schedules
    Route::prefix('/schedules')->group(function () {
        Route::get('/day', [StudentPortalController::class, 'dailySchedules']);
        Route::get('/week', [StudentPortalController::class, 'weeklySchedules']);
    });

    // Classes
    Route::get('/classes', [StudentPortalController::class, 'myClasses']);
    Route::get('/classes/{code}', [StudentPortalController::class, 'classDetail']);
    Route::get('/classes/{code}/lectures', [StudentPortalController::class, 'classLectures']);
    Route::get('/classes/{code}/lectures/{id}', [StudentPortalController::class, 'lectureDetail']);
    Route::get('/classes/{code}/exams', [StudentPortalController::class, 'classExams']);

    // Exams
    Route::get('/exams/{id}/questions', [StudentPortalController::class, 'examQuestions']);
    Route::post('/exams/{id}/submit', [StudentPortalController::class, 'submitExam']);
    Route::get('/exams/{id}/result', [StudentPortalController::class, 'examResult']);
});

// Storage (upload file)
Route::post('/storage/upload', [StorageController::class, 'upload']);

// ─── Teacher Portal ──────────────────────────────────────────────────────────
Route::middleware(['auth:api', 'role:teacher'])->prefix('/teacher')->group(function () {
    // Dashboard
    Route::get('/dashboard/stats', [TeacherPortalController::class, 'dashboardStats']);

    // Profile
    Route::get('/profile', [TeacherPortalController::class, 'getProfile']);
    Route::put('/profile', [TeacherPortalController::class, 'updateProfile']);

    // Schedules
    Route::prefix('/schedules')->group(function () {
        Route::get('/day', [TeacherPortalController::class, 'dailySchedules']);
        Route::get('/week', [TeacherPortalController::class, 'weeklySchedules']);
        Route::delete('/sessions/{id}', [TeacherPortalController::class, 'deleteSession']);
    });

    // Classes
    Route::get('/classes', [TeacherPortalController::class, 'myClasses']);
    Route::get('/classes/{id}', [TeacherPortalController::class, 'classDetail']);

    // Lectures (buổi học)
    Route::post('/classes/{classId}/lectures/import', [LectureController::class, 'import']);
    Route::get('/classes/{classId}/lectures', [LectureController::class, 'index']);
    Route::post('/classes/{classId}/lectures', [LectureController::class, 'store']);
    Route::put('/lectures/{id}', [LectureController::class, 'update']);
    Route::delete('/lectures/{id}', [LectureController::class, 'destroy']);

    // Exams (bài kiểm tra)
    Route::get('/classes/{classId}/exams', [ExamController::class, 'getByClass']);
    Route::get('/exams', [ExamController::class, 'index']);
    Route::post('/exams', [ExamController::class, 'store']);
    Route::get('/exams/{id}', [ExamController::class, 'show']);
    Route::put('/exams/{id}', [ExamController::class, 'update']);
    Route::delete('/exams/{id}', [ExamController::class, 'destroy']);

    // Exam Questions (câu hỏi)
    Route::get('/exams/{examId}/questions', [ExamController::class, 'getQuestions']);
    Route::put('/exams/{examId}/questions', [ExamController::class, 'syncQuestions']);

    // Exam Results & Grading (kết quả & chấm điểm)
    Route::get('/exams/{id}/results', [ExamController::class, 'results']);
    Route::get('/exams/{id}/students', [ExamController::class, 'getStudents']);
    Route::get('/exams/{examId}/students/{studentId}/answers', [ExamController::class, 'studentAnswers']);
    Route::put('/results/{id}/grade', [ExamController::class, 'grade']);

    // Evaluations (đánh giá học sinh)
    Route::get('/classes/{classId}/evaluations', [EvaluationController::class, 'index']);
    Route::post('/classes/{classId}/evaluations', [EvaluationController::class, 'store']);
    Route::put('/evaluations/{id}', [EvaluationController::class, 'update']);
    Route::delete('/evaluations/{id}', [EvaluationController::class, 'destroy']);

    // Announcements (thông báo lớp)
    Route::get('/classes/{classId}/announcements', [AnnouncementController::class, 'index']);
    Route::post('/classes/{classId}/announcements', [AnnouncementController::class, 'store']);
    Route::put('/announcements/{id}', [AnnouncementController::class, 'update']);
    Route::delete('/announcements/{id}', [AnnouncementController::class, 'destroy']);
});