<!DOCTYPE html>
<html>
<head>
    <title>Tài khoản phụ huynh của bạn đã được tạo</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <h2>Chào phụ huynh {{ $user->name }},</h2>
    <p>Chào mừng bạn đã tham gia Go Edu. Tài khoản phụ huynh của bạn đã được thiết lập để bạn có thể theo dõi tiến trình học tập của con em mình.</p>
    <p>Dưới đây là thông tin đăng nhập của bạn:</p>
    
    <div style="background: #f4f4f4; padding: 15px; border-radius: 5px; margin: 20px 0;">
        <p style="margin: 0;"><strong>Email đăng nhập:</strong> {{ $user->email }}</p>
        <p style="margin: 0; padding-top: 5px;"><strong>Mật khẩu:</strong> {{ $password }}</p>
    </div>
    
    <p>Vui lòng đăng nhập và thay đổi mật khẩu của bạn trong lần đăng nhập đầu tiên để đảm bảo an toàn.</p>
    <br>
    <p>Trân trọng,<br>Đội ngũ Go Edu</p>
</body>
</html>
