# Ngày 3 — Chạy preflight môi trường Revit

**Hình thức:** Video ngắn · **Thời lượng:** 45 phút

## Hôm nay làm gì?

Kiểm tra môi trường trước khi debug để phân biệt lỗi máy, lỗi version và lỗi code.

## Kết quả cần đạt

Môi trường build có thể kiểm tra bằng một lệnh rõ ràng

## AI làm gì?

- Đọc workspace, hiểu mục tiêu và đề xuất cách làm.
- Thực hiện phần code/tài liệu, build và báo lại file đã thay đổi.

## Học viên kiểm tra gì?

- Mở đúng workspace và model bản sao; đọc kế hoạch trước khi bấm chạy.
- Kiểm tra kết quả thật trong Revit và chụp minh chứng dễ đối chiếu.

## SOP duy nhất

1. Mở PowerShell tại root workspace.
2. Dán prompt yêu cầu AI đọc script preflight trước khi chạy.
3. Cho AI chạy lệnh read-only; lưu output vào environment-report.json.
4. Kiểm tra Revit version và .NET target có khớp Bộ Kit.

## Minh chứng cần nộp

- Ảnh màn hình kết quả của ngày 3.

## Điều kiện Đạt

Đạt từ 70/100, có ảnh kết quả thật và không có lỗi nghiêm trọng. Nếu chưa đạt, sửa đúng lý do mentor ghi rồi nộp lại; không bị trừ XP.

## Lỗi thường gặp

- Chỉ chụp màn hình, không lưu report.
- Dùng sai terminal hoặc chạy script từ thư mục con.
