# Ngày 10 — Lọc và đếm bằng FilteredElementCollector

**Hình thức:** Video · 60 phút · **Đầu ra:** command lõi dùng chung.

## SOP

Yêu cầu AI dùng `FilteredElementCollector` để lọc Category/Level/View, không hard-code ElementId. Command chỉ đọc nên không mở Transaction. Build Release, chạy trên model bản sao, đối chiếu số lượng với Schedule/Filter và thử model không có phần tử.

## Bài nộp / đạt

Video 15–30 giây bấm nút, ảnh đối chiếu và reflection về collector. Đạt khi kết quả đúng, ghi rõ phạm vi và có thông báo empty state. Chưa đạt nếu đếm cả type, lọc sai Level hoặc tạo Transaction không cần thiết.
