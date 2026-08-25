# Ngày 13 — Thiết kế cửa sổ WPF dùng được trong Revit

**Hình thức:** Video ngắn · **Thời lượng:** 60 phút

## Hôm nay làm gì?

Tạo UI WPF có bố cục rõ, màu DSCons, loading, thành công/thất bại; không che Revit và có trạng thái đóng/mở ổn định.

## Kết quả cần đạt

Giao diện giúp kỹ sư biết đang chạy gì và kết quả là gì

## AI làm gì?

- Đọc workspace, hiểu mục tiêu và đề xuất cách làm.
- Thực hiện phần code/tài liệu, build và báo lại file đã thay đổi.

## Học viên kiểm tra gì?

- Mở đúng workspace và model bản sao; đọc kế hoạch trước khi bấm chạy.
- Kiểm tra kết quả thật trong Revit và chụp minh chứng dễ đối chiếu.

## SOP duy nhất

1. Mở XAML và code-behind do AI tạo trong workspace.
2. Dán prompt yêu cầu AI giữ logic hiện có, chỉ thay UI và binding cần thiết.
3. Build và mở cửa sổ từ Ribbon.
4. Thử loading, success, error, empty state và đóng cửa sổ.

## Minh chứng cần nộp

- Ảnh màn hình kết quả của ngày 13.
- Ảnh sản phẩm đã đăng lên Bảng tin cộng đồng.

**Đăng lên Bảng tin:** Đăng ảnh sản phẩm hoặc kết quả chạy thật, kèm 2–3 câu bạn đã dùng AI như thế nào.

## Điều kiện Đạt

Đạt từ 70/100, có ảnh kết quả thật và không có lỗi nghiêm trọng. Nếu chưa đạt, sửa đúng lý do mentor ghi rồi nộp lại; không bị trừ XP.

## Lỗi thường gặp

- UI giả nút nhưng không gọi command.
- Không có loading khiến người dùng bấm lặp.
