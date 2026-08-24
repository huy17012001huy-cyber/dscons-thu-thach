# Ngày 3 — Chạy preflight môi trường Revit

**Hình thức:** Video · 45 phút · **Đầu ra:** `environment-report.json` hoặc ảnh PASS.

## SOP

1. Mở PowerShell tại root workspace.
2. Dán prompt yêu cầu AI đọc script preflight trước khi chạy.
3. Cho chạy kiểm tra Windows, Revit, .NET SDK, Visual Studio/Build Tools, Git và Bộ Kit.
4. Lưu output; nếu FAIL, gửi đúng đoạn ERROR đến summary cho AI.
5. Kiểm tra lại sau sửa và ghi lỗi chưa sửa, không giấu FAIL.

## Kiểm chứng và bài nộp

Report phải có từng thành phần PASS/FAIL, version Revit và danh sách lỗi đã xử lý. Nộp report/ảnh, log sửa lỗi và reflection. Không đạt nếu chỉ nói “máy đã cài” nhưng không có output kiểm tra.
