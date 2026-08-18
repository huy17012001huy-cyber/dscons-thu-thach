<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ImpersonationController extends Controller
{
    public function start(Request $request, int $user)
    {
        $target = User::findOrFail($user);
        $admin = Auth::user();

        abort_if($target->id === $admin->id, 403, 'Không thể đóng vai chính mình.');
        abort_if($target->is_admin, 403, 'Không thể đóng vai admin khác.');
        abort_if($request->session()->has('impersonator_id'), 409, 'Đang đóng vai user khác — dừng trước đã.');

        Log::info('impersonation.start', [
            'admin_id' => $admin->id,
            'admin_email' => $admin->email,
            'target_id' => $target->id,
            'target_email' => $target->email,
            'ip' => $request->ip(),
            'ua' => $request->userAgent(),
        ]);

        $request->session()->put('impersonator_id', $admin->id);
        Auth::login($target);
        $request->session()->migrate(true);

        return redirect()->route('feed');
    }

    public function stop(Request $request)
    {
        $adminId = $request->session()->get('impersonator_id');
        abort_unless($adminId, 403, 'Không có phiên đóng vai nào để dừng.');

        $admin = User::find($adminId);

        Log::info('impersonation.stop', [
            'admin_id' => $adminId,
            'admin_email' => $admin?->email,
            'target_id' => Auth::id(),
            'target_email' => Auth::user()?->email,
            'ip' => $request->ip(),
        ]);

        $request->session()->forget('impersonator_id');

        if (!$admin) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')
                ->with('error', 'Tài khoản admin gốc không còn tồn tại.');
        }

        // Defense-in-depth: nếu admin gốc bị revoke quyền trong lúc impersonating,
        // không cho login về account đó nữa.
        if (!$admin->is_admin) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')
                ->with('error', 'Tài khoản admin gốc đã bị thu hồi quyền.');
        }

        Auth::login($admin);
        $request->session()->migrate(true);

        return redirect()->route('admin.users')
            ->with('success', 'Đã dừng đóng vai.');
    }
}
