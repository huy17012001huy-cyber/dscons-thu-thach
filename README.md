# The All In Plan™

Vietnamese-language community platform for marketers & entrepreneurs. Users learn, post, earn XP, level up, join challenges, and compete on a leaderboard.

**Production:** https://taip.io

## Tech Stack

- **Laravel 12** + **Livewire 3** (full-stack, no separate API)
- **Alpine.js** (client-side reactivity)
- **Tailwind CSS v4** (styling)
- **PostgreSQL 17** (database)
- **PHP 8.2+**

## Quick Start

```bash
# 1. Clone & install
git clone git@github.com:duongtrongnghia/the-all-in-plan.git
cd the-all-in-plan
composer install
npm install

# 2. Environment
cp .env.example .env
php artisan key:generate
# Edit .env — set DB_DATABASE, DB_USERNAME, DB_PASSWORD

# 3. Database
createdb the_all_in_plan
php artisan migrate
php artisan db:seed
php artisan db:seed --class=TopicSeeder

# 4. Run
php artisan serve --port=9090
npm run dev
```

App runs at http://localhost:9090

## Environment Variables

Key variables to set in `.env`:

| Variable | Description | Required |
|---|---|---|
| `DB_DATABASE` | PostgreSQL database name | Yes |
| `DB_USERNAME` / `DB_PASSWORD` | DB credentials | Yes |
| `SEPAY_WEBHOOK_TOKEN` | SePay payment webhook API key | Production |
| `SEPAY_BANK_ACCOUNT` | Bank account number for QR payments | Production |
| `SEPAY_BANK_NAME` | Bank name (default: MBBank) | Production |

## Project Structure

```
app/
├── Console/Commands/        # Scheduled tasks (challenges, streaks, leaderboard)
├── Http/
│   ├── Controllers/         # SepayWebhookController (payments)
│   └── Middleware/           # RequireActiveMembership
├── Livewire/                # All page components (Feed, PostCard, Challenge, Admin...)
├── Models/                  # Eloquent models
├── Notifications/           # GenericNotification
├── Providers/               # Gates (admin)
└── Services/                # XpService (XP awards)

resources/views/
├── layouts/app.blade.php    # Main layout
└── livewire/                # All component views

routes/
├── web.php                  # Route definitions
└── console.php              # Cron schedule
```

## Key Features

| Feature | Route | Component |
|---|---|---|
| Feed (posts) | `/feed` | `Feed.php` |
| CỐT (curated posts) | `/cot` | `CotPage.php` |
| Signals (short posts) | `/tin-hieu` | `SignalsPage.php` |
| Q&A | `/hoi-dap` | `QaPage.php` |
| Challenges | `/challenge` | `ChallengePage.php` / `ChallengeDetail.php` |
| Leaderboard | `/leaderboard` | `LeaderboardPage.php` |
| Profile | `/@{username}` | `ProfilePage.php` |
| Messages | `/messages` | `MessagesPage.php` |
| Marketplace | `/marketplace` | `MarketplacePage.php` |
| Academy | `/khoa-hoc` | `AcademyPage.php` |
| Membership pricing | `/membership/pricing` | `MembershipPricing.php` |

### Admin Routes (requires `is_admin`)

| Route | Component |
|---|---|
| `/admin` | `AdminDashboard.php` |
| `/admin/users` | `AdminUsers.php` |
| `/admin/topics` | `AdminTopics.php` |
| `/admin/courses` | `AdminCourses.php` |
| `/admin/products` | `AdminProducts.php` |
| `/admin/reports` | `AdminReports.php` |
| `/admin/cot-review` | `AdminCotReview.php` |

## Domain Concepts

- **Pillars**: `offer`, `traffic`, `conversion`, `delivery`, `continuity` — every post belongs to one
- **Topics**: Admin-managed post format tags (Case Study, Win, Q&A, etc.)
- **XP System**: Users earn XP for actions (post=15, comment=3, challenge=25, etc.)
- **Levels**: 1–100, derived from XP via `config/exp_table.php`
- **AIP**: Secondary currency (Aura In Progress)
- **Classes**: `offer_architect`, `traffic_mage`, `conversion_ranger`, `delivery_assassin`, `continuity_captain`
- **CỐT**: Curated "essential" posts, nominated by level 30+ users
- **Signals**: Short posts ≤500 words
- **Rune**: 2x XP for first comment on a post within a time window
- **Challenges**: Multi-day group challenges with daily task submissions, admin review

## Scheduled Tasks (Cron)

Defined in `routes/console.php`. Server needs `* * * * * php artisan schedule:run` in crontab.

| Command | Schedule | Purpose |
|---|---|---|
| `aip:snapshot-leaderboard` | Daily 00:00 | Snapshot leaderboard rankings |
| `aip:recalc-pillar-stats` | Hourly | Recalculate pillar statistics |
| `aip:update-challenge` | Every 15min | Update challenge progress |
| `aip:process-challenges` | Daily 02:00 | Process missed days, send warnings |
| `aip:reset-streaks` | Daily 01:00 | Reset inactive user streaks |

## Payment Webhook

SePay bank transfer webhook at `POST /webhook/sepay`. Transfer content format:

- `CHAL{expeditionId}U{userId}` — Challenge entry fee (auto-approves the member)
- `COURSE{courseId}U{userId}` — Course payment
- `PROD{productId}U{userId}` — Product payment
- `MEM{weeks}WU{userId}` — Membership payment

Bank account, bank name and the webhook API key are configured in the admin UI at
`/admin/settings` (stored in the `settings` table, overrides `.env` at runtime).
`SEPAY_*` env vars still work as the fallback. Fails closed when no key is set.

## Deploy

On the server: `git pull` → `composer install --no-dev --optimize-autoloader` → `npx vite build`
→ `php artisan migrate --force` → `php artisan config:cache route:cache` → restart PHP-FPM.

## Testing

```bash
php artisan test
```

## Important Notes

- **No Filament** — all admin UI is custom Livewire components
- **Membership required** — `RequireActiveMembership` middleware on most routes
- **Admin gate** — `Gate::define('admin', fn($u) => $u->is_admin)` in `AppServiceProvider`
- **Timezone** — App uses UTC internally, displays in `Asia/Ho_Chi_Minh`
- **CSS** — Custom component classes in `resources/css/app.css` (`.card`, `.btn`, `.badge-pillar-*`, etc.)
