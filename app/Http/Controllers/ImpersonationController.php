<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class ImpersonationController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    private function currentUser(): User
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 403);

        return $user;
    }

    public function start(Request $request, int $user): RedirectResponse
    {
        $target = User::findOrFail($user);
        $admin = $this->currentUser();

        abort_if($target->id === $admin->id, 403, 'Không thể đóng vai chính mình.');
        abort_if($target->is_admin, 403, 'Không thể đóng vai admin khác.');
        abort_if($request->session()->has('impersonator_id'), 409, 'Đang đóng vai user khác — dừng trước đã.');

        $request->session()->put('impersonator_id', $admin->id);
        Auth::login($target);
        $request->session()->migrate(true);
        $this->audit->record('security', 'impersonation_started', $admin, $target);

        return redirect()->route('feed');
    }

    public function stop(Request $request): RedirectResponse
    {
        $adminId = $request->session()->get('impersonator_id');
        abort_unless($adminId, 403, 'Không có phiên đóng vai nào để dừng.');

        abort_unless(is_int($adminId) || ctype_digit((string) $adminId), 403);
        $admin = User::find((int) $adminId);

        $target = Auth::user();

        $request->session()->forget('impersonator_id');

        if (! $admin) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', 'Tài khoản admin gốc không còn tồn tại.');
        }

        // Defense-in-depth: nếu admin gốc bị revoke quyền trong lúc impersonating,
        // không cho login về account đó nữa.
        if (! $admin->isSuperAdmin()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', 'Tài khoản admin gốc đã bị thu hồi quyền.');
        }

        Auth::login($admin);
        $request->session()->migrate(true);
        $this->audit->record('security', 'impersonation_stopped', $admin, $target instanceof User ? $target : null);

        return redirect()->route('admin.users')
            ->with('success', 'Đã dừng đóng vai.');
    }
}
