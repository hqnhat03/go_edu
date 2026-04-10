<?php

namespace App\Exceptions;

use App\Helpers\ApiResponse;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;
use Tymon\JWTAuth\Exceptions\JWTException;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->renderable(function (Throwable $e, $request) {
            // 1. Bắt lỗi Validation (Lỗi xác thực dữ liệu như email trùng, thiếu trường...)
            if ($e instanceof ValidationException) {
                return ApiResponse::error(
                    "Validation exeption", // Message chung
                    $e->errors(),           // Chi tiết các lỗi trả về (ví dụ { "email": ["Email đã tồn tại"] })
                    400
                );
            }

            // 2. Bắt lỗi của JWT
            if ($e instanceof JWTException) {
                return ApiResponse::error($e->getMessage(), [], 401);
            }

            if ($e instanceof UserException) {
                return ApiResponse::error($e->getMessage(), [], 400);
            }

            if ($e instanceof UnauthorizedException) {
                return ApiResponse::error("Không có quyền", [], 403);
            }

            if ($e instanceof NotFoundHttpException) {
                return ApiResponse::error("Không tìm thấy", [], 404);
            }

            // 3. Nếu là API, luôn ép trả về JSON khi gặp lỗi khác (để tránh bị redirect ra layout HTML)
            if ($request->is('api/*')) {
                dd($e->getMessage());
                $statusCode = method_exists($e, 'getStatusCode') ? $e->getCode() : 500;
                return ApiResponse::error($e->getMessage(), [], $statusCode);
            }
        });

        $this->reportable(function (Throwable $e) {
            // Ghi log
        });
    }
}
