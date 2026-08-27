<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Auth\AdminSessionService;
use App\Core\Auth\AdminTwoFactorService;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class AdminSecurityController extends Controller
{
    public function __construct(
        private readonly AdminSessionService $sessions,
        private readonly AdminTwoFactorService $twoFactor,
    ) {}

    public function show(Request $request): View
    {
        $user = $request->user();
        abort_unless($user instanceof User && $user->isSuperAdmin(), 403);

        return view('pages.admin-security', [
            'user' => $user,
            'setup' => $request->session()->get('admin_2fa_setup'),
            'sessions' => $this->sessions->activeFor($user),
        ]);
    }

    public function prepare(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User && $user->isSuperAdmin(), 403);

        $request->session()->put('admin_2fa_setup', $this->twoFactor->prepare($user));

        return redirect()->route('admin.security');
    }

    public function confirm(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User && $user->isSuperAdmin(), 403);
        $setup = $request->session()->get('admin_2fa_setup');
        abort_unless(is_array($setup), 422, 'Hãy tạo mã xác thực mới trước.');

        $input = $request->validate(['code' => ['required', 'string', 'min:6', 'max:12']]);
        $confirmed = $this->twoFactor->confirm($user, (string) $setup['secret'], $input['code'], $setup['recovery_codes']);

        if (! $confirmed) {
            return back()->withErrors(['code' => 'Mã từ ứng dụng xác thực chưa đúng.']);
        }

        $request->session()->forget('admin_2fa_setup');
        $request->session()->put('admin_2fa_verified_at', now()->toIso8601String());

        return redirect()->route('admin.security')->with('success', 'Đã bật xác thực hai lớp. Hãy lưu recovery code ở nơi an toàn.');
    }

    public function revokeOtherSessions(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User && $user->isSuperAdmin(), 403);

        $this->sessions->revokeOther($user, $request->session()->getId());

        return back()->with('success', 'Đã thu hồi các phiên đăng nhập khác.');
    }
}
