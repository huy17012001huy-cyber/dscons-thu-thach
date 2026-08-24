# Google OAuth configuration

The public login flow is Google-only. Never commit the client secret or put it in a screenshot, QA report, browser bundle, or log.

## Switching authentication mode by environment

The application uses `AUTH_MODE`:

- Local Docker: `AUTH_MODE=password` (the default when `APP_ENV=local`).
- VPS/staging/production: `AUTH_MODE=google` (the default outside local, but set it explicitly).

After changing the value on a deployed server, clear both configuration and route caches because the legacy auth routes are registered according to the mode:

```bash
php artisan config:clear
php artisan route:clear
# Nếu VPS dùng config cache, chạy thêm: php artisan config:cache
```

Then restart the PHP/web containers. The Google-only mode removes the public password form and routes legacy password URLs back to `/login`.

## Environment variables

Set these values in the environment for each deployment:

```dotenv
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=https://example.test/auth/google/callback
AUTH_MODE=google
```

`GOOGLE_REDIRECT_URI` must exactly match the authorized redirect URI registered in Google Cloud. The origin must also be registered for the environment, for example:

- Local Docker: `http://localhost:8080`
- Staging: `https://staging.example.com`
- Production: the real HTTPS application origin

## Google Cloud checklist

1. Create or select the OAuth consent screen for the correct project.
2. Configure the consent screen and publish it to the intended audience.
3. Create a Web application OAuth client.
4. Add the exact authorized JavaScript origin and callback URL for the environment.
5. Store the client ID/secret in the environment or secret manager.
6. Clear Laravel config cache after changing values: `php artisan config:clear`.

## Account linking rules

- Google must return a non-empty stable subject ID, email, and `verified_email=true`.
- Existing accounts are found by `google_id` first.
- Email linking is allowed only when the local account already has `email_verified_at`.
- Unverified email is never auto-linked.
- New accounts are created only when the current brand has `registration_mode=open`.
- Membership is created in the current brand only; admin/owner flags and existing content are preserved.
- Banned, expired, and inaccessible memberships are rejected before a session is kept.

## Operational checks

- Test `/auth/google/redirect` and `/auth/google/callback` with a fake provider in automated tests.
- Test cancel/error/missing-email/unverified-email paths before enabling a real credential.
- After credential setup, run one real staging login and confirm the callback URL, session redirect, membership, and login log.
- If OAuth must be rolled back, keep the `google_id` column and existing account links; restore the previous public login route only through an explicit reviewed change.

## Web security headers

The Docker Nginx server emits `Content-Security-Policy`, `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, and `Permissions-Policy`. The local CSP allows the Vite development origin `http://localhost:5173`; remove that development allowance from the production edge policy. HSTS is owned by the HTTPS reverse proxy and should be enabled there, not on the local HTTP listener.
