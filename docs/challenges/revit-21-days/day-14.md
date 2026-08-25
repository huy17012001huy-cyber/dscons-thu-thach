# Ngày 14 — Debug build, Revit API và Hot Reload có kiểm soát

**Hình thức:** Video ngắn · **Thời lượng:** 60 phút

## Hôm nay làm gì?

Học quy trình đọc lỗi, phân loại nguyên nhân và đưa đúng log cho AI; không copy lỗi mù quáng.

## Kết quả cần đạt

Một lỗi có nguyên nhân và bằng chứng regression test

## AI làm gì?

- Đọc workspace, hiểu mục tiêu và đề xuất cách làm.
- Thực hiện phần code/tài liệu, build và báo lại file đã thay đổi.

## Học viên kiểm tra gì?

- Mở đúng workspace và model bản sao; đọc kế hoạch trước khi bấm chạy.
- Kiểm tra kết quả thật trong Revit và chụp minh chứng dễ đối chiếu.

## SOP duy nhất

1. Tạo một lỗi an toàn ở bản sao branch hoặc dùng lỗi có sẵn.
2. Copy từ dòng exception/build error đến stack trace liên quan.
3. Dán prompt debug yêu cầu AI giải thích trước khi sửa.
4. AI liệt kê file thay đổi và build lại.

## Minh chứng cần nộp

- Ảnh màn hình kết quả của ngày 14.
- Video ngắn chứng minh thao tác chính chạy thật.

## Điều kiện Đạt

Đạt từ 70/100, có ảnh kết quả thật và không có lỗi nghiêm trọng. Nếu chưa đạt, sửa đúng lý do mentor ghi rồi nộp lại; không bị trừ XP.

## Lỗi thường gặp

- Gửi toàn bộ log có secret.
- Sửa nhiều file không liên quan.
