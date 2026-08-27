<?php

namespace App\Livewire;

use App\Models\LoginLog;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class AdminLoginLogs extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage('logsPage');
        $this->resetPage('sessionsPage');
    }

    public function deviceLabel(?string $ua): string
    {
        if (! $ua) {
            return '—';
        }
        $os = match (true) {
            (bool) preg_match('/Android/i', $ua) => 'Android',
            (bool) preg_match('/iPhone|iPad|iOS/i', $ua) => 'iOS',
            (bool) preg_match('/Windows/i', $ua) => 'Windows',
            (bool) preg_match('/Mac OS X|Macintosh/i', $ua) => 'macOS',
            (bool) preg_match('/Linux/i', $ua) => 'Linux',
            default => 'Khác',
        };
        $br = 'Khác';
        if (preg_match('/Edg\/([0-9]+)/i', $ua, $m)) {
            $br = 'Edge '.$m[1];
        } elseif (preg_match('/Chrome\/([0-9]+)/i', $ua, $m)) {
            $br = 'Chrome '.$m[1];
        } elseif (preg_match('/Firefox\/([0-9]+)/i', $ua, $m)) {
            $br = 'Firefox '.$m[1];
        } elseif (preg_match('/Version\/[0-9.]+.*Safari/i', $ua)) {
            $br = 'Safari';
        }

        return $os.' · '.$br;
    }

    public function render(): View
    {
        $s = trim($this->search);

        // Cách user gõ → match kiểu nào (để hỗ trợ tìm theo fingerprint / device cookie)
        $isFingerprint = (bool) preg_match('/^[a-f0-9]{8,16}$/i', $s);
        $isDeviceCookie = (bool) preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $s);

        $logs = LoginLog::query()
            ->with('user:id,name,username,email')
            ->when($s !== '', function ($q) use ($s, $isFingerprint, $isDeviceCookie) {
                $q->where(function ($w) use ($s, $isFingerprint, $isDeviceCookie) {
                    if ($isFingerprint) {
                        $w->where('fingerprint_hash', $s);
                    } elseif ($isDeviceCookie) {
                        $w->where('device_cookie_id', $s);
                    } else {
                        $w->where('ip_address', 'like', "%{$s}%")
                            ->orWhereHas('user', function ($u) use ($s) {
                                $u->where('name', 'ilike', "%{$s}%")
                                    ->orWhere('username', 'ilike', "%{$s}%")
                                    ->orWhere('email', 'ilike', "%{$s}%");
                            });
                    }
                });
            })
            ->latest('created_at')
            ->paginate(20, ['*'], 'logsPage');

        // Tài khoản đang có session (đăng nhập sẵn) — bắt cả nick chưa "login mới"
        $sessions = DB::table('sessions')
            ->join('users', 'users.id', '=', 'sessions.user_id')
            ->whereNotNull('sessions.user_id')
            ->when($s !== '', function ($q) use ($s) {
                $q->where(function ($w) use ($s) {
                    $w->where('sessions.ip_address', 'like', "%{$s}%")
                        ->orWhere('users.name', 'ilike', "%{$s}%")
                        ->orWhere('users.username', 'ilike', "%{$s}%")
                        ->orWhere('users.email', 'ilike', "%{$s}%");
                });
            })
            ->orderByDesc('sessions.last_activity')
            ->paginate(20, [
                'sessions.ip_address', 'sessions.user_agent', 'sessions.last_activity',
                'users.name', 'users.username', 'users.email', 'users.id as user_id',
            ], 'sessionsPage');

        // Banner counts khi search hữu ích: bao nhiêu account khác nhau / fingerprint khác nhau
        // / device cookie khác nhau trong cùng "thứ" đang tìm. Bỏ via_admin để khỏi nhiễu.
        $distinctAccounts = null;
        $distinctFingerprints = null;
        $distinctDeviceCookies = null;
        $matchType = null; // 'ip' | 'fingerprint' | 'device_cookie'

        if ($s !== '') {
            $base = LoginLog::query()->whereNotNull('user_id')->where('via_admin', false);

            if (filter_var($s, FILTER_VALIDATE_IP)) {
                $matchType = 'ip';
                $base->where('ip_address', $s);
            } elseif ($isFingerprint) {
                $matchType = 'fingerprint';
                $base->where('fingerprint_hash', $s);
            } elseif ($isDeviceCookie) {
                $matchType = 'device_cookie';
                $base->where('device_cookie_id', $s);
            }

            if ($matchType) {
                $rows = (clone $base)->select('user_id', 'fingerprint_hash', 'device_cookie_id')->get();
                $distinctAccounts = $rows->pluck('user_id')->unique()->count();
                $distinctFingerprints = $rows->pluck('fingerprint_hash')->filter()->unique()->count();
                $distinctDeviceCookies = $rows->pluck('device_cookie_id')->filter()->unique()->count();

                // Khi search theo IP, gộp thêm user_id từ sessions để khớp với row "đang đăng nhập"
                if ($matchType === 'ip') {
                    $extra = DB::table('sessions')->where('ip_address', $s)->whereNotNull('user_id')->pluck('user_id');
                    $distinctAccounts = $rows->pluck('user_id')->merge($extra)->unique()->count();
                }
            }
        }

        return view('livewire.admin-login-logs', compact(
            'logs', 'sessions', 'distinctAccounts', 'distinctFingerprints',
            'distinctDeviceCookies', 'matchType', 's'
        ))->layout('layouts.app');
    }
}
