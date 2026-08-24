# 21 Ngày Chinh Phục Tool Revit bằng AI Agent

Đây là chương trình thực chiến của DSCons dành cho kỹ sư BIM/MEP. Học viên không phải tự viết C#/WPF bằng tay. Học viên dùng Codex, Antigravity hoặc agent tương đương để đọc workspace, lập kế hoạch, tạo code, sửa lỗi và đóng gói; sau đó phải chạy thật trên Revit, cá nhân hóa và nộp bằng chứng.

## Đầu ra sau 21 ngày

- Một add-in/tool Revit có tên cá nhân, chạy được trên Revit 2020–2026 theo version đã khai báo.
- Một bộ cài `dist` có DLL, `.addin`, dependency và README; có thể cài lại.
- Một manual một trang, video demo và Tool Brief cuối khóa.
- Bằng chứng cho thấy học viên hiểu AI đã làm gì và mình đã kiểm chứng kết quả thế nào.

## Nhịp học

Ngày 1, 8, 15 và 21 là livestream. Các ngày còn lại là video ngắn 8–20 phút, gồm khái niệm, thao tác mẫu, SOP copy vào AI và video xử lý lỗi. Mỗi ngày có một đầu ra quan sát được; không có ngày chỉ đọc lý thuyết.

## Luật an toàn

1. Chỉ dùng model mẫu hoặc bản sao đã anonymize; không đưa dữ liệu dự án mật lên AI.
2. AI phải đọc file và lập kế hoạch trước khi sửa. Học viên xác nhận phạm vi.
3. Không xóa file, không sửa model gốc. Backup trước Transaction/batch operation.
4. Ảnh code không thay thế ảnh/video tool chạy thật trong Revit.
5. Revit version, target framework và dependency phải được ghi rõ trong bài nộp.

## Cách học một ngày

1. Đọc mục tiêu và kiến thức nền.
2. Mở đúng workspace/model bản sao.
3. Copy prompt vào AI Agent; trả lời các câu hỏi về version, tên tool và đường dẫn.
4. Đọc kế hoạch, xác nhận file được phép sửa rồi để AI thực hiện.
5. Build/chạy trong Revit và đối chiếu output.
6. Nộp bằng chứng thật cùng ba câu reflection: yêu cầu AI làm gì, AI sửa file nào, tôi kiểm chứng ra sao.

## Đánh giá

Bài thường chấm: đủ bằng chứng 30, kết quả đúng 40, cá nhân hóa 15, mô tả kiểm chứng 15. Đạt từ 70/100 và không có Critical Fail. Bài milestone dùng rubric 5 mục trong [`RUBRIC_TEMPLATE.md`](RUBRIC_TEMPLATE.md). Bài bị từ chối phải có lý do cụ thể và được nộp lại, không trừ XP vì nộp lại.

## Nguồn nội dung runtime

Task thật giữ slug `21-ngay-lam-tool-revit-voi-ai-agent`. Nội dung có cấu trúc nằm trong `challenge_tasks.instruction_payload`; `sop_content`, `description`, `evidence` và bài cũ vẫn được đọc fallback.
