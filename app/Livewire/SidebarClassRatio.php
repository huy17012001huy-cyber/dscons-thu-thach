<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

class SidebarClassRatio extends Component
{
    public function render(): View
    {
        $brandId = app()->bound('brand') ? (int) brand()->id : 0;
        $classes = app()->bound('brand') ? brand()->classProfiles() : config('communities.classes.default', []);
        $data = Cache::remember("sidebar_class_ratio:{$brandId}", 600, function () use ($brandId, $classes) {
            $counts = User::query()
                ->join('memberships', 'memberships.user_id', '=', 'users.id')
                ->whereIn('memberships.status', ['active', 'trial'])
                ->when($brandId, fn ($query) => $query->where('memberships.brand_id', $brandId))
                ->whereNotNull('users.class')
                ->select('users.class', DB::raw('count(distinct users.id) as c'))
                ->groupBy('users.class')
                ->pluck('c', 'users.class');

            $total = (int) $counts->sum();
            $ratios = [];
            foreach (array_keys($classes) as $class) {
                $count = (int) ($counts[$class] ?? 0);
                $ratios[$class] = ['count' => $count, 'pct' => $total > 0 ? round($count / $total * 100) : 0];
            }

            return ['ratios' => $ratios, 'total' => $total, 'classes' => $classes];
        });

        return view('livewire.sidebar-class-ratio', $data);
    }
}
