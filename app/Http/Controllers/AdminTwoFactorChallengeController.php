<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Auth\AdminTwoFactorService;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

final class AdminTwoFactorChallengeController extends Controller
{
    public function __construct(private readonly AdminTwoFactorService $twoFactor) {}

    public function show(Request $request): View
    {
        $this->pendingUser($request);

        return view('pages.admin-two-factor-challenge');
    }

    public function verify(Request $request): RedirectResponse
    {
        $user = $this->pendingUser($request);
        $key = 'admin-2fa:'.$user->id.'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withErrors(['code' => 'Bạn đã thử quá nhiều lần. Hãy chờ '.RateLimiter::availableIn($key).' giây.']);
        }

        $input = $request->validate([
            'code' => ['nullable', 'string', 'max:12', 'required_without:recovery_code'],
            'recovery_code' => ['nullable', 'string', 'max:32', 'required_without:code'],
        ]);
        $verified = filled($input['code'] ?? null)
            ? $this->twoFactor->verify($user, (string) $input['code'])
            : $this->twoFactor->useRecoveryCode($user, (string) $input['recovery_code']);

        if (! $verified) {
            RateLimiter::hit($key, 300);

            return back()->withErrors(['code' => 'Mã xác thực hoặc recovery code không đúng.']);
        }

        RateLimiter::clear($key);
        $redirect = $request->session()->pull('admin_2fa_redirect', route('admin.dashboard'));
        $request->session()->forget('admin_2fa_pending_user_id');
        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('admin_2fa_verified_at', now()->toIso8601String());

        return redirect()->to($redirect);
    }

    public function verifyCurrent(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User && $user->isSuperAdmin(), 403);
        $key = 'admin-2fa:'.$user->id.'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withErrors(['code' => 'Bạn đã thử quá nhiều lần. Hãy chờ '.RateLimiter::availableIn($key).' giây.']);
        }

        $input = $request->validate([
            'code' => ['nullable', 'string', 'max:12', 'required_without:recovery_code'],
            'recovery_code' => ['nullable', 'string', 'max:32', 'required_without:code'],
        ]);
        $verified = filled($input['code'] ?? null)
            ? $this->twoFactor->verify($user, (string) $input['code'])
            : $this->twoFactor->useRecoveryCode($user, (string) $input['recovery_code']);

        if (! $verified) {
            RateLimiter::hit($key, 300);

            return back()->withErrors(['code' => 'Mã xác thực hoặc recovery code không đúng.']);
        }

        RateLimiter::clear($key);
        $request->session()->put('admin_2fa_verified_at', now()->toIso8601String());

        return redirect()->route('admin.dashboard');
    }

    private function pendingUser(Request $request): User
    {
        $id = $request->session()->get('admin_2fa_pending_user_id');
        $user = is_int($id) || ctype_digit((string) $id) ? User::find((int) $id) : null;

        abort_unless($user instanceof User && $user->isSuperAdmin() && $user->two_factor_confirmed_at !== null, 403);

        return $user;
    }
}
