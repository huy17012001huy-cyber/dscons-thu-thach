# SOP copy vào AI Agent

## Bối cảnh

- Phiên bản Revit: [điền phiên bản]
- Tên tool: [điền tên]
- Workspace: [chọn thư mục làm việc]
- Model kiểm thử: [bản sao/model mẫu]

## Mục tiêu

[Nói bằng ngôn ngữ công việc: tôi muốn giảm thao tác nào và kết quả cần thấy là gì?]

## Ràng buộc an toàn

- Không sửa model gốc hoặc xóa file ngoài workspace.
- Đọc những gì đang có và nói kế hoạch trước khi sửa.
- Backup trước thao tác thay đổi dữ liệu.
- Không đoán API hoặc phiên bản; nếu chưa chắc phải hỏi lại.
- Không đưa dữ liệu mật, token hoặc thông tin công ty lên AI.

## Yêu cầu AI

1. Đọc workspace và giải thích ngắn gọn những gì đã tìm thấy.
2. Nêu kế hoạch, file sẽ thay đổi, lệnh build và cách kiểm tra; chờ tôi xác nhận.
3. Thực hiện sau khi tôi xác nhận.
4. Build/chạy theo phiên bản Revit thật.
5. Báo lại file đã đổi, kết quả, lỗi còn lại và cách rollback.

## Việc tôi phải kiểm tra

- Mở Revit bằng model bản sao.
- Bấm tool và kiểm tra kết quả với Properties, Schedule hoặc cách đếm thủ công.
- Chụp ảnh kết quả thật và ghi ngắn gọn cách tôi đã kiểm chứng.
