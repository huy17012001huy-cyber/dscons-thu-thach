# 21 Ngày Chinh Phục Tool Revit bằng AI Agent

Chương trình dành cho kỹ sư BIM/MEP muốn biến một việc lặp lại thành một tool Revit dùng được. Bạn không cần tự viết C#/WPF bằng tay. AI Agent làm phần nặng; bạn chọn bài toán, kiểm tra kết quả thật và chia sẻ sản phẩm.

## Sau 21 ngày

- Có một tool Revit nhỏ, đúng nhu cầu công việc của bạn.
- Biết cách dùng AI Agent: đọc workspace, lập kế hoạch, làm, sửa lỗi và đóng gói.
- Có ảnh/video chứng minh tool chạy thật trên model bản sao.
- Có sản phẩm, hướng dẫn ngắn và bài chia sẻ trên Bảng tin.

## Cách học

Mỗi ngày chỉ tập trung vào một việc:

1. Đọc mục tiêu và mở đúng workspace.
2. Copy **một SOP duy nhất** vào Codex, Antigravity hoặc agent tương đương.
3. Để AI đọc và lập kế hoạch; chỉ cho AI làm sau khi bạn xác nhận.
4. Mở Revit, chạy trên model mẫu/bản sao và kiểm tra kết quả.
5. Nộp minh chứng theo đúng yêu cầu của ngày.

Ngày 1, 8, 15 và 21 là livestream. Các ngày còn lại là video ngắn. Video nộp bài chỉ bắt buộc ở ngày 7, 14 và 21; những ngày khác chủ yếu nộp ảnh màn hình kết quả.

## Nguyên tắc AI First

Trước khi làm một việc, hãy hỏi: **AI có thể giúp mình làm việc này tốt hơn hoặc nhanh hơn không?** Hãy thử, ghi lại điều học được và chia sẻ cách bạn đã áp dụng vào công việc thật.

## An toàn

- Không đưa model hoặc dữ liệu dự án mật lên AI.
- Không sửa model gốc; luôn dùng bản sao và backup trước thao tác ghi.
- AI phải đọc file và nói kế hoạch trước khi sửa.
- Ảnh code không thay thế ảnh tool chạy trong Revit.

## Chấm bài

Bài thường đạt từ 70/100: minh chứng 30, kết quả đúng 40, cá nhân hóa 15, cách kiểm chứng 15. Chỉ nộp ảnh code, sửa nhầm model gốc hoặc không thể chạy lại là chưa đạt. Bài bị từ chối luôn có lý do và được nộp lại, không trừ XP.

Task thật giữ slug `21-ngay-lam-tool-revit-voi-ai-agent`. Payload có cấu trúc nằm trong `challenge_tasks.instruction_payload`; task cũ vẫn dùng được nhờ `sop_content` fallback.
