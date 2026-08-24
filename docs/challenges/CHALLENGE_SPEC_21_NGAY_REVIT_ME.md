# BẢN THIẾT KẾ CHƯƠNG TRÌNH THỬ THÁCH 21 NGÀY
## "21 NGÀY TỰ TAY LÀM TOOL REVIT ME BẰNG AI"
**(Dành cho Kỹ sư Cơ Điện chưa biết code - Dựa trên Bộ Kit Revit API Programming)**

---

### I. TỔNG QUAN CHƯƠNG TRÌNH (OVERVIEW)

* **Tên thử thách**: 21 Ngày Tự Tay Làm Tool Revit ME Bằng AI
* **Slug (Định danh URL)**: `21-ngay-tu-tay-lam-tool-revit-me`
* **Người sáng tạo (Fire Keeper / Leader)**: Phạm Quang Huy - DSCons
* **Đối tượng tham gia**: Kỹ sư Cơ Điện (MEP) đã sử dụng thành thạo Revit thuần túy, chưa biết lập trình (0 know-how C#/Python), chỉ quen hỏi đáp Chatbot Web.
* **Thời lượng**: 21 Ngày (03 Tuần)
* **Hình thức & Chi phí**: Thử thách có phí (2,000,000 - 3,000,000 VNĐ)
* **Kết quả đầu ra (Outcome)**: 01 Add-in Revit ME hoàn chỉnh chạy trực tiếp trên Revit mang tên cá nhân, có giao diện WPF bo góc chuyên nghiệp, đóng gói bộ cài installer tự động và áp dụng vào dự án thực tế tại công ty.

---

### II. CẤU TRÚC 3 TUẦN (WEEKLY ARC)

```
TUẦN 1: NHẬP MÔN AI AGENT & KẾT NỐI REVIT MCP
├── Ngày 01: Khai hỏa - Cài đặt Trợ lý AI Agent (Video 3 phút "cầm tay chỉ việc")
├── Ngày 02: Phân biệt AI Agent vs Web Chatbot - Tại sao AI Agent làm được Tool?
├── Ngày 03: Kích hoạt Bộ Kit DSCons & Chạy Preflight kiểm tra môi trường
├── Ngày 04: Kết nối Revit MCP - Cho AI trò chuyện & khảo sát mô hình ME
├── Ngày 05: Ra lệnh cho MCP đọc Parameter (Duct/Pipe/Equipment)
├── Ngày 06: Truy vấn dữ liệu ME nâng cao bằng MCP (Lọc theo System/Size)
├── Ngày 07: Lập hồ sơ ý tưởng Tool Capstone (TOOL_BRIEF.md)
└── 🔥 NGÀY 08 [LIGHT DAY]: LIVE ZOOM FEEDBACK TUẦN 1 - Review kết quả & Chốt đề tài Capstone

TUẦN 2: LẬP TRÌNH & XÂY DỰNG TOOL CƠ ĐIỆN
├── Ngày 09: Tool Đầu Tiên - Nút "About Me" (Thông Tin Tác Giả) & Ribbon Tên Cá Nhân
├── Ngày 10: Tool ME #1 - Lọc & Đếm số lượng phần tử ME tự động
├── Ngày 11: Tool ME #2 - Gán Tham số Parameter hàng loạt cho Đường ống/Thiết bị
├── Ngày 12: Tool ME #3 - Auto-Tagging ống gió & ống nước
├── Ngày 13: Thiết kế Giao diện Bảng điều khiển WPF bo góc chuẩn DSCons
├── Ngày 14: Kỹ thuật Hot Reload - Vừa mở Revit vừa tinh chỉnh Giao diện & Logic C# 24/7
└── 🔥 NGÀY 15 [LIGHT DAY]: LIVE ZOOM FEEDBACK TUẦN 2 - Review Giao diện & Logic Tool Capstone

TUẦN 3: ĐÓNG GÓI, BẢO MẬT & PHÁT HÀNH TOOL
├── Ngày 16: Tinh chỉnh Logic Tool Capstone nâng cao (Geometry / Excel Report)
├── Ngày 17: Bảo mật Tool & Giới hạn Bản quyền (License Key / Ngày hết hạn Trial)
├── Ngày 18: Đóng gói Tool thành Bộ cài tự động (.exe / .zip) gửi đồng nghiệp
├── Ngày 19: Viết Hướng dẫn sử dụng (User Manual 1 trang / Video 1 phút)
├── Ngày 20: Thử nghiệm Tool trên Dự án Thực tế tại công ty & Quay Video Demo
└── 🏆 NGÀY 21 [LIGHT DAY]: VỀ ĐÍCH GRAND FINALE - Showcase Capstone Tool & Cấp Chứng Chỉ
```

---

### III. CHI TIẾT SOP & BẰNG CHỨNG NỘP BÀI (DAILY SOP & EVIDENCE)

#### 🟢 TUẦN 1: NHẬP MÔN AI AGENT & KẾT NỐI REVIT MCP

* **NGÀY 01: Khai hỏa - Cài đặt "Trợ lý AI Agent" lên máy tính**
  * *Mục tiêu*: Vượt qua rào cản công nghệ, cài thành công 1 AI Agent (Antigravity / Claude Code / Codex).
  * *Thao tác*: Xem Video 3 phút của Thầy Huy -> Tải phần mềm -> Mở thư mục Bộ Kit DSCons.
  * *Bằng chứng (Evidence)*: Ảnh chụp màn hình AI Agent đã nhận diện thư mục Bộ Kit.
  * *Lời Keeper*: *"Chào mừng anh em! Hôm nay không đụng dòng code nào, chỉ cần dán 1 lệnh để bật bộ não AI trên máy mình!"*

* **NGÀY 02: Phân biệt AI Agent vs Web Chatbot**
  * *Mục tiêu*: Hiểu rõ tại sao dùng Chatbot Web (ChatGPT) bị bế tắc, còn AI Agent lại tự động làm được Tool Revit.
  * *Thao tác*: Đọc bài viết phân tích 1 trang -> Thực hành giao nhiệm vụ đơn giản cho AI Agent trên máy.
  * *Bằng chứng*: Ảnh chụp AI Agent tự động trả lời kết quả khảo sát hệ thống.

* **NGÀY 03: Kích hoạt Bộ Kit DSCons & Preflight Môi trường**
  * *Mục tiêu*: Chạy script kiểm tra môi trường Revit trên máy (Revit 2020 - 2026).
  * *Thao tác*: Dán Prompt: `"Đọc SETUP.md và chạy preflight.ps1 cho tôi"`.
  * *Bằng chứng*: Ảnh chụp kết quả `environment-report.json` báo ĐẠT (PASS).

* **NGÀY 04: Kết nối Revit MCP - Khảo sát Mô hình ME Đầu tiên**
  * *Mục tiêu*: Lần đầu tiên cho AI "trò chuyện trực tiếp" với mô hình Revit mà không cần mở bảng Schedule.
  * *Thao tác*: Dán Prompt kích hoạt Revit MCP -> Yêu cầu AI đếm số lượng ống gió Duct và thiết bị ME trong file Revit đang mở.
  * *Bằng chứng*: Ảnh chụp câu trả lời của AI liệt kê chính xác số lượng đối tượng trong file Revit.

* **NGÀY 05: Đọc & Trích xuất Parameter Cơ Điện bằng MCP**
  * *Mục tiêu*: Yêu cầu AI quét toàn bộ Parameter (System Name, Air Flow, Size, Mark) của thiết bị ME.
  * *Thao tác*: Gửi Prompt khảo sát Parameter -> AI xuất bảng tóm tắt Parameter lên cửa sổ chat.
  * *Bằng chứng*: Ảnh chụp danh sách Parameter mà AI trích xuất được từ Revit.

* **NGÀY 06: Truy vấn dữ liệu ME Phức tạp bằng MCP**
  * *Mục tiêu*: Thực hiện các câu hỏi lọc chuyên sâu (VD: "Lọc tất cả ống gió kích thước > 300mm ở Tầng 2").
  * *Thao tác*: Đặt câu hỏi bằng tiếng Việt -> AI dùng MCP truy vấn mô hình Revit và trả kết quả.
  * *Bằng chứng*: Ảnh chụp kết quả lọc đối tượng thành công.

* **NGÀY 07: Lập Hồ sơ Ý tưởng Tool Capstone (TOOL_BRIEF.md)**
  * *Mục tiêu*: Xác định bài toán ME thực tế tại công ty bạn muốn giải quyết ở Tuần 2 & Tuần 3.
  * *Thao tác*: Điền mẫu `templates/TOOL_BRIEF.md` (Input là gì, Logic xử lý là gì, Output ra sao).
  * *Bằng chứng*: File `TOOL_BRIEF.md` hoàn chỉnh.

* **🔥 NGÀY 08 [LIGHT DAY]: LIVE ZOOM FEEDBACK TUẦN 1**
  * *Khối lượng kỹ thuật*: Nhẹ (Chỉ hoàn thiện file Tool Brief).
  * *Hoạt động chính*:
    1. Tham gia buổi Live Zoom với Thầy Phạm Quang Huy.
    2. Thầy Huy chữa bài, tuyên dương kết quả Tuần 1 và góp ý định hướng Tool Capstone cho từng học viên.

---

#### 🔵 TUẦN 2: LẬP TRÌNH & XÂY DỰNG TOOL CƠ ĐIỆN

* **NGÀY 09: Tool Đầu Tiên — Nút "About Me" (Thông Tin Tác Giả) & Ribbon Tên Cá Nhân**
  * *Mục tiêu*: Lần đầu tiên thấy Tab Ribbon mang tên mình xuất hiện trên Revit và tự tay sở hữu 01 Tool đầu tiên: Nút "About Me" chứa thông tin cá nhân chuyên nghiệp.
  * *Thao tác*: Dán Prompt bootstrap & cá nhân hóa -> AI hỏi thông tin (Họ tên, SĐT, Chức vụ, Công ty) -> AI tự tạo Tab Ribbon tên riêng (VD: `QUANG HUY MEP`) + Nút "About Me" -> Bấm nút trên Revit sẽ bật lên cửa sổ Giới thiệu Tác giả Add-in cực kỳ hoành tráng.
  * *Bằng chứng*: Ảnh chụp màn hình Revit có Tab Ribbon tên riêng và cửa sổ "About Me" chứa đầy đủ thông tin cá nhân của bạn đang mở.

* **NGÀY 10: Tool ME #1 - Lọc & Đếm phần tử ME tự động**
  * *Mục tiêu*: AI viết code C# hoàn chỉnh đầu tiên: Lọc và đếm phần tử ME theo View/Level.
  * *Thao tác*: Dán Prompt tạo lệnh `/lay-doi-tuong` -> Test nút bấm trên Ribbon Revit.
  * *Bằng chứng*: Video 15s bấm nút trên Ribbon và xuất hộp thoại kết quả đếm phần tử.

* **NGÀY 11: Tool ME #2 - Gán Parameter (Set Parameter) hàng loạt**
  * *Mục tiêu*: Tự động điền dữ liệu (Mark, Comments, System Name) cho hàng loạt ống gió/ống nước.
  * *Thao tác*: Dán Prompt tạo lệnh `/tham-so-hinh-hoc` -> Kiểm tra thuộc tính đối tượng trong Revit sau khi chạy tool.
  * *Bằng chứng*: Ảnh trước & sau khi chạy tool thể hiện Parameter đã được điền tự động.

* **NGÀY 12: Tool ME #3 - Auto-Tagging Ống gió / Ống nước**
  * *Mục tiêu*: Tự động đặt Tag kích thước cho toàn bộ ống gió/ống nước trong View hiện hành.
  * *Thao tác*: Dán Prompt tạo lệnh `/tags-sheets` -> Chạy thử trong View bản vẽ ME.
  * *Bằng chứng*: Ảnh màn hình bản vẽ bản vẽ ME tràn ngập các Tag được đặt tự động thẳng hàng.

* **NGÀY 13: Thiết kế Giao diện Bảng điều khiển WPF Bo Góc**
  * *Mục tiêu*: Nâng cấp Tool từ ô Popup đơn sơ thành Bảng điều khiển WPF sang trọng chuẩn DSCons.
  * *Thao tác*: Dán Prompt tạo giao diện `/wpf` -> AI tự dựng file XAML bo góc, chuẩn màu thương hiệu.
  * *Bằng chứng*: Ảnh chụp cửa sổ WPF giao diện tool vừa dựng.

* **NGÀY 14: Kỹ thuật Hot Reload - Sửa Tool không cần tắt Revit**
  * *Mục tiêu*: Làm chủ kỹ thuật Hot Reload, thoải mái bảo AI chỉnh sửa giao diện & logic code và test lại ngay lập tức mà không phải tắt/mở lại Revit.
  * *Thao tác*: Bật tính năng Hot Reload -> Yêu cầu AI đổi màu nút/thêm chức năng -> Bấm chạy lại trong Revit 24/7.
  * *Bằng chứng*: Video quay thao tác vừa bảo AI sửa code vừa test trực tiếp trong Revit.

* **🔥 NGÀY 15 [LIGHT DAY]: LIVE ZOOM FEEDBACK TUẦN 2**
  * *Khối lượng kỹ thuật*: Nhẹ (Chỉ quay video test Hot Reload & Giao diện WPF).
  * *Hoạt động chính*:
    1. Tham gia buổi Live Zoom Feedback Tuần 2 với Thầy Huy.
    2. Thầy Huy Review thiết kế giao diện WPF và kiểm tra logic code Capstone Tool của từng người.

---

#### 🟠 TUẦN 3: ĐÓNG GÓI, BẢO MẬT & PHÁT HÀNH TOOL

* **NGÀY 16: Tinh chỉnh Logic Tool Capstone Nâng cao**
  * *Mục tiêu*: Hoàn thiện tính năng nâng cao cho Tool Capstone (Xử lý hình học Geometry, BoundingBox va chạm hoặc Xuất báo cáo Excel).
  * *Thao tác*: Dán Prompt hoàn thiện logic từ `TOOL_BRIEF.md` -> AI tinh chỉnh code C#.
  * *Bằng chứng*: Video test chạy thành công bài toán Capstone trên mô hình thực tế.

* **NGÀY 17: Bảo mật Tool & Giới hạn Bản quyền (License Key & Expiry Date)**
  * *Mục tiêu*: Thêm tính năng khóa bản quyền theo máy (HWID/License Key), cài đặt số ngày hết hạn dùng thử (Trial Expiry Date) để bảo vệ Add-in trước khi chia sẻ.
  * *Thao tác*: Dán Prompt thêm tính năng License/Expiry -> AI tự chèn logic khóa bản quyền vào code C#.
  * *Bằng chứng*: Ảnh chụp cửa sổ thông báo bản quyền/hạn sử dụng của Tool khi kích hoạt.

* **NGÀY 18: Đóng gói Tool thành Bộ cài tự động (.exe / .zip)**
  * *Mục tiêu*: Đóng gói toàn bộ file add-in thành 1 bộ cài duy nhất sẵn sàng chia sẻ.
  * *Thao tác*: Dán Prompt chạy script `/dong-goi` -> AI tự gom file DLL, Addin manifest và nén thành bộ cài.
  * *Bằng chứng*: File bộ cài `.zip` hoặc `.exe` được tạo ra trong thư mục `dist/`.

* **NGÀY 19: Soạn thảo Hướng dẫn sử dụng (User Manual)**
  * *Mục tiêu*: Viết 1 file HDSD ngắn gọn (hoặc video 1 phút) để đồng nghiệp trong công ty đọc là biết dùng ngay.
  * *Thao tác*: Dán Prompt yêu cầu AI tự tạo file `HUONG_DAN_SU_DUNG.md` dựa trên tính năng của tool.
  * *Bằng chứng*: File HDSD hoàn chỉnh dạng PDF/Markdown.

* **NGÀY 20: Thử nghiệm thực tế tại Công ty & Quay Video Demo**
  * *Mục tiêu*: Gửi bộ cài cho 1 đồng nghiệp cài thử (hoặc tự cài trên máy khác) -> Quay video Demo từ A-Z.
  * *Thao tác*: Cài đặt add-in từ bộ cài đã đóng gói -> Thao tác sử dụng tool trên dự án thật của công ty -> Quay video màn hình 2-3 phút.
  * *Bằng chứng*: Link Video Demo (Loom/YouTube/Facebook).

* **🏆 NGÀY 21 [LIGHT DAY]: VỀ ĐÍCH GRAND FINALE & TỔNG KẾT**
  * *Khối lượng kỹ thuật*: Nhẹ (Chỉ nộp link bài đăng tổng kết).
  * *Hoạt động chính*:
    1. Đăng bài tổng kết hành trình 21 ngày lột xác lên Cộng đồng DSCons.
    2. Tham gia Lễ Tổng Kết Về Đích (Grand Finale) qua Zoom.
    3. Thầy Phạm Quang Huy vinh danh các chiến binh hoàn thành và trao Chứng chỉ Thử Thách.

---

### IV. QUY TẮC ĐÁNH GIÁ TỰ ĐỘNG (AI AUTO-REVIEW GUIDELINES)

* **Tiêu chuẩn Duyệt Bài Nộp (Pass Criteria)**:
  * Nộp đủ Bằng chứng (Evidence) theo đúng định dạng được yêu cầu (Ảnh/Video/Link/File).
  * Bài làm thể hiện rõ cá nhân hóa (Tên học viên xuất hiện trên Ribbon/WPF/About Me).
  * Không vi phạm các lỗi Red Flags.
* **Cờ Đỏ Tự Động Từ Chối (Red Flags)**:
  * Nộp ảnh/video không có tên cá nhân trên Ribbon Revit.
  * Nộp bài muộn quá 24h mà không xin phép Fire Keeper.
  * Copy bài làm hoặc video của học viên khác.

---
*(Bản thiết kế này đã được lưu vào hệ thống để làm tài liệu triển khai Thử thách cho DSCons)*
