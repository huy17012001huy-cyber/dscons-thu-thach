<?php
namespace App\Livewire;
use App\Models\User;
use App\Models\XpTransaction;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;
class LeaderboardPage extends Component {
    #[Url] public string $period = 'week';
    public function setPeriod(string $p) { $this->period = $p; }
    public function render() {
        $top = match($this->period) {
            'week'    => Cache::remember('leaderboard.week', 300, fn() => $this->leaderboardByPeriod(now()->startOfWeek())),
            'month'   => Cache::remember('leaderboard.month', 300, fn() => $this->leaderboardByPeriod(now()->startOfMonth())),
            'alltime' => Cache::remember('leaderboard.alltime', 300, fn() => User::orderByDesc('xp')->take(50)->get()),
            'da'      => Cache::remember('leaderboard.da', 300, fn() => User::select('users.*')
                ->join('da_khong_cuc', 'da_khong_cuc.user_id', '=', 'users.id')
                ->orderByDesc('da_khong_cuc.total_count')
                ->take(50)->get()),
            default   => Cache::remember('leaderboard.alltime', 300, fn() => User::orderByDesc('xp')->take(50)->get()),
        };
        $myRank = null;
        if (auth()->check()) {
            $myRank = $top->search(fn($u)=>$u->id === auth()->id());
            if ($myRank === false) $myRank = null;
        }
        return view('livewire.leaderboard-page', ['top' => $top, 'myRank' => $myRank])
            ->layout('layouts.app', ['title' => 'Leaderboard — DSCons AI-BIM Lab']);
    }

    private function leaderboardByPeriod(\Carbon\Carbon $since) {
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
