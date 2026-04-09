<?php

use App\Http\Controllers\AuthController;
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

