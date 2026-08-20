<?php

namespace App\Livewire;

use App\Models\Brand;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CommunitiesPage extends Component
{
    public string $search = '';

    public function join(int $brandId): void
    {
        abort_unless(Auth::check(), 403);

        $community = Brand::query()->where('status', 'active')->findOrFail($brandId);
        $user = Auth::user();

        $user->brandRoles()->syncWithoutDetaching([$community->id => ['role' => 'member']]);
        \App\Models\Membership::withoutGlobalScopes()->updateOrCreate(
            ['brand_id' => $community->id, 'user_id' => $user->id],
            ['status' => 'active', 'tier' => 'free', 'starts_at' => now(), 'expires_at' => now()->addYears(10)]
        );

        $this->dispatch('toast', message: 'Đã tham gia '.$community->name.'.', type: 'success');
    }

    public function render()
    {
        $query = Brand::query()->where('status', 'active')->withCount('users')->with('owner:id,name');
        if (filled($this->search)) {
            $query->where(fn ($q) => $q->where('name', 'like', '%'.$this->search.'%')
                ->orWhere('tagline', 'like', '%'.$this->search.'%'));
        }

        $communities = $query->orderByDesc('users_count')->orderBy('name')->get();
        $joinedIds = collect();

        if (Auth::check()) {
            $joinedIds = Auth::user()->memberships()
                ->withoutGlobalScopes()
                ->where(function ($membership) {
                    $membership
                        ->where(function ($active) {
                            $active->where('status', 'active')
                                ->where(fn ($expiry) => $expiry->whereNull('expires_at')->orWhere('expires_at', '>', now()));
                        })
                        ->orWhere(function ($trial) {
                            $trial->where('status', 'trial')->where('trial_ends_at', '>', now());
                        });
                })
                ->pluck('brand_id')
                ->filter()
                ->unique()
                ->values();
        }

        return view('livewire.communities-page', compact('communities', 'joinedIds'))
            ->layout('layouts.app', ['title' => 'Khám phá cộng đồng']);
    }
}
