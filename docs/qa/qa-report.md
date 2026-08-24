# QA Report

Environment: `local` / `staging`
Dataset: records prefixed `[TEST]` from `QaSampleSeeder`
Evidence: screenshots and traces under `output/playwright/`

Auth mode: local verification uses `AUTH_MODE=password`; staging/VPS acceptance must use `AUTH_MODE=google` with real Google credentials.

## Re-run commands

```powershell
docker compose exec app php artisan db:seed --class=Database\Seeders\QaSampleSeeder --force
$env:PLAYWRIGHT_EXECUTABLE_PATH='C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe'
$env:QA_AUTH_MODE='password' # use 'google' when QA runs against VPS/staging
npm run test:e2e -- --workers=1
```

The seeder is idempotent and refuses non-local/staging/testing environments. The authenticated suites require temporary `QA_SESSION_COOKIE` and, for admin coverage, `QA_ADMIN_SESSION_COOKIE`; create those only in local/staging test infrastructure and remove the corresponding sessions after the run. Never commit cookie values.

Cleanup is explicit and environment-guarded:

```powershell
docker compose exec app php artisan qa:clean-sample       # dry run
docker compose exec app php artisan qa:clean-sample --force
```

The cleanup command deletes only the `[TEST]` dataset and `qa-test` accounts. Do not run `--force` against production.

## Baseline and latest verification

- Baseline scope: the full PHPUnit suite is now green after preserving legacy user stats for accounts without a community membership. Latest full run: `171 passed`, `424 assertions`.
- QA seeder was run twice and remained idempotent: `5` QA users, `3` QA posts, `1` event and `1` marketplace product after both runs.
- The targeted plan suite remains green across OAuth, provisioning, ComposePost, guest-route, QA-seeder and community-isolation coverage.
- Temporary authenticated QA sessions were removed after the browser run.

| Feature | Preconditions | Expected | Actual | Status | Severity | Evidence | Owner |
|---|---|---|---|---|---|---|---|
| Login / Google OAuth | Fake Socialite provider | Redirect/scopes/linking/rejection cases pass | 11 OAuth tests pass; real Google login needs credentials | BLOCKED | P1 | `tests/Feature/GoogleAuthTest.php` | Backend |
| ComposePost preview | Active member | Markdown preview matches published post | 8 Livewire tests pass; safe renderer, Markdown formats, YouTube allowlist and mode switching covered | PASS | P1 | `tests/Feature/ComposePostTest.php` | Frontend |
| Login UI / legacy auth URLs | Guest desktop + mobile | Google-only UI and redirects | Playwright 6/6 pass | PASS | P1 | `output/playwright/desktop-chromium-login.png` | QA |
| Guest protected-route guard | Guest requests | Protected routes redirect to login | 16 route tests / 34 assertions pass, including rejected password POST and DSCons guide isolation | PASS | P1 | `tests/Feature/QaRouteSmokeTest.php` | QA |
| Feed / CỐT / Signal | `[TEST]` member data + authenticated browser | Content and filters render | Seeded post/CỐT/signal assertions pass in feature regression on desktop/mobile | PASS | P1 | `tests/e2e/feature-regression.spec.js` | QA |
| Q&A | `[TEST]` question + answer | Question and answer render | Seeded question assertion passes in feature regression on desktop/mobile | PASS | P1 | `tests/e2e/feature-regression.spec.js` | QA |
| Academy | `[TEST]` published course | Course/module/lesson visible | Seeded course assertion passes in feature regression on desktop/mobile | PASS | P1 | `tests/e2e/feature-regression.spec.js` | QA |
| Event | `[TEST]` published event | View/register state works | Seeded event and `Đã đăng ký` state pass on desktop/mobile | PASS | P1 | `tests/e2e/feature-regression.spec.js` | QA |
| Challenge Revit | `[TEST]` active challenge/member | Task unlock/difficulty/join state works | Seeded approved member challenge assertion passes on desktop/mobile | PASS | P1 | `tests/e2e/feature-regression.spec.js` | QA |
| Marketplace | `[TEST]` product/purchase | Free/owned state works | Seeded resource and `Đã mua` state pass on desktop/mobile | PASS | P1 | `tests/e2e/feature-regression.spec.js` | QA |
| Leaderboard | `[TEST]` stats/snapshots | Ranking renders in order | Seeded current-period XP rows render QA ranking on desktop/mobile | PASS | P1 | `tests/e2e/feature-regression.spec.js` | QA |
| Profile / notification / search | `[TEST]` users/content | Navigation and results work | Profile, search empty/non-empty, and notification dropdown assertions pass on desktop/mobile | PASS | P1 | `tests/e2e/feature-regression.spec.js` | QA |
| Admin critical flow | `[TEST]` admin account | Dashboard and Google-only provisioning UI works | Dashboard, users page, provisioning modal, and no-password guidance pass on desktop/mobile | PASS | P1 | `tests/e2e/feature-regression.spec.js` | QA |

## Baseline exceptions

There are no known failing PHPUnit baseline tests in the current worktree. If a future run reports an AIP/XP regression, record it separately from QA/auth findings with exact reproduction steps, viewport, request/response evidence where useful, and screenshot/trace path.

## Validation evidence

- Full PHPUnit regression: `171 passed` (`424 assertions`).
- Playwright regression: `16 passed` across desktop/mobile Chromium (6 guest, 8 authenticated member, 2 admin); temporary QA passwords were cleared and QA sessions removed after the run.
- ComposePost browser smoke checks toolbar labels, preview/edit mode persistence, keyboard focus and console errors.
- Final PHPUnit run: `171 passed`, `0 failed` (`424 assertions`).
- Vite build, Composer validation, migration status, PHP syntax checks and `git diff --check` pass.
- `npm audit --audit-level=high` reports `0 vulnerabilities` after lockfile updates.
- Playwright guest smoke: `6 passed` across desktop and mobile Chromium, with login screenshots saved in `output/playwright/.
- Static accessibility checker does not parse Blade templates; browser role/label assertions, focus checks and visual smoke were used for login/ComposePost. The AG Kit UX script scanned the stylesheet and returned PASS with generic warnings; Blade-specific behavior was verified through the browser smoke suite and source review.
- AG Kit security scan reports no secrets, dangerous code patterns or dependency risks. Its configuration heuristic does not recognize the project's nested `docker/nginx/default.conf`, so it reports one medium review recommendation; runtime headers were verified directly and include CSP, X-Frame-Options, Referrer-Policy and Permissions-Policy. HSTS is intentionally delegated to the HTTPS reverse proxy because local Docker runs over HTTP.
- Real Google OAuth remains `BLOCKED` until environment credentials are configured. Authenticated feature-browser smoke is `PASS` using a temporary local QA session; it does not replace a real Google staging login.
