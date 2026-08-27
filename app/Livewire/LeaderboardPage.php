<?php

namespace App\Livewire;

use App\Models\CommunityUserStat;
use App\Models\User;
use App\Models\XpTransaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;

class LeaderboardPage extends Component
{
    #[Url]
    public string $period = 'week';

    public function setPeriod(string $p): void
    {
        $this->period = $p;
    }

    public function render(): View
    {
        $brandId = app()->bound('brand') ? brand()->id : 0;
        $cacheKey = fn (string $period) => "leaderboard:{$brandId}:{$period}";
        $top = match ($this->period) {
            'week' => Cache::remember($cacheKey('week'), 300, fn () => $this->leaderboardByPeriod(now()->startOfWeek())),
            'month' => Cache::remember($cacheKey('month'), 300, fn () => $this->leaderboardByPeriod(now()->startOfMonth())),
            'alltime' => Cache::remember($cacheKey('alltime'), 300, fn () => CommunityUserStat::query()->with('user')->orderByDesc('xp')->take(50)->get()->map(function ($stat) {
                $user = $stat->user;
                if ($user) {
                    $user->setAttribute('xp', $stat->xp);
                    $user->setAttribute('level', $stat->level);
                }

                return $user;
            })->filter()->values()),
            'da' => Cache::remember($cacheKey('da'), 300, fn () => User::select('users.*')
                ->join('da_khong_cuc', 'da_khong_cuc.user_id', '=', 'users.id')
                ->when($brandId, fn ($q) => $q->where('da_khong_cuc.brand_id', $brandId))
                ->orderByDesc('da_khong_cuc.total_count')
                ->take(50)->get()),
            default => Cache::remember($cacheKey('alltime'), 300, fn () => CommunityUserStat::query()->with('user')->orderByDesc('xp')->take(50)->get()->pluck('user')->filter()->values()),
        };
        $myRank = null;
        if (auth()->check()) {
            $myRank = $top->search(fn ($u) => $u->id === auth()->id());
            if ($myRank === false) {
                $myRank = null;
            }
        }

        return view('livewire.leaderboard-page', ['top' => $top, 'myRank' => $myRank])
            ->layout('layouts.app', ['title' => 'Bảng xếp hạng — '.brand()->name]);
    }

    /** @return Collection<int, User> */
    private function leaderboardByPeriod(Carbon $since): Collection
    {
        $xp = XpTransaction::where('created_at', '>=', $since)
            ->select('user_id', DB::raw('SUM(amount) as period_xp'))
            ->groupBy('user_id')
            ->orderByDesc('period_xp')
            ->take(50)
            ->get();

        $users = User::whereIn('id', $xp->pluck('user_id'))->get()->keyBy('id');

        return $xp->map(function ($row) use ($users) {
            $user = $users[$row->user_id] ?? null;
            if ($user) {
                $user->period_xp = $row->period_xp;
            }

            return $user;
        })->filter()->values();
    }
}
