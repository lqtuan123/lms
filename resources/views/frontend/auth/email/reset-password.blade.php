<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại mật khẩu</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #e5e5e5;
            border-radius: 5px;
        }
        .header {
            background-color: #3b82f6;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            padding: 20px;
        }
        .button {
            display: inline-block;
            background-color: #3b82f6;
            color: white;
            padding: 12px 24px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            margin: 20px 0;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Đặt lại mật khẩu của bạn</h1>
        </div>
        
        <div class="content">
            <p>Xin chào,</p>
            
            <p>Bạn đã yêu cầu đặt lại mật khẩu cho tài khoản của mình. Vui lòng nhấp vào nút bên dưới để tiếp tục:</p>
            
            <p style="text-align: center;">
                <a href="{{ url('/reset-password/'.$token.'?email='.urlencode($email)) }}" class="button">Đặt lại mật khẩu</a>
            </p>
            
            <p>Liên kết đặt lại mật khẩu này sẽ hết hạn sau 60 phút.</p>
            
            <p>Nếu bạn không yêu cầu đặt lại mật khẩu, bạn có thể bỏ qua email này và mật khẩu của bạn sẽ không thay đổi.</p>
            
            <p>Trân trọng,<br>Đội ngũ hỗ trợ LMS Book</p>
        </div>
        
        <div class="footer">
            <p>© {{ date('Y') }} LMS Book. Tất cả các quyền được bảo lưu.</p>
            <p>Nếu bạn gặp vấn đề với nút "Đặt lại mật khẩu", hãy sao chép và dán liên kết này vào trình duyệt web của bạn: <br>
            {{ url('/reset-password/'.$token.'?email='.urlencode($email)) }}</p>
        </div>
    </div>
</body>
</html> 