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
use App\Livewire\AdminOrders;
use App\Livewire\AdminTopics;
use App\Http\Controllers\ImpersonationController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\AdminSecurityController;
use App\Http\Controllers\AdminTwoFactorChallengeController;
use App\Livewire\Auth\ClassSelection;
use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\LoginForm;
use App\Livewire\Auth\RegisterForm;
use App\Livewire\Auth\ResetPassword;
use App\Livewire\Auth\VerifyEmailNotice;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Livewire\CotPage;
use App\Livewire\ChallengeDetail;
use App\Livewire\ChallengeLessonPage;
use App\Livewire\ChallengePage;
use App\Livewire\MembershipPricing;
use App\Livewire\MessagesPage;
use App\Livewire\Feed;
use App\Livewire\AcademyDetail;
use App\Livewire\AcademyPage;
use App\Livewire\AffiliatePage;
use App\Livewire\LeaderboardPage;
use App\Livewire\ProfilePage;
use App\Livewire\PostPage;
use App\Livewire\RecruiterDashboard;
use App\Livewire\RecruiterOnboarding;
use App\Livewire\RecruiterPlansPage;
use App\Livewire\RecruiterMessagesPage;
use App\Livewire\EngineerCvPage;
use App\Livewire\EngineerRecruitmentRequestsPage;
use App\Livewire\AccountSettings;
use App\Livewire\ProfileEditPage;
use App\Livewire\QaPage;
use App\Livewire\SignalsPage;
use App\Livewire\EventsPage;
use App\Livewire\OrdersPage;
use App\Livewire\AdminEvents;
use App\Livewire\AdminCommunities;
use App\Livewire\AdminRecruitment;
use App\Livewire\CommunitiesPage;
use App\Livewire\CommunityPreview;
use App\Livewire\CreateCommunity;
use App\Livewire\CommunityManage;
use App\Livewire\CommunityFeedbacks;
use App\Livewire\CreateCommunityFeedback;
use App\Livewire\CommunityGuidePage;
use App\Livewire\CommunityRulesPage;
use App\Livewire\RevitDevicePage;
use App\Livewire\AdminToolLicenses;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// ─── Bot API (GoClaw integration) ───────────────────────────────────
Route::prefix('api/bot')->group(function () {
    Route::get('/member', [\App\Http\Controllers\BotApiController::class, 'lookupMember']);
    Route::get('/challenge-progress', [\App\Http\Controllers\BotApiController::class, 'challengeProgress']);
    Route::get('/pending-submissions', [\App\Http\Controllers\BotApiController::class, 'pendingSubmissions']);
});

// Revit add-ins use their own bearer token, never a browser session or Google password.
Route::prefix('api/revit')->group(function () {
    Route::post('/device/start', [\App\Http\Controllers\RevitToolApiController::class, 'start'])->middleware('throttle:revit-device-start');
    Route::post('/device/poll', [\App\Http\Controllers\RevitToolApiController::class, 'poll'])->middleware('throttle:revit-device-poll');
    Route::get('/entitlements', [\App\Http\Controllers\RevitToolApiController::class, 'entitlements'])->middleware('throttle:revit-heartbeat');
    Route::get('/tools/{toolKey}/manifest', [\App\Http\Controllers\RevitToolApiController::class, 'manifest'])->where('toolKey', '[a-z0-9-]+');
    Route::post('/heartbeat', [\App\Http\Controllers\RevitToolApiController::class, 'heartbeat'])->middleware('throttle:revit-heartbeat');
    Route::post('/logout', [\App\Http\Controllers\RevitToolApiController::class, 'logout'])->middleware('throttle:revit-heartbeat');
});

Route::middleware(['auth', \App\Http\Middleware\EnsureEmailVerified::class])->group(function () {
    Route::get('/revit/kich-hoat/{code}', [\App\Http\Controllers\RevitDeviceAuthorizationController::class, 'show'])->name('revit.authorization.show')->where('code', '[A-Za-z0-9]+');
    Route::post('/revit/kich-hoat/{code}', [\App\Http\Controllers\RevitDeviceAuthorizationController::class, 'approve'])->name('revit.authorization.approve')->where('code', '[A-Za-z0-9]+');
    Route::get('/tai-khoan/thiet-bi-revit', RevitDevicePage::class)->name('account.revit-device');
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

// Public discovery and preview pages. Community URLs use the same `/c/{slug}`
// namespace as the contextual feed and learning routes.
Route::get('/discovery', CommunitiesPage::class)->name('communities');
Route::redirect('/cong-dong', '/discovery', 301);
Route::get('/cong-dong/{slug}', fn (string $slug) => redirect()->to('/c/' . rawurlencode($slug), 301))
    ->where('slug', '[A-Za-z0-9-]+');
Route::get('/c/{community:slug}', CommunityPreview::class)->name('community.preview');
Route::view('/tuyen-dung', 'pages.recruitment-landing')->name('recruiter.landing');

Route::middleware(['auth', \App\Http\Middleware\EnsureEmailVerified::class])->group(function () {
    Route::get('/tai-khoan/cai-dat', AccountSettings::class)->name('account.settings');
    Route::get('/ho-so/chinh-sua', ProfileEditPage::class)->name('profile.edit');
    Route::get('/nha-tuyen-dung/onboarding', RecruiterOnboarding::class)->name('recruiter.onboarding');
    Route::middleware([\App\Http\Middleware\EngineerAccountOnly::class])->group(function () {
        Route::get('/ho-so-cv', fn () => redirect()->to('/c/dscons/ho-so-cv'))->name('engineer.cv');
    });
    Route::middleware([\App\Http\Middleware\RecruiterOnly::class, \App\Http\Middleware\VerifiedRecruiter::class])->prefix('nha-tuyen-dung')->name('recruiter.')->group(function () {
        Route::get('/dashboard', RecruiterDashboard::class)->name('dashboard');
        Route::get('/goi-dich-vu', RecruiterPlansPage::class)->name('plans');
        Route::get('/tin-nhan/{conversation?}', RecruiterMessagesPage::class)->name('messages');
    });
});

// Contextual community routes. The legacy routes above remain the default
// DSCons context so existing bookmarks and integrations continue to work.
Route::prefix('c/{community:slug}')->name('community.')->group(function () {
    Route::middleware([
        'auth',
        \App\Http\Middleware\EnsureEmailVerified::class,
    ])->group(function () {
        Route::get('/goi-don-hang', OrdersPage::class)->name('orders');
    });

    // Learning products remain discoverable and purchasable without Premium.
    // Community routes below require community participation only; Premium is
    // checked separately by the challenge/course access logic.
    Route::middleware([
        'auth',
        \App\Http\Middleware\EnsureEmailVerified::class,
        \App\Http\Middleware\EngineerOnly::class,
    ])->group(function () {
        Route::get('/challenge', ChallengePage::class)->name('challenge');
        Route::get('/challenge/{slug}/ngay/{day}', ChallengeLessonPage::class)->name('challenge.lesson')->whereNumber('day');
        Route::get('/challenge/{slug}', ChallengeDetail::class)->name('challenge.show');
        Route::get('/khoa-hoc', AcademyPage::class)->name('academy');
        Route::get('/khoa-hoc/{id}', AcademyDetail::class)->name('academy.show');
        Route::get('/membership', MembershipPricing::class)->name('membership');
        Route::get('/marketplace', \App\Livewire\MarketplacePage::class)->name('marketplace');
    });

    Route::middleware([
        'auth',
        \App\Http\Middleware\EnsureEmailVerified::class,
        \App\Http\Middleware\EngineerOnly::class,
        \App\Http\Middleware\RequireActiveMembership::class,
        \App\Http\Middleware\HydrateCommunityStats::class,
    ])->group(function () {
        Route::get('/feed', Feed::class)->name('feed');
        Route::get('/bai-viet/{slug}', PostPage::class)->name('post.show')->where('slug', '[A-Za-z0-9-]+');
        Route::get('/cot', CotPage::class)->name('cot');
        Route::get('/tin-hieu', SignalsPage::class)->name('signals');
        Route::get('/hoi-dap', QaPage::class)->name('qa');
        Route::get('/leaderboard', LeaderboardPage::class)->name('leaderboard');
        Route::get('/su-kien', EventsPage::class)->name('events');
        Route::get('/affiliate', AffiliatePage::class)->name('affiliate');
        Route::get('/messages/{conversation?}', MessagesPage::class)->name('messages');
        Route::get('/search', SearchResults::class)->name('search');
        Route::get('/gop-y-khieu-nai', CommunityFeedbacks::class)->name('feedbacks');
        Route::get('/gop-y-khieu-nai/tao', CreateCommunityFeedback::class)->name('feedbacks.create');
        Route::get('/huong-dan-su-dung', CommunityGuidePage::class)->name('guide');
        Route::get('/noi-quy', CommunityRulesPage::class)->name('rules');
    });

    // Community administration is independent from account type and
    // membership. A community admin can manage only the active community.
    Route::middleware([
        'auth',
        \App\Http\Middleware\EnsureEmailVerified::class,
        \App\Http\Middleware\CommunityAdminOnly::class,
    ])->group(function () {
        Route::get('/manage', CommunityManage::class)->name('manage');
        Route::get('/manage/courses', AdminCourses::class)->name('manage.courses');
        Route::get('/manage/courses/{id}/build', AdminCourseBuilder::class)->name('manage.courses.build')->whereNumber('id');
        Route::get('/manage/challenges', \App\Livewire\AdminChallenges::class)->name('manage.challenges');
        Route::get('/manage/events', AdminEvents::class)->name('manage.events');
        Route::get('/manage/settings', \App\Livewire\CommunitySettings::class)->name('manage.settings');
        Route::get('/manage/recruitment', AdminRecruitment::class)->name('manage.recruitment');
        Route::get('/manage/recruitment/xem-truoc/cv/{cv}', EngineerCvPage::class)->name('manage.recruitment.preview.cv');
        Route::get('/manage/recruitment/xem-truoc/recruiter/{recruiter}', RecruiterDashboard::class)->name('manage.recruitment.preview.recruiter');
        Route::get('/manage/feedbacks', AdminFeedbacks::class)->name('manage.feedbacks');
        Route::get('/manage/members', \App\Livewire\CommunityMembers::class)->name('manage.members');
    });

    Route::get('/manage/moderation', \App\Livewire\AdminReports::class)
        ->name('manage.moderation')
        ->middleware([
            'auth',
            \App\Http\Middleware\EnsureEmailVerified::class,
            \App\Http\Middleware\CommunityModeratorOnly::class,
        ]);

    // CV and Talent are independent community features. A community can keep
    // CV editing available while turning recruiter search off.
    Route::get('/tuyen-dung', fn () => view('pages.recruitment-landing'))
        ->name('recruiter.landing')
        ->middleware(\App\Http\Middleware\RecruitmentEnabled::class);

    Route::middleware(['auth', \App\Http\Middleware\EnsureEmailVerified::class])->group(function () {
        Route::get('/ho-so-cv', EngineerCvPage::class)
            ->name('engineer.cv')
            ->middleware([\App\Http\Middleware\EngineerAccountOnly::class, \App\Http\Middleware\CvEnabled::class]);
        Route::get('/yeu-cau-tuyen-dung', EngineerRecruitmentRequestsPage::class)
            ->name('engineer.recruitment-requests')
            ->middleware([\App\Http\Middleware\EngineerAccountOnly::class, \App\Http\Middleware\CvEnabled::class]);

        Route::get('/nha-tuyen-dung/onboarding', RecruiterOnboarding::class)
            ->name('recruiter.onboarding')
            ->middleware(\App\Http\Middleware\RecruitmentEnabled::class);

        Route::middleware([
            \App\Http\Middleware\RecruiterOnly::class,
            \App\Http\Middleware\VerifiedRecruiter::class,
            \App\Http\Middleware\RecruitmentEnabled::class,
        ])->prefix('nha-tuyen-dung')->name('recruiter.')->group(function () {
            Route::get('/dashboard', RecruiterDashboard::class)->name('dashboard');
            Route::get('/ung-vien', RecruiterDashboard::class)->name('candidates');
            Route::get('/goi-dich-vu', RecruiterPlansPage::class)->name('plans');
            Route::get('/tin-nhan/{conversation?}', RecruiterMessagesPage::class)->name('messages')->whereNumber('conversation');
        });
    });

});

Route::middleware(['auth', \App\Http\Middleware\EnsureEmailVerified::class])->group(function () {
    Route::get('/admin/communities', AdminCommunities::class)
        ->name('admin.communities')
        ->can('super-admin');
});

// ─── SePay webhook (no auth, verified by API key) ──────────────────
Route::post('/webhook/sepay', \App\Http\Controllers\SepayWebhookController::class)
    ->middleware('throttle:sepay-webhook')
    ->name('webhook.sepay');

// ─── Register webhook (server-to-server, verified by Bearer token) ─
Route::post('/webhook/register', \App\Http\Controllers\RegisterWebhookController::class)
    ->name('webhook.register');


// ─── Public feature recap page ─────────────────────────────────────
Route::view('/tinh-nang', 'pages.tinh-nang')->name('tinh-nang');

// ─── Public slides (no auth required) ─────────────────────────────
Route::get("/sale-like-crazy", fn() => response()->file(
    app()->bound('brand') && brand()->slug === 'dscons'
        ? abort(404)
        : storage_path("app/private/guides/sale-like-crazy.html"),
    ["X-Robots-Tag" => "noindex, nofollow"]
))->name("sale-like-crazy");

Route::get("/iman-funnel-map-viet.html", fn() => response()->file(
    app()->bound('brand') && brand()->slug === 'dscons'
        ? abort(404)
        : storage_path("app/private/guides/iman-funnel-map-viet.html"),
    ["X-Robots-Tag" => "noindex, nofollow"]
));

// ─── Member-only guide pages (auth + active membership required) ───
// Whitelist filenames để chống path traversal. Files ở storage/app/private/guides/.
Route::middleware(['auth', \App\Http\Middleware\RequireActiveMembership::class])->group(function () {
    // Blog index — list all guides, noindex
    Route::get('/blog', function () {
        if (app()->bound('brand') && brand()->slug === 'dscons') {
            abort(404);
        }

        return response()
            ->view('pages.blog')
            ->header('X-Robots-Tag', 'noindex, nofollow, noarchive, nosnippet');
    })->name('blog');

    // Gated static pages (moved out of public/ - require active membership)
    Route::get('/{guide}.html', function (string $guide) {
        if (app()->bound('brand') && brand()->slug === 'dscons') {
            abort(404);
        }

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
    Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])->middleware('throttle:google-auth')->name('auth.google.redirect');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->middleware('throttle:google-auth')->name('auth.google.callback');
    Route::get('/admin/2fa', [AdminTwoFactorChallengeController::class, 'show'])->name('admin.two-factor.challenge');
    Route::post('/admin/2fa', [AdminTwoFactorChallengeController::class, 'verify'])->middleware('throttle:admin-sensitive')->name('admin.two-factor.verify.submit');

    if (config('auth.mode') === 'password') {
        Route::get('/register', RegisterForm::class)->name('register');
        Route::get('/quen-mat-khau', ForgotPassword::class)->name('password.request');
        Route::get('/dat-lai-mat-khau/{token}', ResetPassword::class)->name('password.reset');
    } else {
        // Keep legacy URLs safe and useful while public auth is Google-only.
        Route::get('/register', fn() => redirect()->route('login')->with('status', 'Tài khoản mới đăng nhập bằng Google.'))->name('register');
        Route::get('/quen-mat-khau', fn() => redirect()->route('login')->with('status', 'Hệ thống chỉ hỗ trợ đăng nhập bằng Google.'))->name('password.request');
        Route::get('/dat-lai-mat-khau/{token}', fn() => redirect()->route('login')->with('status', 'Hệ thống chỉ hỗ trợ đăng nhập bằng Google.'))->name('password.reset');
    }
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
    Route::get('/goi-don-hang', OrdersPage::class)
        ->name('orders')
        ->middleware(\App\Http\Middleware\EnsureEmailVerified::class);

    // ─── Impersonation (outside RequireActiveMembership so stop works
    //     even when target user has no active membership) ──────────────
    Route::post('/admin/impersonate/{user}', [ImpersonationController::class, 'start'])
        ->name('admin.impersonate.start')
        ->whereNumber('user')
        ->middleware(['throttle:admin-sensitive', \App\Http\Middleware\EnsureRecentAdminTwoFactor::class])
        ->can('super-admin');
    Route::post('/admin/impersonate/stop', [ImpersonationController::class, 'stop'])
        ->name('admin.impersonate.stop')
        ->middleware('throttle:admin-sensitive');

    Route::middleware([\App\Http\Middleware\EnsureEmailVerified::class])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/security', [AdminSecurityController::class, 'show'])->name('security')->can('super-admin');
        Route::post('/security/prepare', [AdminSecurityController::class, 'prepare'])->name('security.prepare')->can('super-admin');
        Route::post('/security/confirm', [AdminSecurityController::class, 'confirm'])->middleware('throttle:admin-sensitive')->name('security.confirm')->can('super-admin');
        Route::post('/security/sessions/revoke', [AdminSecurityController::class, 'revokeOtherSessions'])->middleware('throttle:admin-sensitive')->name('security.sessions.revoke')->can('super-admin');
        Route::get('/2fa/xac-nhan', function () { return view('pages.admin-two-factor-challenge'); })->name('two-factor.verify')->can('super-admin');
        Route::post('/2fa/xac-nhan', [AdminTwoFactorChallengeController::class, 'verifyCurrent'])->middleware('throttle:admin-sensitive')->name('two-factor.verify.current')->can('super-admin');
    });

    // ─── Main platform (require verified email + active membership) ──
    Route::middleware([
        \App\Http\Middleware\EnsureEmailVerified::class,
        \App\Http\Middleware\EngineerOnly::class,
        \App\Http\Middleware\RequireActiveMembership::class,
        \App\Http\Middleware\HydrateCommunityStats::class,
    ])->group(function () {
        Route::get('/feed',              Feed::class)->name('feed');
        Route::get('/bai-viet/{slug}',   PostPage::class)->name('post.show')->where('slug', '[A-Za-z0-9-]+');
        Route::get('/cot',               CotPage::class)->name('cot');
        Route::get('/tin-hieu', SignalsPage::class)->name('signals');
        Route::get('/hoi-dap',           QaPage::class)->name('qa');
        Route::get('/challenge',           ChallengePage::class)->name('challenge');
        Route::get('/challenge/{slug}/ngay/{day}', ChallengeLessonPage::class)->name('challenge.lesson')->whereNumber('day');
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
        ->can('super-admin');
            Route::get('/topics', AdminTopics::class)->name('topics')
        ->can('super-admin');
            Route::get('/courses', AdminCourses::class)->name('courses')
        ->can('super-admin');
            Route::get('/orders', AdminOrders::class)->name('orders')
        ->can('super-admin');
            Route::get('/products', \App\Livewire\AdminProducts::class)->name('products')
        ->can('super-admin');
            Route::get('/courses/{id}/build', AdminCourseBuilder::class)->name('courses.build')
        ->can('super-admin');
            Route::get('/challenges', AdminChallenges::class)->name('challenges')
        ->can('super-admin');
            Route::get('/events', AdminEvents::class)->name('events')
        ->can('super-admin');
            Route::get('/cot-review', AdminCotReview::class)->name('cot')
        ->can('super-admin');
            Route::get('/reports', AdminReports::class)->name('reports')
        ->can('super-admin');
            Route::get('/feedbacks', AdminFeedbacks::class)->name('feedbacks')
        ->can('super-admin');
            Route::get('/users', AdminUsers::class)->name('users')
        ->can('super-admin');
            Route::get('/settings', AdminSettings::class)->name('settings')
        ->can('super-admin');
            Route::get('/login-logs', AdminLoginLogs::class)->name('login-logs')
        ->can('super-admin');
            Route::get('/recruitment', AdminRecruitment::class)->name('recruitment')
                ->can('super-admin');
            Route::get('/tool-licenses', AdminToolLicenses::class)->name('tool-licenses')
                ->can('super-admin');
        });
        Route::get('/@{username}',       ProfilePage::class)->name('profile');
        Route::get('/u/{id}',            function ($id) {
            $user = \App\Models\User::findOrFail($id);
            return redirect()->route('profile', $user->username ?? $user->id);
        })->name('profile.id');
    });
});
