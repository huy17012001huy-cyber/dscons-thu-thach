<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chúc mừng hoàn thành {{ $challengeTitle }}</title>
</head>
<body style="margin:0;padding:24px 12px;background-color:#f4f4f5;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif">
    <div style="max-width: 600px;margin: 0 auto;background-color: #ffffff;padding: 40px;border-radius: 8px;border: 1px solid #eeeeee">

        <div style="text-align:center;margin-bottom:28px">
            <div style="font-size:48px;line-height:1">🏆</div>
            <div style="color:#d17856;font-size:22px;font-weight:800;margin-top:10px">Chúc mừng bạn đã về đích!</div>
        </div>

        <p style="color: #333333;font-size: 16px;line-height: 1.6;margin-bottom: 20px">Chào <strong>{{ $userName }}</strong>,</p>

        <p style="color: #333333;font-size: 16px;line-height: 1.6;margin-bottom: 20px">
            Bạn đã chính thức <strong>hoàn thành thử thách {{ $challengeTitle }}</strong> với toàn bộ
            <strong>{{ $totalDays }} ngày</strong> được duyệt. Một hành trình bền bỉ — và bạn đã đi tới cuối! 🎉
        </p>

        @if($lateCount > 0)
        <p style="color: #92531c;font-size: 14px;line-height: 1.6;margin-bottom: 20px;background:#fff7ed;border:1px solid #fed7aa;border-radius:6px;padding:12px 14px">
            Bạn có <strong>{{ $lateCount }} ngày nộp trễ</strong> (được đánh dấu <strong>(có trễ)</strong> bên dưới),
            nhưng vẫn hoàn thành đủ chặng đường. Lần tới giữ nhịp đúng hạn để trọn vẹn hơn nhé!
        </p>
        @else
        <p style="color: #166534;font-size: 14px;line-height: 1.6;margin-bottom: 20px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:6px;padding:12px 14px">
            Tuyệt vời — bạn hoàn thành <strong>đúng hạn cả {{ $totalDays }} ngày</strong>, không trễ ngày nào! 💪
        </p>
        @endif

        <div style="color: #111111;font-size: 13px;font-weight: 700;text-transform: uppercase;letter-spacing: 1px;margin-top: 35px;margin-bottom: 15px;border-bottom: 1px solid #eeeeee;padding-bottom: 8px">Bảng thành tích — {{ $totalDays }} ngày</div>

        <div style="background-color: #f8f9fa;padding: 10px 18px;border-radius: 6px;margin-top: 16px;border: 1px solid #e9ecef">
            <table style="width: 100%;border-collapse: collapse;font-size: 15px">
                @foreach($days as $d)
                <tr>
                    <td style="padding: 9px 0;color: #16a34a;width: 26px;vertical-align: top;font-weight:700">✓</td>
                    <td style="padding: 9px 0;color: #111111;vertical-align: top">
                        <strong>Ngày {{ $d['day'] }}</strong>@if(!empty($d['label']) && $d['label'] !== 'Ngày '.$d['day'])<span style="color:#555"> — {{ $d['label'] }}</span>@endif
                        @if($d['is_late'])
                        <span style="color:#dc2626;font-weight:700;font-size:13px"> (có trễ)</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </table>
        </div>

        <div style="text-align: center;margin: 32px 0 8px">
            <a href="{{ $challengeUrl }}" style="display: inline-block;background-color: #d17856;color: #ffffff;text-decoration: none;font-weight: 700;font-size: 16px;padding: 14px 32px;border-radius: 8px">XEM THỬ THÁCH CỦA TÔI</a>
        </div>

        <p style="color: #666666;font-size: 14px;line-height: 1.6;margin-top:24px;text-align:center">
            Hoàn thành lúc {{ $completedAt }}
        </p>

        <div style="margin-top: 36px;border-top: 1px solid #eeeeee;padding-top: 20px;font-size: 14px;color: #666666">
            Cảm ơn bạn đã kiên trì cùng cộng đồng. Hành trình tiếp theo đang chờ!<br><br>
            <strong style="color: #000">Team Website Thử Thách &amp; {{ $brandName }}.</strong>
        </div>
    </div>
</body>
</html>
