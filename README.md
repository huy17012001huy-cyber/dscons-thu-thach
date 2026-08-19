# DSCons AI-BIM Lab

Nền tảng cộng đồng tiếng Việt gồm thử thách, Academy, gamification, marketplace và affiliate. Codebase dùng Laravel 12, Livewire 3, Tailwind CSS 4, PHP 8.4 và PostgreSQL 17.

## Chạy local bằng Docker

1. Sao chép `.env.docker.example` thành `.env` và đặt `SEED_ADMIN_PASSWORD` thành mật khẩu riêng.
2. Chạy `docker compose up --build`.
3. Ở terminal khác, chạy `docker compose exec app php artisan key:generate`, sau đó `docker compose exec app php artisan migrate --seed` và `docker compose exec app php artisan storage:link`.
4. Mở `http://localhost:8080`; đăng nhập bằng `SEED_ADMIN_EMAIL` và mật khẩu đã đặt.

Vite chạy tại cổng `5173`; PostgreSQL được map cổng `5432`. Dừng dịch vụ bằng `docker compose down`; chỉ dùng `docker compose down -v` khi muốn xóa hẳn dữ liệu local.

## Cấu hình staging

- Sao chép `.env.staging.example` thành `.env.staging`, điền toàn bộ giá trị duy nhất rồi chạy `docker compose --env-file .env.staging -f docker-compose.staging.yml up --build -d`.
- Dùng PostgreSQL và volume riêng; không dùng dữ liệu, mật khẩu hay `APP_KEY` của môi trường khác.
- Đặt `APP_ENV=staging`, `APP_DEBUG=false`, `APP_URL=https://<staging-domain>`, `BRAND_DOMAIN=<staging-domain>` và sinh `APP_KEY` mới.
- Giữ `MAIL_MAILER=log`; để trống SePay, Telegram, Resend/Brevo và webhook credentials cho đến khi có integration riêng.
- Chạy migration/seed một lần bằng `docker compose --env-file .env.staging -f docker-compose.staging.yml exec app php artisan migrate --seed`; các service queue và scheduler đã chạy liên tục. Đặt reverse proxy/HTTPS trước service `web`.

## Chức năng

- Xác thực, xác minh email, membership, referral, profile, thông báo và nhắn tin.
- Feed, CỐT, Signals, hỏi đáp, comments, likes, bookmarks, upload, tìm kiếm và moderation.
- XP, level, streak, AIP, huy hiệu, leaderboard và community challenges.
- Academy, khóa học, quiz, bài nộp; Challenge nhiều ngày với check-in, minh chứng, duyệt bài và phần thưởng.
- Marketplace, sản phẩm số, affiliate, thanh toán SePay, webhook, Telegram, và admin dashboard.

## Kiểm thử

Sau khi các container chạy và database đã migrate:

```bash
docker compose exec app php artisan test
docker compose exec app php artisan route:list
```

Các test hiện tại bao phủ xác thực, membership, XP/AIP, badges, post security, rate limiting, admin export và bảo vệ webhook.
