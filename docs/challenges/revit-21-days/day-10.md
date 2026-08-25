# Ngày 10 — Lọc và đếm phần tử bằng FilteredElementCollector

**Hình thức:** Video ngắn · **Thời lượng:** 60 phút

## Hôm nay làm gì?

Xây command lõi dùng chung: lọc Category/Level/View và hiển thị số lượng có thể đối chiếu với Schedule.

## Kết quả cần đạt

Kết quả đếm đúng trên model mẫu và lặp lại được

## AI làm gì?

- Đọc workspace, hiểu mục tiêu và đề xuất cách làm.
- Thực hiện phần code/tài liệu, build và báo lại file đã thay đổi.

## Học viên kiểm tra gì?

- Mở đúng workspace và model bản sao; đọc kế hoạch trước khi bấm chạy.
- Kiểm tra kết quả thật trong Revit và chụp minh chứng dễ đối chiếu.

## SOP duy nhất

1. Mở bản sao model.
2. Dán prompt yêu cầu AI dùng FilteredElementCollector, không hard-code ElementId.
3. AI phải giải thích collector, filter và transaction; command đếm không được mở Transaction.
4. Build Release, cài lại DLL và chạy nút.

## Minh chứng cần nộp

- Ảnh màn hình kết quả của ngày 10.
- Ảnh sản phẩm đã đăng lên Bảng tin cộng đồng.

**Đăng lên Bảng tin:** Đăng ảnh sản phẩm hoặc kết quả chạy thật, kèm 2–3 câu bạn đã dùng AI như thế nào.

## Điều kiện Đạt

Đạt từ 70/100, có ảnh kết quả thật và không có lỗi nghiêm trọng. Nếu chưa đạt, sửa đúng lý do mentor ghi rồi nộp lại; không bị trừ XP.

## Lỗi thường gặp

- Đếm cả type thay vì instance.
- Lọc sai view/level.
