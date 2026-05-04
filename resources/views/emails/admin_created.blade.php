<!DOCTYPE html>
<html>
<head>
    <title>Tài khoản quản trị của bạn đã được tạo</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <h2>Chào {{ $user->name }},</h2>
    <p>Bạn đã được cấp quyền quản trị trên hệ thống Go Edu.</p>
    <p>Dưới đây là thông tin đăng nhập của bạn:</p>
    
    <div style="background: #f4f4f4; padding: 15px; border-radius: 5px; margin: 20px 0;">
        <p style="margin: 0;"><strong>Email đăng nhập:</strong> {{ $user->email }}</p>
        <p style="margin: 0; padding-top: 5px;"><strong>Mật khẩu:</strong> {{ $password }}</p>
    </div>
    
    <p>Vui lòng đăng nhập và bảo quản thông tin tài khoản cẩn thận.</p>
    <br>
    <p>Trân trọng,<br>Hệ thống Go Edu</p>
</body>
</html>
