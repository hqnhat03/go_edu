<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LevelController;
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
    Route::middleware(['permission:teacher_list'])->get('/', [TeacherController::class, 'listTeacher']);
    Route::middleware(['permission:teacher_create'])->post('/', [TeacherController::class, 'createTeacher']);
    Route::middleware(['permission:teacher_detail'])->get('/{id}', [TeacherController::class, 'getTeacher']);
    Route::middleware(['permission:teacher_edit'])->put('/{id}', [TeacherController::class, 'editTeacher']);
    Route::middleware(['permission:teacher_delete'])->delete('/{id}', [TeacherController::class, 'deleteTeacher']);
});


Route::middleware(['auth:api'])->prefix('/students')->group(function () {
    Route::middleware(['permission:student_list'])->get('/', [StudentController::class, 'listStudent']);
    Route::middleware(['permission:student_create'])->post('/', [StudentController::class, 'createStudent']);
    Route::middleware(['permission:student_detail'])->get('/{id}', [StudentController::class, 'getStudent']);
    Route::middleware(['permission:student_edit'])->put('/{id}', [StudentController::class, 'editStudent']);
    Route::middleware(['permission:student_delete'])->delete('/{id}', [StudentController::class, 'deleteStudent']);
});

Route::middleware(['auth:api'])->prefix('/subjects')->group(function () {
    Route::middleware(['permission:subject_list'])->get('/', [SubjectController::class, 'listSubject']);
    Route::middleware(['permission:subject_create'])->post('/', [SubjectController::class, 'createSubject']);
    Route::middleware(['permission:subject_edit'])->put('/{id}', [SubjectController::class, 'updateSubject']);
    Route::middleware(['permission:subject_delete'])->delete('/{id}', [SubjectController::class, 'deleteSubject']);
});

Route::middleware(['auth:api'])->prefix('/levels')->group(function () {
    Route::middleware(['permission:level_list'])->get('/', [LevelController::class, 'listLevel']);
    Route::middleware(['permission:level_create'])->post('/', [LevelController::class, 'createLevel']);
    Route::middleware(['permission:level_edit'])->put('/{id}', [LevelController::class, 'updateLevel']);
    Route::middleware(['permission:level_delete'])->delete('/{id}', [LevelController::class, 'deleteLevel']);
});