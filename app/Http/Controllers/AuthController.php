<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Services\AuthService;

class AuthController extends Controller
{
    private $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(RegisterRequest $registerRequest){
        $user = $this->authService->register($registerRequest->validated());
        return ApiResponse::success($user, "User registered successfully", [], 201);
    }

    public function login(LoginRequest $loginRequest){
        $accessToken = $this->authService->login($loginRequest->validated());
        return ApiResponse::success(['access_token' => $accessToken], "User logged in successfully");
    }

    public function me(){
        $user = $this->authService->me();
        return ApiResponse::success($user, "User profile retrieved successfully");
    }
}