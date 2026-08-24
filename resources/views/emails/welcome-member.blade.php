<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chào mừng tới {{ $brandName }}</title>
</head>
<body style="margin:0;padding:24px 12px;background-color:#f4f4f5;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif">
    <div style="max-width: 600px;margin: 0 auto;background-color: #ffffff;padding: 40px;border-radius: 8px;border: 1px solid #eeeeee">

        <p style="color: #333333;font-size: 16px;line-height: 1.6;margin-bottom: 20px">Chào bạn <strong>{{ $name }}</strong>,</p>

        <p style="color: #333333;font-size: 16px;line-height: 1.6;margin-bottom: 20px">
            Tài khoản của bạn tại <strong>{{ $brandName }}</strong> đã được khởi tạo thành công. Chúng tôi rất vui được chào đón bạn vào cộng đồng!
        </p>

        <p style="color: #333333;font-size: 16px;line-height: 1.6;margin-bottom: 20px">
            Bạn có thể đăng nhập ngay bằng thông tin bên dưới và bắt đầu hành trình của mình.
        </p>

        <div style="color: #111111;font-size: 13px;font-weight: 700;text-transform: uppercase;letter-spacing: 1px;margin-top: 35px;margin-bottom: 15px;border-bottom: 1px solid #eeeeee;padding-bottom: 8px">Thông tin đăng nhập</div>
        <div style="background-color: #f8f9fa;padding: 20px;border-radius: 6px;margin-top: 20px;border: 1px solid #e9ecef">
            <table style="width: 100%;border-collapse: collapse;font-size: 15px">
                <tr><td style="padding: 8px 0;color: #666666;width: 35%;vertical-align: top">Trang đăng nhập:</td><td style="padding: 8px 0;color: #111111;font-weight: 500;text-align: right;vertical-align: top"><a href="{{ $loginUrl }}" style="color:#d17856;text-decoration:none">{{ $loginUrl }}</a></td></tr>
                <tr><td style="padding: 8px 0;color: #666666;width: 35%;vertical-align: top">Email:</td><td style="padding: 8px 0;color: #111111;font-weight: 500;text-align: right;vertical-align: top">{{ $email }}</td></tr>
            </table>
        </div>

        <div style="text-align: center;margin: 30px 0">
            <a href="{{ $loginUrl }}" style="display: inline-block;background-color: #d17856;color: #ffffff;text-decoration: none;font-weight: 700;font-size: 16px;padding: 14px 32px;border-radius: 8px">ĐĂNG NHẬP BẰNG GOOGLE</a>
        </div>

        <p style="color: #666666;font-size: 14px;line-height: 1.6;margin-bottom: 20px">
            Để truy cập tài khoản, hãy dùng đúng địa chỉ email này và chọn nút “Đăng nhập bằng Google” tại trang đăng nhập.
        </p>

        <div style="margin-top: 40px;border-top: 1px solid #eeeeee;padding-top: 20px;font-size: 14px;color: #666666">
            Nếu cần hỗ trợ thêm, bạn có thể liên hệ admin fanpage hoặc tại theallinplan.com.<br><br>
            Hẹn gặp bạn bên trong!<br>
            <strong style="color: #000">Đội ngũ {{ $brandName }}.</strong>
        </div>
    </div>
</body>
</html>
