# SOP Template — AutoCAD

## Bối cảnh

- AutoCAD version/edition:
- KIT_ROOT:
- Workspace:
- Tên học viên:
- Tên tool:
- Nhánh capstone:

## Mục tiêu và giới hạn

- Vấn đề:
- Input:
- Output:
- File AI được phép sửa:
- File/model không được sửa:

## Quy trình

1. Mở DWG bản sao và đọc tài liệu kit liên quan.
2. Dán prompt vào AI Agent; yêu cầu lập kế hoạch và liệt kê file.
3. Xác nhận kế hoạch trước khi AI sửa code.
4. Build theo profile AutoCAD đã khai báo.
5. NETLOAD/cài chỉ sau khi xác nhận đúng artifact.
6. Chạy happy path, empty input và invalid input.
7. Đối chiếu output trong AutoCAD, lưu log và ghi giới hạn.

## Bằng chứng

Nộp ảnh/video kết quả chạy thật, log build khi yêu cầu, file output/bundle và ba câu: AI đã làm gì, sửa file nào, tôi kiểm chứng ra sao.
