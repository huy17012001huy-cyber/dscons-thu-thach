# Ngày 18 — Build Release và đóng gói bộ cài

**Hình thức:** Video ngắn · **Thời lượng:** 60 phút

## Hôm nay làm gì?

Gom DLL, .addin, dependency, icon và cấu hình thành ZIP/installer có thể cài lại.

## Kết quả cần đạt

Người khác có thể cài và mở add-in từ bộ cài đã nộp

## AI làm gì?

- Đọc workspace, hiểu mục tiêu và đề xuất cách làm.
- Thực hiện phần code/tài liệu, build và báo lại file đã thay đổi.

## Học viên kiểm tra gì?

- Mở đúng workspace và model bản sao; đọc kế hoạch trước khi bấm chạy.
- Kiểm tra kết quả thật trong Revit và chụp minh chứng dễ đối chiếu.

## SOP duy nhất

1. Yêu cầu AI lập danh sách artifact và dependency.
2. Xóa bin/obj cũ rồi build Release.
3. Tạo dist với DLL, .addin, dependency, icon, README và uninstall/rollback.
4. Cài từ ZIP vào môi trường sạch hoặc thư mục test.

## Minh chứng cần nộp

- Ảnh màn hình kết quả của ngày 18.
- Ảnh sản phẩm đã đăng lên Bảng tin cộng đồng.

**Đăng lên Bảng tin:** Đăng ảnh sản phẩm hoặc kết quả chạy thật, kèm 2–3 câu bạn đã dùng AI như thế nào.

## Điều kiện Đạt

Đạt từ 70/100, có ảnh kết quả thật và không có lỗi nghiêm trọng. Nếu chưa đạt, sửa đúng lý do mentor ghi rồi nộp lại; không bị trừ XP.

## Lỗi thường gặp

- Thiếu dependency.
- Manifest trỏ sai đường dẫn.
