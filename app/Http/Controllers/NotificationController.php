<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Lấy danh sách thông báo của người dùng (tối đa 20 cái gần nhất).
     */
    public function index(Request $request)
    {
        $notifications = Auth::user()->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 10));

        return ApiResponse::success($notifications);
    }

    /**
     * Đánh dấu thông báo đã đọc.
     */
    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        return ApiResponse::success(null, 'Đã đánh dấu đã đọc.');
    }

    /**
     * Đánh dấu tất cả là đã đọc.
     */
    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        return ApiResponse::success(null, 'Đã đánh dấu tất cả là đã đọc.');
    }

    /**
     * Xóa một thông báo.
     */
    public function destroy($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->delete();
        return ApiResponse::success(null, 'Đã xóa thông báo.');
    }
}
