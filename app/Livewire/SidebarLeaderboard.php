<?php
namespace App\Livewire;
use App\Models\Membership;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
class SidebarLeaderboard extends Component {
    public function render() {
        $top = Cache::remember('sidebar_leaderboard', 300, function () {
            $memberIds = Membership::whereIn('status', ['active', 'trial'])->pluck('user_id');
            return User::whereIn('id', $memberIds)
                ->orderByDesc('xp')->take(5)->get();
        });
        return view('livewire.sidebar-leaderboard', ['top' => $top]);
    }
}
