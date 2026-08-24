# Nhánh capstone Navisworks

## Search Set

Tạo rule theo Category/Property/Value, lưu set không trùng tên và hiển thị empty/invalid state.

## Model QA

Kiểm tra Property/category/source, phát hiện missing hoặc sai quy ước và xuất danh sách cần xử lý.

## Clash và Coordination

Chỉ dành cho Navisworks Manage có dữ liệu Clash thật. Tolerance phải do người dùng cung cấp; không tự tạo clash/tolerance giả.

## Viewpoint

Kết nối result với selection/viewpoint/section box khi API đã qua capability gate và có fallback theo version.

## Reporting

Xuất HTML/CSV/Excel tùy capability, có source, identifier, timestamp, summary và không ghi đè report cũ.
