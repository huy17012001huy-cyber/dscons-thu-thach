# Website Thử Thách — Project Context

## Stack
- **Laravel 12** + **Livewire 3** (full-stack, no separate API)
- **Alpine.js** (client-side reactivity, dropdowns, toggling)
- **Tailwind CSS v4** (utility classes + custom component classes in `app.css`)
- **PostgreSQL 17** (database: `the_all_in_plan`)
- **PHP 8.2+**

## Project Purpose
Vietnamese-language community platform for marketers/entrepreneurs. Users learn, post, earn XP, level up, join expeditions, and compete on a leaderboard.

## Key Domain Concepts
- **Pillars**: `offer`, `traffic`, `conversion`, `delivery`, `continuity` — every post belongs to one
- **Topics**: Admin-managed post format tags (Case Study, Win, Hỏi đáp, etc.) stored in `topics` table
- **Signals** (`is_signal`): Short posts ≤500 words, shown in a separate feed tab
- **CỐT** (`is_cot`): Curated "essential" posts, nominated by GD3+ users (level ≥ 30)
- **XP System**: Users earn XP for actions (post=15, comment=3, expedition=25, etc.) with streak multipliers
- **AIP**: Secondary currency (Aura In Progress)
- **Đá Không Cực (💎)**: Rare gem reward, tracked in `da_khong_cuc` table
- **Classes**: `offer_architect`, `traffic_mage`, `conversion_ranger`, `delivery_assassin`, `continuity_captain` — user archetypes with emoji/color badges
- **Level**: Derived from XP via `config/exp_table.php`; levels 1–100
- **Membership**: Required to access the platform; checked by `RequireActiveMembership` middleware
- **Expedition**: Group challenges with captains, check-ins, and XP rewards
- **Rune**: A mechanic that gives 2x XP to the first comment on a post within a time window

## Directory Structure
```
app/
  Http/Middleware/     RequireActiveMembership.php
  Livewire/
    Auth/              LoginForm, RegisterForm, ClassSelection
    AdminTopics.php    — Admin CRUD for topics (no Filament)
    ComposePost.php    — Post composer
    Feed.php           — Main feed with tabs + pillar filters
    PostCard.php       — Individual post with likes/comments/bookmarks
    ProfilePage.php
    LeaderboardPage.php
    ExpeditionPage.php, ExpeditionDetail.php
    SignalsPage.php, QaPage.php, CotPage.php
    Sidebar*.php       — Sidebar widgets
    NotificationBell.php
  Models/
    User, Post, Comment, Like, Bookmark
    Topic, Membership, XpTransaction, AipTransaction
    Expedition, ExpeditionMember, ExpeditionCheckin
    DaKhongCuc, DaKhongCucLog, PowerSymbol
    Question, Answer, Badge, UserBadge
    Setting, LeaderboardSnapshot, PillarStat
    CommunityChallenge, Course, Module, Lesson, etc.
  Services/
    XpService.php      — award(user, type, multiplier, description, reference)
  Providers/
    AppServiceProvider.php  — Gate::define('admin', fn($u) => $u->is_admin)

resources/views/
  layouts/app.blade.php       — Main layout
  livewire/
    compose-post.blade.php    — Post composer UI
    post-card.blade.php       — Post display card
    admin-topics.blade.php    — Admin topic management
    feed.blade.php
    ...

routes/web.php
  — Guest: /login, /register
  — Auth: /onboarding, /logout, /membership/expired
  — Auth + ActiveMembership: /feed, /cot, /tin-hieu, /hoi-dap, /expedition, /leaderboard, /@{username}
  — Admin (->can('admin')): /admin/topics
```

## Models — Key Fields

### User
- `name`, `email`, `username`, `avatar`
- `class` (offer_architect/traffic_mage/conversion_ranger/delivery_assassin/continuity_captain), `level`, `xp`, `aip`, `streak`
- `is_admin` (bool), `is_moderator` (bool)
- `referred_by`, `last_active_at`, `class_changed_at`
- Accessors: `avatar_url`, `job_stage`, `class_label`, `class_color`, `class_emoji`, `da_count`

### Post
- `user_id`, `title` (nullable), `content`, `pillar`
- `topic_id` (nullable FK → topics)
- `is_signal` (bool), `is_cot` (bool), `cot_by` (nullable FK → users)
- `rune_expires_at` (nullable datetime)
- Scopes: `cot()`, `signal()`, `byPillar()`

### Topic
- `name`, `emoji`, `slug` (unique), `sort_order`, `is_active`
- Scope: `active()` → where is_active=true, orderBy sort_order

### Membership
- `user_id`, `plan`, `status` (active/expired/cancelled), `expires_at`

## XpService
```php
app(XpService::class)->award($user, 'post', 1.0, 'Description', $model);
```
Types: `login`, `post`, `cot`, `answer`, `best_answer`, `comment`, `expedition_checkin`, `expedition_complete`, `expedition_captain`, `challenge`, `affiliate`

## CSS Conventions
Custom component classes defined in `resources/css/app.css`:
- `.card` — white rounded card with shadow
- `.btn`, `.btn-primary`, `.btn-ghost`
- `.badge`, `.badge-pillar-{offer|traffic|conversion|delivery|continuity}`
- `.badge-class-{offer_architect|traffic_mage|conversion_ranger|delivery_assassin|continuity_captain}`
- `.level-badge`, `.cot-badge`, `.da-gem`
- `.avatar` — circular image
- `.input` — form input style
- `.post-card`, `.post-card.is-cot`, `.post-card.is-signal`, `.post-card.has-rune`

## Important Patterns

### Livewire Component
```php
// Always use #[Rule] attributes for validation
#[Rule('required|min:5|max:10000')]
public string $content = '';

// Dispatch events for cross-component communication
$this->dispatch('post-created');

// Listen with #[On] in other components
#[On('post-created')]
public function refreshFeed() { ... }
```

### Alpine Dropdown Pattern
```html
<div x-data="{ open: false }" style="position:relative;">
    <button @click="open = !open">Label ▾</button>
    <div x-show="open" @click.away="open = false" x-transition
         style="position:absolute; right:0; top:calc(100% + 6px); z-index:50; ...">
        ...items...
    </div>
</div>
```

### Auto-expanding Textarea
```html
<textarea x-data
    x-init="$el.style.height = $el.scrollHeight + 'px'"
    @input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
    style="overflow:hidden; resize:none;">
</textarea>
```

### Admin Gate
```php
// Check in blade
@can('admin') ... @endcan
// Check in routes
Route::get('/admin/...', Component::class)->can('admin');
```

## Do NOT
- Do NOT install or use Filament for admin UI — use custom Livewire components instead
- Do NOT use `Route::middleware(closure)` — use named middleware strings or `->can()`
- Do NOT add scrollbars to textareas — use auto-expand pattern above
- Do NOT break single-row toolbar in compose-post — all icons + dropdowns + actions on one line

## Dev Server
- Laravel: `php artisan serve --port=9090`
- Assets: `npm run dev` (Vite)
- Database: `createdb the_all_in_plan` then `php artisan migrate` then `php artisan db:seed` then `php artisan db:seed --class=TopicSeeder`
