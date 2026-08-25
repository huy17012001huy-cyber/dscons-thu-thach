# Ngày 17 — Đặt giới hạn an toàn và trial/demo

**Hình thức:** Video ngắn · **Thời lượng:** 45 phút

## Hôm nay làm gì?

Bảo vệ model và người dùng: backup, xác nhận trước khi sửa, log thao tác và trial demo đơn giản; không xây license server.

## Kết quả cần đạt

Tool an toàn khi chạy thử và có giới hạn minh bạch

## AI làm gì?

- Đọc workspace, hiểu mục tiêu và đề xuất cách làm.
- Thực hiện phần code/tài liệu, build và báo lại file đã thay đổi.

## Học viên kiểm tra gì?

- Mở đúng workspace và model bản sao; đọc kế hoạch trước khi bấm chạy.
- Kiểm tra kết quả thật trong Revit và chụp minh chứng dễ đối chiếu.

## SOP duy nhất

1. Dán prompt yêu cầu AI audit Transaction, destructive action và dữ liệu log.
2. Thêm confirmation trước batch operation.
3. Test trên bản sao model với backup.
4. Nếu có trial, dùng ngày hết hạn cấu hình cục bộ; không hard-code secret.

## Minh chứng cần nộp

- Ảnh màn hình kết quả của ngày 17.

## Điều kiện Đạt

Đạt từ 70/100, có ảnh kết quả thật và không có lỗi nghiêm trọng. Nếu chưa đạt, sửa đúng lý do mentor ghi rồi nộp lại; không bị trừ XP.

## Lỗi thường gặp

- Không backup.
- Ghi nguyên model vào log.
