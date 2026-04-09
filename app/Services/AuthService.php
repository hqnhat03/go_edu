<?php

namespace App\Services;

use App\Exceptions\UserException;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Cache;
use Request;

class AuthService
{

    public function register($data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        $user->assignRole('student');
        $user->student()->create([
            'user_id' => $user->id,
        ]);
        return $user;
    }

    public function login(LoginRequest $request)
    {
        // $data = $request->validated();
        $user = User::where('email', $request->email)->first();

        // Lấy role yêu cầu từ Middleware
        $requiredRole = $request->attributes->get('required_role');

        if ($requiredRole && !$user->hasRole($requiredRole)) {
            throw new UserException("Sai email hoặc mật khâu");
        }

        $credentials = $request->only('email', 'password');
        if (!$token = auth()->attempt($credentials)) {
            throw new UserException("Sai email hoặc mật khâu");
        }
        return [
            'access_token' => $token,
            'refresh_token' => $token, // Using Option A: JWT acts as its own refresh token natively
        ];
    }

    public function me()
    {
        return auth()->user();
    }

    public function logout()
    {
        auth()->logout();
    }

    public function resendVerification($email)
    {
        $user = User::where('email', $email)->first();
        if (!$user) {
            throw new \Exception("User not found.");
        }
        if ($user->email_verified_at) {
            throw new \Exception("Email is already verified.");
        }

        // Tạo OTP 6 số
        $otp = rand(100000, 999999);

        // Lưu OTP vào Cache (sẽ tự động dùng Redis nếu cấu hình CACHE_DRIVER=redis) - Hết hạn sau 15 phút
        Cache::put('email_verification_' . $email, $otp, now()->addMinutes(15));

        // TODO: Gửi email chứa OTP cho user

        return $otp; // Tạm thời return ra để có thể test API
    }

    public function verifyEmail($data)
    {
        $email = $data['email'];
        $otp = $data['otp'];

        $cachedOtp = Cache::get('email_verification_' . $email);

        if (!$cachedOtp || $cachedOtp != $otp) {
            throw new \Exception("Mã OTP không hợp lệ hoặc đã hết hạn.");
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            throw new \Exception("User not found.");
        }

        $user->email_verified_at = now();
        $user->save();

        Cache::forget('email_verification_' . $email);
    }

    public function refreshToken()
    {
        try {
            return auth()->refresh();
        } catch (\Exception $e) {
            throw new \Exception("Could not refresh token. " . $e->getMessage());
        }
    }

    public function changePassword($user, $data)
    {
        if (!Hash::check($data['current_password'], $user->password)) {
            throw new \Exception("Mật khẩu hiện tại không chính xác.");
        }

        $user->password = $data['new_password'];
        $user->save();
    }

    public function forgotPassword($email)
    {
        // Xóa token cũ của user nếu có để tránh rác DB
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        // Tạo một token ngẫu nhiên (hoặc mã PIN tùy yêu cầu)
        $token = Str::random(60);

        // Lưu token vào database. Laravel mặc định có sẵn bảng password_reset_tokens
        // Nếu không có, bạn cần tạo migration cho bảng này: migration `create_password_reset_tokens_table`
        DB::table('password_reset_tokens')->insert([
            'email' => $email,
            'token' => $token, // Có thể dùng Hash::make($token) nếu yêu cầu khắt khe về bảo mật
            'created_at' => Carbon::now()
        ]);

        // TODO: Gửi email chứa token hoặc link đặt lại mật khẩu cho user
        // Ví dụ: \Illuminate\Support\Facades\Mail::to($email)->send(new \App\Mail\ResetPasswordMail($token));

        return $token; // Tạm thời return ra để có thể test API, trong thực tế chỉ gửi qua mail!
    }

    public function resetPassword($data)
    {
        // 1. Kiểm tra record token từ DB
        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $data['email'])
            ->where('token', $data['token'])
            ->first();

        if (!$resetRecord) {
            throw new \Exception("Token không hợp lệ hoặc không tồn tại.");
        }

        // Kiểm tra thời gian hết hạn của token (ví dụ: 60 phút)
        if (Carbon::parse($resetRecord->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $data['email'])->delete();
            throw new \Exception("Token đã bị hết hạn.");
        }

        // 3. Đổi mật khẩu cho user
        $user = User::where('email', $data['email'])->first();
        $user->password = bcrypt($data['password']);
        $user->save();

        // 4. Xóa token đi vì đã sử dụng xong
        DB::table('password_reset_tokens')->where('email', $data['email'])->delete();
    }
}