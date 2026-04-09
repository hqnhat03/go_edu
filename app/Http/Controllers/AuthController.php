<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    private $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(RegisterRequest $registerRequest)
    {
        $user = $this->authService->register($registerRequest->validated());
        return ApiResponse::success($user, "User registered successfully", [], 201);
    }

    public function login(LoginRequest $request)
    {
        $tokens = $this->authService->login($request);
        return ApiResponse::success([
            'access_token' => $tokens['access_token'],
            'refresh_token' => $tokens['refresh_token']
        ], "User logged in successfully");
    }

    public function me()
    {
        $user = $this->authService->me();
        return ApiResponse::success($user, "User profile retrieved successfully");
    }

    public function logout()
    {
        $this->authService->logout();
        return ApiResponse::success(null, "User logged out successfully");
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        // Trong môi trường test, ta lấy token trả về để dễ test. Ở production, bạn chỉ cần báo gửi thành công.
        $token = $this->authService->forgotPassword($request->email);

        // Return token in dev, hide in production
        return ApiResponse::success(['reset_token' => $token], "Password reset instructions sent to your email (Token is shown here for testing)");
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
            'password' => 'required|string|min:6|confirmed' // Yêu cầu client tự động gửi kèm trường password_confirmation
        ]);

        try {
            $this->authService->resetPassword($request->all());
            return ApiResponse::success(null, "Password has been successfully reset");
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), [], 400);
        }
    }

    public function verifyEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|numeric'
        ]);

        try {
            $this->authService->verifyEmail($request->all());
            return ApiResponse::success(null, "Email verified successfully");
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), [], 400);
        }
    }

    public function resendVerification(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        try {
            $otp = $this->authService->resendVerification($request->email);
            return ApiResponse::success(['otp' => $otp], "Verification OTP sent successfully (OTP string is shown here for testing)");
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), [], 400);
        }
    }

    public function refreshToken()
    {
        try {
            $token = $this->authService->refreshToken();
            return ApiResponse::success([
                'access_token' => $token,
                'refresh_token' => $token
            ], "Token refreshed successfully");
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), [], 401);
        }
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed'
        ]);

        try {
            $this->authService->changePassword($request->user(), $request->all());
            return ApiResponse::success(null, "Password changed successfully");
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), [], 400);
        }
    }
}