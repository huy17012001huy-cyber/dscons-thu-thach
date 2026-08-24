# SOP Template — AI Agent làm, học viên kiểm chứng

## Bối cảnh

- Revit version: `...`
- .NET/Visual Studio: `...`
- Tên học viên: `...`
- Tên tool: `...`
- Nhánh capstone: `counting | parameter | tagging | report`
- Workspace: `...`
- Model test: bản sao/anonymized, không phải file gốc

## Mục tiêu

Mô tả một output quan sát được trong Revit: `...`

## Ràng buộc an toàn

1. Không xóa file hoặc sửa model gốc.
2. Đọc file hiện có và lập kế hoạch trước khi sửa.
3. Tạo backup trước Transaction/batch operation.
4. Không đoán Revit API khi chưa kiểm tra version.
5. Không đưa model/dữ liệu mật lên AI.

## Prompt copy vào AI Agent

```text
Hãy đọc workspace và tài liệu liên quan trước. Lập kế hoạch, liệt kê file sẽ sửa,
lệnh build, cách rollback và checklist test; chờ tôi xác nhận kế hoạch.
Sau khi xác nhận, hãy tạo/sửa code theo mục tiêu, build Release, giải thích file
đã đổi, nêu lỗi còn lại và hướng dẫn tôi kiểm tra output trong Revit.
Không xóa file, không sửa model gốc, không đoán API, không log dữ liệu nhạy cảm.
```

## Khi AI hỏi lại

Trả lời đúng Revit version, đường dẫn workspace, Category/Level/System, input và output. Nếu chưa biết, nói “chưa xác định, hãy đề xuất cách đọc read-only”; không bịa giá trị.

## Sau khi AI hoàn thành

- Lưu log build và danh sách file thay đổi.
- Chạy bản Release trên model bản sao.
- Đối chiếu bằng Schedule/Properties hoặc file output độc lập.
- Nếu lỗi: gửi phần log từ exception/error đến stack trace liên quan, bỏ token/đường dẫn bí mật.
- Chỉ nộp khi có kết quả thật, tên cá nhân hóa và mô tả cách kiểm chứng.
