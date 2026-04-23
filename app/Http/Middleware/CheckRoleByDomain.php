<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRoleByDomain
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        // 1. Lấy Origin từ Header (ví dụ: http://goedu.demo.vn)
        $origin = $request->headers->get('origin');

        // Parse để lấy host (goedu.demo.vn)
        $host = parse_url($origin, PHP_URL_HOST);

        // 2. Định nghĩa bản đồ Domain => Role tương ứng
        $domainMap = [
            'goedu.demo.vn' => ['super_admin', 'admin'],
            'teacher-goedu.demo.vn' => ['teacher'],
            'student-goedu.demo.vn' => ['student'],
            'localhost' => ['super_admin', 'admin'],
            '127.0.0.1' => ['super_admin', 'admin'],
        ];

        // Nếu domain không nằm trong danh sách quản lý, có thể chặn hoặc bỏ qua
        if (!isset($domainMap[$host])) {
            return response()->json([
                'message' => 'Domain not found',
            ], 404);
        }

        $requiredRole = $domainMap[$host];

        // 3. Đính kèm thông tin role yêu cầu vào request để Controller sử dụng
        $request->attributes->add(['required_role' => $requiredRole]);

        return $next($request);
    }
}
