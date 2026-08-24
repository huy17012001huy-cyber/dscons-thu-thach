# QA, ComposePost và Environment-specific Login

## Overview

Thực hiện một vòng QA có dữ liệu mẫu `[TEST]` trên local/staging an toàn, cải thiện trình soạn bài theo mô hình “viết như tin nhắn + xem trước trực quan”, và chuyển public authentication sang Google OAuth trên staging/production; local vẫn hỗ trợ mật khẩu để phát triển. Không sử dụng dữ liệu production và không làm mất membership, role, bài viết hoặc lịch sử của tài khoản hiện có.

## Project Type

WEB full-stack: Laravel 12, Livewire 3, Blade, Tailwind CSS 4, PHP 8.4, PostgreSQL 17; automated browser QA bằng Playwright.

## Baseline đã xác nhận

- `laravel/socialite` đã có trong `composer.json` và `composer.lock`; chưa có Google service config, OAuth routes/controller hoặc `google_id`.
- Public login hiện là `app/Livewire/Auth/LoginForm.php` với email/password; `/register`, forgot-password và reset-password vẫn tồn tại ở guest routes.
- Tài khoản do `AdminUsers` và `RegisterWebhookController` tạo vẫn sinh/lưu/truyền password; welcome email cũng hiển thị password/reset link.
- `ComposePost` đã lưu Markdown, có toolbar cơ bản, giới hạn 5 bài/ngày, upload ảnh và dùng `PostContentRenderer` để strip HTML/link không an toàn; chưa có chế độ Soạn thảo/Xem trước.
- Repo chưa có Playwright suite riêng. README yêu cầu local bằng Docker/PostgreSQL; PHPUnit dùng SQLite in-memory.
- Worktree đang có thay đổi chưa commit ở nhiều file. Mọi implementation phải bắt đầu bằng baseline test/diff và không được ghi đè các thay đổi đó.
- Chưa có Google Client ID/Secret; OAuth thật chỉ nghiệm thu sau khi credential và callback URL của môi trường được cung cấp.

## Success Criteria

- Có QA report phân loại từng flow là `PASS`, `BUG`, `UX issue` hoặc `BLOCKED`, kèm screenshot/trace trong `output/playwright/`.
- Có dataset mẫu `[TEST]` đủ cho feed, CỐT, signal, Q&A, Academy, event, leaderboard, Challenge Revit, marketplace, profile/notification/search; có cách nhận diện và dọn dữ liệu test.
- ComposePost có hai mode `Soạn thảo`/`Xem trước`; preview dùng cùng `PostContentRenderer`, không mất nội dung khi chuyển mode, vẫn validate khi submit và responsive/a11y trên mobile.
- Public `/login` dùng `AUTH_MODE`: local có thể dùng password, staging/production chỉ Google; callback xử lý đúng account linking, registration policy, membership/role và trạng thái banned/expired.
- Ở Google-only mode không còn password/reset flow trong public UI; admin/webhook/welcome email provision tài khoản chờ Google login và không gửi password.
- Có feature/unit tests cho OAuth và ComposePost, Playwright smoke flows, `git diff --check`, Blade/build/test validation; baseline AIP/XP failure được báo cáo riêng nếu không liên quan.

## Tech Stack và nguyên tắc

- Giữ Laravel Socialite hiện có; dùng session-based Laravel auth, không tạo JWT hoặc auth stack mới.
- Dùng migration nullable + unique cho `users.google_id`; giữ `password` nullable để tương thích dữ liệu cũ nhưng không dùng cho public login.
- Dùng `PostContentRenderer` làm nguồn render duy nhất của preview và post đã đăng; tiếp tục strip HTML nguy hiểm, chỉ cho phép URL an toàn và YouTube embed theo allowlist hiện tại.
- Dùng PHPUnit/Livewire cho logic và Playwright Page Object Model cho smoke/regression; test data độc lập, không phụ thuộc seed của test khác.

## Task Breakdown

### T0 — Khóa baseline và chuẩn bị môi trường QA

- **Agent/skills:** `project-planner` + `qa-automation-engineer`; `systematic-debugging`, `webapp-testing`, `testing-patterns`.
- **Phụ thuộc:** Không.
- **Input → Output:** `git status`, route/component inventory, test baseline, Docker/local runbook → checklist baseline và danh sách failure hiện hữu.
- **Việc chính:** chạy test hiện có, `route:list`, kiểm tra migration/seed, xác nhận brand `registration_mode`, kiểm tra Playwright/browser availability; ghi rõ các thay đổi dirty worktree cần bảo toàn.
- **Verify:** baseline log có command, timestamp, pass/fail và phân biệt failure AIP/XP với failure liên quan task; không sửa code trong bước này.

### T1 — Tạo dữ liệu mẫu `[TEST]` và bộ khung QA report

- **Agent/skills:** `qa-automation-engineer`; `testing-patterns`, `webapp-testing`.
- **Phụ thuộc:** T0.
- **Input → Output:** schema/model hiện tại → seeder/command dữ liệu mẫu idempotent, tài khoản QA theo role/trạng thái, cấu trúc `output/playwright/` và `docs/qa/qa-report.md`.
- **Việc chính:** tạo bản ghi có prefix `[TEST]` cho các nhóm trong yêu cầu; tạo member active, unverified, expired, banned và admin test; ghi quy tắc không chạy seeder trên production và cách cleanup theo marker.
- **Verify:** chạy seed hai lần không nhân bản ngoài ý muốn; mọi record liên quan truy được bằng marker; report có cột feature, precondition, expected/actual, status, evidence, severity và owner.

### T2 — Chạy QA route/UI/business flow toàn hệ thống

- **Agent/skills:** `qa-automation-engineer` + `frontend-specialist`; `webapp-testing`, `testing-patterns`, `web-design-guidelines`.
- **Phụ thuộc:** T1.
- **Input → Output:** dataset `[TEST]`, route inventory và acceptance matrix → Playwright smoke/regression specs, screenshot/trace, QA report.
- **Coverage:** guest/member redirect; empty/non-empty/error states; CRUD có quyền; validation/character limit/daily limit; pagination/tab/filter; joined/not-joined; profile/notification/search; feed/CỐT/signal/Q&A; Academy/course/lesson; event/register; leaderboard; Challenge Revit; marketplace/free purchase/owned state; admin critical flows.
- **Verify:** desktop + mobile viewport, keyboard/focus cơ bản, no console error cho smoke flow; dùng locator theo role/label/test id, auto-wait, không hardcode sleep; mọi `BUG` có reproduction steps và trace/screenshot.

### T3 — Cải thiện ComposePost và preview trực quan

- **Agent/skills:** `frontend-specialist`; `frontend-design`, `web-design-guidelines`, `clean-code`.
- **Phụ thuộc:** T0; có thể song song T1.
- **Files dự kiến:** `app/Livewire/ComposePost.php`, `resources/views/livewire/compose-post.blade.php`, `resources/js/app.js`, `resources/css/app.css`; chỉ cập nhật consumer nếu contract renderer thay đổi.
- **Input → Output:** editor Markdown hiện tại → hai mode `Soạn thảo`/`Xem trước`, hướng dẫn ngắn, toolbar có label/tooltip rõ (`In đậm`, `In nghiêng`, danh sách, trích dẫn, link, video, undo/redo), trạng thái empty/validation/loading/focus/keyboard.
- **Quy tắc:** giữ payload Markdown và hỗ trợ ảnh, signal, pillar, topic, giới hạn 5 bài/ngày; preview gọi `PostContentRenderer`; không expose Markdown raw như yêu cầu UX; không render HTML nguy hiểm/link không an toàn; toolbar mobile không tràn/che nút `Đăng`.
- **Verify:** chuyển mode nhiều lần không mất nội dung; preview `**đậm**`, `*nghiêng*`, list, quote, safe link, YouTube; payload sau đăng render giống preview; validation vẫn chặn submit ở preview; keyboard/focus/ARIA và mobile viewport đạt checklist.

### T4 — Bổ sung regression/security tests cho ComposePost và QA findings

- **Agent/skills:** `test-engineer`/`qa-automation-engineer`; `testing-patterns`, `webapp-testing`, `vulnerability-scanner`.
- **Phụ thuộc:** T3.
- **Input → Output:** acceptance cases T2/T3 → PHPUnit/Livewire tests và Playwright editor tests; fix các bug P0/P1 được xác nhận.
- **Cases bắt buộc:** Markdown format/list/link/YouTube; script/event-handler/unsafe URL bị loại; mode switching; preview-empty; validation; daily limit; upload ảnh; keyboard/mobile; exact post-vs-preview HTML.
- **Verify:** `php artisan test --filter=ComposePost`, `PostXssTest` và Playwright editor suite pass; security finding có severity và evidence trước khi fix; không làm thay đổi behavior renderer ngoài mong muốn.

### T5 — Thiết kế và triển khai Google OAuth/account linking

- **Agent/skills:** `backend-specialist` + `database-architect` + `security-auditor`; `api-patterns`, `database-design`, `vulnerability-scanner`, `testing-patterns`.
- **Phụ thuộc:** T0; migration phải hoàn tất trước callback implementation.
- **Files dự kiến:** migration mới cho `users.google_id`, `app/Models/User.php`, `config/services.php`, `.env.example` và env docs, routes/web.php, controller/service Google auth mới, auth views/tests.
- **Input → Output:** Socialite 5.20 và auth/membership/brand policy hiện tại → redirect/callback có state/scopes, provider error handling và callback service rõ ràng.
- **Callback order:** tìm `google_id`; nếu chưa có thì tìm email exact và chỉ auto-link khi tài khoản local đã verified; từ chối nếu provider thiếu email hoặc email chưa verified; nếu chưa có user thì chỉ tạo khi brand `registration_mode` cho phép, tạo username theo rule hiện tại, mark verified và tạo membership theo policy; banned/expired fail closed; giữ admin/owner role và lịch sử.
- **Verify:** fake Socialite tests cho new user, existing google id, verified email linking, unverified local/provider, missing email, registration closed, banned/expired, provider cancel/error; assert session regeneration, redirect, no privilege loss và không log secret/token.

### T6 — Loại bỏ public password login và cập nhật các đường provision

- **Agent/skills:** `backend-specialist` + `security-auditor` + `frontend-specialist`; `api-patterns`, `vulnerability-scanner`, `web-design-guidelines`.
- **Phụ thuộc:** T5.
- **Files dự kiến:** `app/Livewire/Auth/LoginForm.php`, login Blade, register/forgot/reset components và views, `routes/web.php`, `app/Livewire/AdminUsers.php`, `app/Http/Controllers/RegisterWebhookController.php`, `app/Mail/WelcomeMemberMail.php`, email view và test liên quan.
- **Input → Output:** Google callback/route contract → `/login` chỉ hiển thị nút Google; `/register`, forgot/reset redirect login với thông báo Google-only; admin/webhook tạo user không password và welcome email chỉ hướng dẫn Google login.
- **Bảo toàn:** password column vẫn nullable cho dữ liệu cũ; rà soát impersonation/admin nội bộ để không làm mất hỗ trợ quản trị; chặn password form/POST public, không gửi password/reset link, không auto-link email chưa verified.
- **Verify:** route/UI audit không còn public password form hoặc reset link; tests xác nhận legacy URLs redirect đúng; admin/webhook không trả/gửi password; existing admin/owner membership, roles, posts và history nguyên vẹn; ban/expired không bypass được bằng Google.

### T7 — Tài liệu OAuth, nghiệm thu tích hợp và rollback gate

- **Agent/skills:** `security-auditor` + `qa-automation-engineer` + `documentation-writer`; `vulnerability-scanner`, `webapp-testing`, `web-design-guidelines`.
- **Phụ thuộc:** T2, T4, T6.
- **Input → Output:** test/report/config đã hoàn tất → tài liệu local/staging/production gồm authorized origins, callback URLs, env vars, credential ownership, log/incident notes và rollback plan.
- **Verify:** fake provider pass toàn bộ; OAuth thật chỉ chạy khi có credential đúng môi trường; staging callback exact match và HTTPS; failure/cancel quay về login rõ ràng; QA report hoàn chỉnh và có screenshot/trace.

## Phase X: Verification

- [x] `git diff --check` pass; không ghi đè thay đổi dirty worktree ngoài file đã thống nhất.
- [x] `php artisan test` pass hoặc report baseline failures tách riêng, có lý do và phạm vi.
- [x] Blade/config/routes compile; `php artisan route:list` không còn public password route ngoài redirect tương thích.
- [x] `npm run build` pass; chạy accessibility/UX audit cho login và ComposePost.
- [x] Chạy security scan tập trung auth, webhook, open redirect, OAuth state/CSRF, XSS/unsafe URL và secret exposure.
- [x] Playwright smoke pass desktop/mobile; lưu screenshot/trace ở `output/playwright/`; QA report phân loại đủ `PASS/BUG/UX issue/BLOCKED`.
- [x] Xác nhận dữ liệu tài khoản cũ: google linking đúng, membership/role/posts/history không mất; admin/owner vẫn giữ quyền.
- [x] Chưa có credential Google: đánh dấu real OAuth là `BLOCKED`, không gọi là `PASS`; sau khi có credential mới chạy final staging acceptance.

## Rủi ro và quyết định cần giữ

- **Google credential là blocker tích hợp thật:** có thể hoàn tất code, fake provider và UI trước; không commit secret, chỉ dùng env/secret manager.
- **Registration policy:** lấy `brand()->registration_mode` làm source of truth (`open` cho phép tạo mới; `invite`/`closed` từ chối), không hard-code một policy thứ hai.
- **Multi-community:** `google_id` là định danh global của user; membership vẫn theo brand, tuyệt đối không tự cấp membership ở community khác.
- **Dirty worktree:** không reset/checkout file; mỗi bug QA cần đối chiếu diff hiện tại trước khi kết luận regression.
- **Test data:** marker `[TEST]` phải nằm trong name/title/content/email/slug phù hợp để dễ query và cleanup; không dùng account production.
