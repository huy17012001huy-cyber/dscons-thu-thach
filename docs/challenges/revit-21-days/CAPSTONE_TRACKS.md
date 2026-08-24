# Bốn nhánh capstone

## 1. Counting / Filtering — Đếm và lọc

Phù hợp khi công việc lặp lại là đếm Duct/Pipe/Equipment, lọc theo Level/System/Size/View. Output là số lượng, danh sách ElementId hoặc bảng lọc. Không chọn phạm vi “phân tích toàn bộ dự án”.

## 2. Parameter — Gán Parameter hàng loạt

Phù hợp khi cần set Mark, Comments, System Name hoặc parameter tùy biến. Bắt buộc xử lý Parameter thiếu, read-only, kiểu dữ liệu sai và có summary trước/sau.

## 3. Tagging — Auto-Tag

Phù hợp khi cần đặt tag cho phần tử trong view. Bắt buộc kiểm tra view hỗ trợ, tag family, phần tử đã có tag/trùng tag và trường hợp không có tag type.

## 4. Report / Export — Báo cáo

Phù hợp khi cần xuất bảng kiểm kê sang CSV/XLSX/Markdown. Output phải có cột định nghĩa rõ, đơn vị đúng, đường dẫn an toàn và không chứa dữ liệu mật ngoài phạm vi.

## Cách chọn

Chọn một workflow mất thời gian hoặc dễ sai khi làm tay, có input rõ và output đo được trong tối đa hai tuần. Từ ngày 11 có thể thêm một nâng cấp, nhưng không đổi nhánh giữa chừng nếu chưa cập nhật `TOOL_BRIEF.md` và được mentor xác nhận.
