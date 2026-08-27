# Ngày 20 — Kiểm thử như một người dùng thật

**Hình thức:** Tự thực hành · **Thời lượng:** 75 phút

## Hôm nay làm gì?

Bạn thử tool trên model mô phỏng công việc với dữ liệu đúng, thiếu và thao tác sai.

## Kết quả cần đạt

Tool đạt ít nhất ba tình huống và có giới hạn rõ ràng.

## AI làm gì?

- Đọc mục tiêu và những gì đang có trước khi thực hiện.
- Đề xuất kế hoạch, file cần thay đổi, lệnh build và cách kiểm tra.
- Thực hiện phần kỹ thuật sau khi học viên xác nhận và báo lại kết quả.

## Học viên kiểm tra gì?

- Chọn đúng phiên bản Revit và cung cấp thông tin công việc cho AI.
- Đọc kế hoạch, xác nhận trước thay đổi và không đưa dữ liệu mật lên AI.
- Mở Revit bằng model mẫu/bản sao, chạy thử và đối chiếu kết quả.

## SOP từng bước

1. Mở công cụ hoặc model được ghi trong bài và dùng bản sao an toàn.
2. Copy toàn bộ prompt bên dưới, dán vào AI Agent và điền các phần trong ngoặc vuông.
3. Khi AI hỏi lại, trả lời phiên bản Revit, mục tiêu và model kiểm thử; nếu chưa biết thì nói rõ “chưa biết”, không đoán.
4. Đọc kế hoạch AI: mục tiêu, việc sẽ làm, file sẽ sửa, lệnh chạy và cách quay lại. Chỉ xác nhận khi bạn hiểu.
5. Chọn model mô phỏng hoặc đã ẩn dữ liệu nhạy cảm; chạy từ bộ cài đã đóng gói.
6. Ghi bảng test cho happy path, dữ liệu thiếu và thao tác sai.
7. Nếu sửa sau UAT, build lại dist và chạy lại case lỗi đã phát hiện.
8. Để AI thực hiện, build/chạy theo hướng dẫn, rồi mở Revit để kiểm tra kết quả thật.
9. Chụp bằng chứng, trả lời ba câu reflection và nộp bài trên website. Nếu lỗi, gửi đoạn log từ ERROR/exception đến phần summary.

## Minh chứng cần nộp

- Ảnh màn hình kết quả thật của ngày 20.
- Có bảng test và ảnh chụp của ba tình huống.
- Ba câu ngắn: Tôi yêu cầu AI làm gì? AI đã làm gì? Tôi kiểm tra kết quả thế nào?

## Điều kiện Đạt

Đạt từ 70/100, có ảnh kết quả thật và không có lỗi nghiêm trọng. Nếu chưa đạt, sửa đúng lý do mentor ghi rồi nộp lại; không bị trừ XP.

## Lỗi thường gặp

- Chỉ gửi ảnh code hoặc câu trả lời của AI mà chưa chạy trong Revit.
- Dùng sai phiên bản, sai model hoặc quên kiểm tra kết quả độc lập.
- Cho AI sửa quá nhiều thứ cùng lúc hoặc sửa model gốc.
