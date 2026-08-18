<?php
namespace App\Livewire;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
class SidebarClassRatio extends Component {
    public function render() {
        $data = Cache::remember('sidebar_class_ratio', 600, function () {
            $counts = User::query()
                ->join('memberships', 'memberships.user_id', '=', 'users.id')
                ->whereIn('memberships.status', ['active', 'trial'])
                ->whereNotNull('users.class')
                ->select('users.class', DB::raw('count(*) as c'))
                ->groupBy('users.class')
                ->pluck('c', 'users.class');

            $total = (int) $counts->sum();
            $ratios = [];
            foreach (['offer_architect','traffic_mage','conversion_ranger','delivery_assassin','continuity_captain'] as $class) {
                $count = (int) ($counts[$class] ?? 0);
                $ratios[$class] = ['count' => $count, 'pct' => $total > 0 ? round($count / $total * 100) : 0];
            }
            return ['ratios' => $ratios, 'total' => $total];
        });

        return view('livewire.sidebar-class-ratio', $data);
    }
}
