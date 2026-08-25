# Ngày 12 — Hoàn thiện command chính và xử lý dữ liệu không hoàn hảo

**Hình thức:** Video ngắn · **Thời lượng:** 75 phút

## Hôm nay làm gì?

Hoàn thiện Auto-Tag, Set Parameter, báo cáo hoặc lọc/đếm; đặc biệt xử lý thiếu Parameter, trùng Tag và phần tử lỗi.

## Kết quả cần đạt

MVP xử lý được happy path và báo lỗi có thể hành động

## AI làm gì?

- Đọc workspace, hiểu mục tiêu và đề xuất cách làm.
- Thực hiện phần code/tài liệu, build và báo lại file đã thay đổi.

## Học viên kiểm tra gì?

- Mở đúng workspace và model bản sao; đọc kế hoạch trước khi bấm chạy.
- Kiểm tra kết quả thật trong Revit và chụp minh chứng dễ đối chiếu.

## SOP duy nhất

1. Liệt kê 3 trường hợp xấu cần test: missing Parameter, duplicate Tag, empty selection hoặc invalid input.
2. Dán prompt yêu cầu AI thêm guard clause và summary.
3. Chạy happy path rồi chạy từng trường hợp xấu.
4. Kiểm tra output và log theo ElementId.

## Minh chứng cần nộp

- Ảnh màn hình kết quả của ngày 12.
- Ảnh sản phẩm đã đăng lên Bảng tin cộng đồng.

**Đăng lên Bảng tin:** Đăng ảnh sản phẩm hoặc kết quả chạy thật, kèm 2–3 câu bạn đã dùng AI như thế nào.

## Điều kiện Đạt

Đạt từ 70/100, có ảnh kết quả thật và không có lỗi nghiêm trọng. Nếu chưa đạt, sửa đúng lý do mentor ghi rồi nộp lại; không bị trừ XP.

## Lỗi thường gặp

- Tool báo thành công dù không có phần tử.
- Duplicate Tag làm command crash.
