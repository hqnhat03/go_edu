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

        // 1. Lấy Origin từ Header (ví dụ: http://admin.go.edu.vn)
        $origin = $request->headers->get('origin');

        // Parse để lấy host (admin.go.edu.vn)
        $host = parse_url($origin, PHP_URL_HOST);

        // 2. Định nghĩa bản đồ Domain => Role tương ứng
        $domainMap = [
            'admin.go.edu.vn' => ['super_admin', 'admin'],
            'teacher.go.edu.vn' => ['teacher'],
            'student.go.edu.vn' => ['student'],
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
