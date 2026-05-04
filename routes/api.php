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
    Route::get('/profile', [AdminController::class, 'getProfile']);
    Route::put('/profile', [AdminController::class, 'updateProfile']);

    Route::prefix('/teachers')->controller(TeacherController::class)->group(function () {
        Route::get('/', 'index')->middleware('permission:teacher_list');
        Route::post('/', 'store')->middleware('permission:teacher_create');
        Route::get('/{id}', 'show')->middleware('permission:teacher_detail');
        Route::put('/{id}', 'update')->middleware('permission:teacher_edit');
        Route::delete('/{id}', 'destroy')->middleware('permission:teacher_delete');
    });

    Route::prefix('/students')->controller(StudentController::class)->group(function () {
        Route::get('/all-students', 'getAllStudent')->middleware('permission:student_list');
        Route::get('/', 'index')->middleware('permission:student_list');
        Route::post('/', 'store')->middleware('permission:student_create');
        Route::get('/{id}', 'show')->middleware('permission:student_detail');
        Route::put('/{id}', 'update')->middleware('permission:student_edit');
        Route::post('/{id}/enroll-course', 'enrollCourse')->middleware('permission:student_edit');
        Route::delete('/{id}', 'destroy')->middleware('permission:student_delete');
    });

    Route::prefix('/subjects')->controller(SubjectController::class)->group(function () {
        Route::get('/', 'index')->middleware('permission:subject_list');
        Route::post('/', 'store')->middleware('permission:subject_create');
        Route::put('/{id}', 'update')->middleware('permission:subject_edit');
        Route::delete('/{id}', 'destroy')->middleware('permission:subject_delete');
        Route::get('/categories', 'getAllCategory')->middleware('permission:subject_list');
    });

    Route::prefix('/levels')->controller(LevelController::class)->group(function () {
        Route::get('/', 'index')->middleware('permission:level_list');
        Route::post('/', 'store')->middleware('permission:level_create');
        Route::put('/{id}', 'update')->middleware('permission:level_edit');
        Route::delete('/{id}', 'destroy')->middleware('permission:level_delete');
        Route::get('/education-levels', 'getAllEducationLevel')->middleware('permission:level_list');
    });

    Route::prefix('/roles')->controller(RoleController::class)->group(function () {
        Route::get('/', 'listRole')->middleware('permission:role_list');
        Route::post('/', 'createRole')->middleware('permission:role_create');
        Route::put('/{id}', 'updateRole')->middleware('permission:role_edit');
        Route::delete('/{id}', 'deleteRole')->middleware('permission:role_delete');
    });

    Route::prefix('/guardians')->controller(GuardianController::class)->group(function () {
        Route::get('/', 'index')->middleware('permission:guardian_list');
        Route::post('/', 'store')->middleware('permission:guardian_create');
        Route::get('/{id}', 'show')->middleware('permission:guardian_detail');
        Route::put('/{id}', 'update')->middleware('permission:guardian_edit');
        Route::delete('/{id}', 'destroy')->middleware('permission:guardian_delete');
    });

    Route::prefix('/courses')->controller(CourseController::class)->group(function () {
        Route::get('/', 'index')->middleware('permission:course_list');
        Route::post('/', 'store')->middleware('permission:course_create');
        Route::get('/{id}', 'show')->middleware('permission:course_detail');
        Route::put('/{id}', 'update')->middleware('permission:course_edit');
        Route::delete('/{id}', 'destroy')->middleware('permission:course_delete');
        Route::get('/{id}/students', 'getStudents')->middleware('permission:course_detail');
    });

    Route::prefix('/classes')->controller(ClassRoomController::class)->group(function () {
        Route::get('/', 'index')->middleware('permission:class_list');
        Route::post('/', 'store')->middleware('permission:class_create');
        Route::get('/{id}', 'show')->middleware('permission:class_detail');
        Route::put('/{id}', 'update')->middleware('permission:class_edit');
        Route::delete('/{id}', 'destroy')->middleware('permission:class_delete');
        Route::post('/{id}/assign-students', 'assignStudents')->middleware('permission:class_edit');
    });

    Route::prefix('/news')->controller(NewController::class)->group(function () {
        Route::get('/', 'index')->middleware('permission:news_list');
        Route::post('/', 'store')->middleware('permission:news_create');
        Route::get('/{id}', 'show')->middleware('permission:news_detail');
        Route::put('/{id}', 'update')->middleware('permission:news_edit');
        Route::delete('/{id}', 'destroy')->middleware('permission:news_delete');
    });

    Route::prefix('/admins')->controller(AdminController::class)->group(function () {
        Route::get('/', 'index')->middleware('permission:admin_list');
        Route::post('/', 'store')->middleware('permission:admin_create');
        Route::get('/{id}', 'show')->middleware('permission:admin_detail');
        Route::put('/{id}', 'update')->middleware('permission:admin_edit');
        Route::delete('/{id}', 'destroy')->middleware('permission:admin_delete');
    });

    Route::get('/permissions', [RoleController::class, 'listPermission'])->middleware('permission:role_list');
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
    // Profile
    Route::get('/profile', [StudentPortalController::class, 'getProfile']);
    Route::put('/profile', [StudentPortalController::class, 'updateProfile']);

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