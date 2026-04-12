<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\FilterController;
use App\Http\Controllers\GuardianController;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SkillLevelController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\TestController;
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


Route::middleware(['auth:api'])->prefix('/teachers')->group(function () {
    Route::middleware(['permission:teacher_list'])->get('/', [TeacherController::class, 'index']);
    Route::middleware(['permission:teacher_create'])->post('/', [TeacherController::class, 'store']);
    Route::middleware(['permission:teacher_detail'])->get('/{id}', [TeacherController::class, 'show']);
    Route::middleware(['permission:teacher_edit'])->put('/{id}', [TeacherController::class, 'update']);
    Route::middleware(['permission:teacher_delete'])->delete('/{id}', [TeacherController::class, 'destroy']);
});


Route::middleware(['auth:api'])->prefix('/students')->group(function () {
    Route::middleware(['permission:student_list'])->get('/all-students', [StudentController::class, 'getAllStudent']);
    Route::middleware(['permission:student_list'])->get('/', [StudentController::class, 'index']);
    Route::middleware(['permission:student_create'])->post('/', [StudentController::class, 'store']);
    Route::middleware(['permission:student_detail'])->get('/{id}', [StudentController::class, 'show']);
    Route::middleware(['permission:student_edit'])->put('/{id}', [StudentController::class, 'update']);
    Route::middleware(['permission:student_delete'])->delete('/{id}', [StudentController::class, 'destroy']);
});

Route::middleware(['auth:api'])->prefix('/subjects')->group(function () {
    Route::middleware(['permission:subject_list'])->get('/', [SubjectController::class, 'index']);
    Route::middleware(['permission:subject_create'])->post('/', [SubjectController::class, 'store']);
    Route::middleware(['permission:subject_edit'])->put('/{id}', [SubjectController::class, 'update']);
    Route::middleware(['permission:subject_delete'])->delete('/{id}', [SubjectController::class, 'destroy']);
});

Route::middleware(['auth:api'])->prefix('/levels')->group(function () {
    Route::middleware(['permission:level_list'])->get('/', [LevelController::class, 'index']);
    Route::middleware(['permission:level_create'])->post('/', [LevelController::class, 'store']);
    Route::middleware(['permission:level_edit'])->put('/{id}', [LevelController::class, 'update']);
    Route::middleware(['permission:level_delete'])->delete('/{id}', [LevelController::class, 'destroy']);
});

Route::middleware(['auth:api'])->prefix('/roles')->group(function () {
    Route::middleware(['permission:role_list'])->get('/', [RoleController::class, 'listRole']);
    Route::middleware(['permission:role_create'])->post('/', [RoleController::class, 'createRole']);
    Route::middleware(['permission:role_edit'])->put('/{id}', [RoleController::class, 'updateRole']);
    Route::middleware(['permission:role_delete'])->delete('/{id}', [RoleController::class, 'deleteRole']);
});

Route::middleware(['auth:api'])->prefix('/guardians')->group(function () {
    Route::middleware(['permission:guardian_list'])->get('/', [GuardianController::class, 'index']);
    Route::middleware(['permission:guardian_create'])->post('/', [GuardianController::class, 'store']);
    Route::middleware(['permission:guardian_detail'])->get('/{id}', [GuardianController::class, 'show']);
    Route::middleware(['permission:guardian_edit'])->put('/{id}', [GuardianController::class, 'update']);
    Route::middleware(['permission:guardian_delete'])->delete('/{id}', [GuardianController::class, 'destroy']);
});

Route::middleware(['auth:api'])->prefix('/courses')->group(function () {
    Route::middleware(['permission:course_list'])->get('/', [CourseController::class, 'index']);
    Route::middleware(['permission:course_create'])->post('/', [CourseController::class, 'store']);
    Route::middleware(['permission:course_detail'])->get('/{id}', [CourseController::class, 'show']);
    Route::middleware(['permission:course_edit'])->put('/{id}', [CourseController::class, 'update']);
    Route::middleware(['permission:course_delete'])->delete('/{id}', [CourseController::class, 'destroy']);
});

Route::get('/filters', [FilterController::class, 'index']);