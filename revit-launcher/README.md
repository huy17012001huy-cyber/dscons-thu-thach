# DSCons Revit Launcher MVP

Launcher độc lập cho Ribbon `DSCons`; không sửa Bộ Kit học viên. Bản MVP kiểm tra luồng license với hai command nhúng sẵn: `Test 1` và `Test 2`.

## Hành vi

1. Mở Revit → DSCons → **Đăng nhập DSCons**.
2. Add-in mở browser đến website DSCons để người dùng xác nhận Google OAuth.
3. Trở lại Revit → bấm **Làm mới quyền**.
4. Nút Test 1/Test 2 chỉ bật nếu tài khoản đã mua tool tương ứng.

Token được lưu bằng Windows DPAPI tại `%LOCALAPPDATA%\DSCons\RevitLauncher`; không có mật khẩu Google, refresh token Google hoặc API secret trong DLL.

## Build

Dùng profile Revit đã runtime-test từ Bộ Kit. Truyền `RevitVersion`, `RevitTargetFramework`, `RevitApiPath`, `RevitApiUiPath` giống script build của Kit. Không tự tuyên bố phiên bản được hỗ trợ chỉ vì build thành công.

Ví dụ (thay toàn bộ đường dẫn bằng đường dẫn Revit đã kiểm tra trên máy build):

```powershell
dotnet build .\DSCons.Revit.Launcher.csproj -c Release `
  -p:RevitVersion=2024 `
  -p:RevitTargetFramework=net48 `
  -p:RevitApiPath='C:\Program Files\Autodesk\Revit 2024\RevitAPI.dll' `
  -p:RevitApiUiPath='C:\Program Files\Autodesk\Revit 2024\RevitAPIUI.dll'
```

Sao chép `DSCons.Revit.Launcher.addin` vào `%AppData%\Autodesk\Revit\Addins\<version>`, đặt DLL/config đúng thư mục release, rồi mở Revit.

`DSCONS_API_BASE_URL` trong `DSCons.Revit.Launcher.config` phải là domain HTTPS thật khi phát hành. Local dùng `http://localhost:8080` chỉ để test. Không commit file cấu hình phát hành có URL/private key. Launcher không nhận, không lưu mật khẩu Google và không ghi token ra log.

## Bộ cài Ribbon

Chạy `Build-DSConsRevitInstaller.ps1` trên máy build để tạo `DSCons-Revit-Launcher-Setup.exe` self-contained trong `dist/`. Học viên chỉ cần double-click file EXE; không cần mở PowerShell hay cài .NET. Mặc định EXE dùng local `http://localhost:8080`; khi phát hành cần build kèm domain HTTPS DSCons hoặc chạy với tham số `--api-url`.

Installer tự tìm Revit 2019–2027, tạo file `.addin` trong AppData và đặt launcher vào LocalAppData của từng Windows user. Bản 2020, 2022, 2024 và 2026 dùng payload gần nhất trong cùng nhóm .NET để kiểm tra tương thích; chỉ gọi một phiên bản là runtime-certified khi đã mở Revit và chạy thử thật.

## Giới hạn MVP

Test 1 và Test 2 là command nhúng trong launcher, chưa tải package tool từ server. Cơ chế URL tải có chữ ký và kiểm tra checksum/signature là hạng mục bắt buộc trước khi phân phối DLL tool thật.
