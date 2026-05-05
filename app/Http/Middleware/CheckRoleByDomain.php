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

        // 1. Lấy Origin từ Header (ví dụ: http://hqnhat.id.vn)
        $origin = $request->headers->get('origin');

        // Parse để lấy host (hqnhat.id.vn)
        $host = parse_url($origin, PHP_URL_HOST);

        // 2. Định nghĩa bản đồ Domain => Role tương ứng
        $baseDomain = config('app.frontend_domain');
        $domainMap = [
            $baseDomain => ['type' => 'exclude', 'roles' => ['student', 'teacher']],
            'teacher.' . $baseDomain => ['type' => 'allow', 'roles' => ['teacher']],
            'student.' . $baseDomain => ['type' => 'allow', 'roles' => ['student']],
            // DEV
            'localhost' => ['type' => 'exclude', 'roles' => ['student', 'teacher']],
            '127.0.0.1' => ['type' => 'exclude', 'roles' => ['student', 'teacher']],
        ];

        // Nếu domain không nằm trong danh sách quản lý, có thể chặn hoặc bỏ qua
        if (!isset($domainMap[$host])) {
            return response()->json([
                'message' => 'Domain not found',
                'debug' => [
                    'host' => $host,
                    'origin' => $origin,
                    'base_domain' => $baseDomain,
                    'map_keys' => array_keys($domainMap),
                ]
            ], 404);
        }

        $requiredRoleData = $domainMap[$host];

        // 3. Đính kèm thông tin role yêu cầu vào request để Controller sử dụng
        $request->attributes->add(['required_role' => $requiredRoleData]);

        return $next($request);
    }
}
