<?php

use App\Livewire\AdminChallenges;
use App\Livewire\AdminCotReview;
use App\Livewire\AdminDashboard;
use App\Livewire\AdminFeedbacks;
use App\Livewire\AdminLoginLogs;
use App\Livewire\AdminReports;
use App\Livewire\AdminSettings;
use App\Livewire\AdminUsers;
use App\Livewire\SearchResults;
use App\Livewire\AdminCourseBuilder;
use App\Livewire\AdminCourses;
use App\Livewire\AdminTopics;
use App\Http\Controllers\ImpersonationController;
use App\Livewire\Auth\ClassSelection;
use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\LoginForm;
use App\Livewire\Auth\ResetPassword;
use App\Livewire\Auth\VerifyEmailNotice;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Livewire\CotPage;
use App\Livewire\ChallengeDetail;
use App\Livewire\ChallengePage;
use App\Livewire\MembershipPricing;
use App\Livewire\MessagesPage;
use App\Livewire\Feed;
use App\Livewire\AcademyDetail;
use App\Livewire\AcademyPage;
use App\Livewire\AffiliatePage;
use App\Livewire\LeaderboardPage;
use App\Livewire\ProfilePage;
use App\Livewire\QaPage;
use App\Livewire\SignalsPage;
use App\Livewire\EventsPage;
use App\Livewire\AdminEvents;
use App\Livewire\AdminCommunities;
use App\Livewire\CommunitiesPage;
use App\Livewire\CommunityPreview;
use App\Livewire\CreateCommunity;
use App\Livewire\CommunityManage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// ─── Bot API (GoClaw integration) ───────────────────────────────────
Route::prefix('api/bot')->group(function () {
    Route::get('/member', [\App\Http\Controllers\BotApiController::class, 'lookupMember']);
    Route::get('/challenge-progress', [\App\Http\Controllers\BotApiController::class, 'challengeProgress']);
    Route::get('/pending-submissions', [\App\Http\Controllers\BotApiController::class, 'pendingSubmissions']);
});

// Creator application. The account must be verified and belong to an active
// community before submitting a new community for review.
Route::middleware([
    'auth',
    \App\Http\Middleware\EnsureEmailVerified::class,
    \App\Http\Middleware\RequireActiveMembership::class,
])->group(function () {
    Route::get('/tao-cong-dong', CreateCommunity::class)->name('community.create');
});

// Public discovery and preview pages. `/cong-dong` remains as a legacy URL.
Route::get('/discovery', CommunitiesPage::class)->name('communities');
Route::redirect('/cong-dong', '/discovery', 301);
Route::get('/cong-dong/{community:slug}', CommunityPreview::class)->name('community.preview');

// Contextual community routes. The legacy routes above remain the default
// DSCons context so existing bookmarks and integrations continue to work.
Route::prefix('c/{community:slug}')->name('community.')->group(function () {
    Route::middleware([
        'auth',
        \App\Http\Middleware\EnsureEmailVerified::class,
        \App\Http\Middleware\RequireActiveMembership::class,
        \App\Http\Middleware\HydrateCommunityStats::class,
    ])->group(function () {
        Route::get('/feed', Feed::class)->name('feed');
        Route::get('/cot', CotPage::class)->name('cot');
        Route::get('/tin-hieu', SignalsPage::class)->name('signals');
        Route::get('/hoi-dap', QaPage::class)->name('qa');
        Route::get('/challenge', ChallengePage::class)->name('challenge');
        Route::get('/challenge/{slug}', ChallengeDetail::class)->name('challenge.show');
        Route::get('/leaderboard', LeaderboardPage::class)->name('leaderboard');
        Route::get('/khoa-hoc', AcademyPage::class)->name('academy');
        Route::get('/khoa-hoc/{id}', AcademyDetail::class)->name('academy.show');
        Route::get('/su-kien', EventsPage::class)->name('events');
        Route::get('/membership', MembershipPricing::class)->name('membership');
        Route::get('/marketplace', \App\Livewire\MarketplacePage::class)->name('marketplace');
        Route::get('/affiliate', AffiliatePage::class)->name('affiliate');
        Route::get('/messages/{conversation?}', MessagesPage::class)->name('messages');
        Route::get('/search', SearchResults::class)->name('search');
        Route::get('/manage', CommunityManage::class)->name('manage');
    });
});

Route::middleware(['auth', \App\Http\Middleware\EnsureEmailVerified::class])->group(function () {
    Route::get('/admin/communities', AdminCommunities::class)
        ->name('admin.communities')
        ->can('admin');
});

// ─── SePay webhook (no auth, verified by API key) ──────────────────
Route::post('/webhook/sepay', \App\Http\Controllers\SepayWebhookController::class)
    ->name('webhook.sepay');

// ─── Register webhook (server-to-server, verified by Bearer token) ─
Route::post('/webhook/register', \App\Http\Controllers\RegisterWebhookController::class)
    ->name('webhook.register');


// ─── Public feature recap page ─────────────────────────────────────
Route::view('/tinh-nang', 'pages.tinh-nang')->name('tinh-nang');

// ─── Public slides (no auth required) ─────────────────────────────
Route::get("/sale-like-crazy", fn() => response()->file(
    storage_path("app/private/guides/sale-like-crazy.html"),
    ["X-Robots-Tag" => "noindex, nofollow"]
))->name("sale-like-crazy");

Route::get("/iman-funnel-map-viet.html", fn() => response()->file(
    storage_path("app/private/guides/iman-funnel-map-viet.html"),
    ["X-Robots-Tag" => "noindex, nofollow"]
));

// ─── Member-only guide pages (auth + active membership required) ───
// Whitelist filenames để chống path traversal. Files ở storage/app/private/guides/.
Route::middleware(['auth', \App\Http\Middleware\RequireActiveMembership::class])->group(function () {
    // Blog index — list all guides, noindex
    Route::get('/blog', function () {
        return response()
            ->view('pages.blog')
            ->header('X-Robots-Tag', 'noindex, nofollow, noarchive, nosnippet');
    })->name('blog');

    // Gated static pages (moved out of public/ - require active membership)
    Route::get('/{guide}.html', function (string $guide) {
        $whitelist = [
            'iman-funnel-map-viet',
            'bao-cao-huong-dan-quan-tri-menu',
        ];
        if (!in_array($guide, $whitelist, true)) abort(404);
        $path = storage_path('app/private/guides/'.$guide.'.html');
        if (!file_exists($path)) abort(404);
        return response()->file($path, ['X-Robots-Tag' => 'noindex, nofollow']);
    })->where('guide', '[a-z0-9-]+')
      ->name('member-guide');
});

// ─── Redirect root → feed or login ──────────────────────────────────
Route::get('/', fn() => redirect()->route(Auth::check() ? 'feed' : 'login'));

// ─── Guest routes ────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',    LoginForm::class)->name('login');
    // Đăng ký công khai đã đóng — tài khoản tạo qua webhook/Admin. Giữ tên route để khỏi vỡ link cũ.
    Route::get('/register', fn() => redirect()->route('login'))->name('register');

    // Quên / đặt lại mật khẩu
    Route::get('/quen-mat-khau',            ForgotPassword::class)->name('password.request');
    Route::get('/dat-lai-mat-khau/{token}', ResetPassword::class)->name('password.reset');
});

// Referral link (store in session)
Route::get('/ref/{username}', function (string $username) {
    session(['referral' => $username]);
    return redirect()->route('register');
})->name('referral');

// ─── Authenticated routes ────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    // Onboarding (class selection)
    Route::get('/onboarding', ClassSelection::class)->name('onboarding');

    // ─── Email verification ─────────────────────────────────────────
    // Trang chờ xác minh (user đã đăng nhập nhưng chưa verify email)
    Route::get('/email/verify', VerifyEmailNotice::class)->name('verification.notice');
    // Link trong email — chữ ký + đăng nhập, đánh dấu đã verify rồi vào feed
    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect()->route('feed');
    })->middleware(['signed', 'throttle:6,1'])->name('verification.verify');

    // Logout
    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('login');
    })->name('logout');

    // Membership pages (no active membership required)
    Route::get('/membership/expired', function () {
        return view('pages.membership-expired');
    })->name('membership.expired');
    Route::get('/membership/pricing', MembershipPricing::class)->name('membership.pricing');

    // ─── Impersonation (outside RequireActiveMembership so stop works
    //     even when target user has no active membership) ──────────────
    Route::post('/admin/impersonate/{user}', [ImpersonationController::class, 'start'])
        ->name('admin.impersonate.start')
        ->whereNumber('user')
        ->middleware('throttle:20,1')
        ->can('admin');
    Route::post('/admin/impersonate/stop', [ImpersonationController::class, 'stop'])
        ->name('admin.impersonate.stop')
        ->middleware('throttle:20,1');

    // ─── Main platform (require verified email + active membership) ──
    Route::middleware([
        \App\Http\Middleware\EnsureEmailVerified::class,
        'App\Http\Middleware\RequireActiveMembership',
        \App\Http\Middleware\HydrateCommunityStats::class,
    ])->group(function () {
        Route::get('/feed',              Feed::class)->name('feed');
        Route::get('/cot',               CotPage::class)->name('cot');
        Route::get('/tin-hieu',          SignalsPage::class)->name('signals');
        Route::get('/hoi-dap',           QaPage::class)->name('qa');
        Route::get('/challenge',           ChallengePage::class)->name('challenge');
        Route::get('/challenge/{slug}',  ChallengeDetail::class)->name('challenge.show');
        Route::get('/challenge/{slug}/task/{day}/reward', [\App\Http\Controllers\ChallengeRewardController::class, 'download'])
            ->name('challenge.reward.download')
            ->whereNumber('day');
        Route::get('/leaderboard',       LeaderboardPage::class)->name('leaderboard');
        Route::get('/khoa-hoc',          AcademyPage::class)->name('academy');
        Route::get('/khoa-hoc/{id}',     AcademyDetail::class)->name('academy.show');
        Route::get('/su-kien',            EventsPage::class)->name('events');
        Route::get('/marketplace',       \App\Livewire\MarketplacePage::class)->name('marketplace');
        Route::get('/affiliate',         AffiliatePage::class)->name('affiliate');
        Route::get('/messages/{conversation?}', MessagesPage::class)->name('messages');
        Route::get('/search',            SearchResults::class)->name('search');

        // ─── Admin routes ────────────────────────────────────────────
        Route::prefix('admin')->name('admin.')->group(function () {
            Route::get('/', AdminDashboard::class)->name('dashboard')
                ->can('admin');
            Route::get('/topics', AdminTopics::class)->name('topics')
                ->can('admin');
            Route::get('/courses', AdminCourses::class)->name('courses')
                ->can('admin');
            Route::get('/products', \App\Livewire\AdminProducts::class)->name('products')
                ->can('admin');
            Route::get('/courses/{id}/build', AdminCourseBuilder::class)->name('courses.build')
                ->can('admin');
            Route::get('/challenges', AdminChallenges::class)->name('challenges')
                ->can('admin');
            Route::get('/events', AdminEvents::class)->name('events')
                ->can('admin');
            Route::get('/cot-review', AdminCotReview::class)->name('cot')
                ->can('admin');
            Route::get('/reports', AdminReports::class)->name('reports')
                ->can('admin');
            Route::get('/feedbacks', AdminFeedbacks::class)->name('feedbacks')
                ->can('admin');
            Route::get('/users', AdminUsers::class)->name('users')
                ->can('admin');
            Route::get('/settings', AdminSettings::class)->name('settings')
                ->can('admin');
            Route::get('/login-logs', AdminLoginLogs::class)->name('login-logs')
                ->can('admin');
        });
        Route::get('/@{username}',       ProfilePage::class)->name('profile');
        Route::get('/u/{id}',            function ($id) {
            $user = \App\Models\User::findOrFail($id);
            return redirect()->route('profile', $user->username ?? $user->id);
        })->name('profile.id');
    });
});
