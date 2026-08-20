<?php

namespace App\Livewire;

use App\Models\Brand;
use App\Models\Course;
use App\Models\Expedition;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CommunityPreview extends Component
{
    public Brand $community;

    public function mount(Brand $community): void
    {
        abort_unless($community->status === 'active', 404);
        $this->community = $community;
    }

    public function join(): void
    {
        abort_unless(Auth::check(), 403);

        $user = Auth::user();
        $user->brandRoles()->syncWithoutDetaching([$this->community->id => ['role' => 'member']]);
        \App\Models\Membership::withoutGlobalScopes()->updateOrCreate(
            ['brand_id' => $this->community->id, 'user_id' => $user->id],
            ['status' => 'active', 'tier' => 'free', 'starts_at' => now(), 'expires_at' => now()->addYears(10)]
        );

        $this->redirect(community_route('feed'), navigate: true);
    }

    public function render()
    {
        $courses = Course::query()->where('is_published', true)->latest()->limit(3)->get();
        $challenges = Expedition::query()->whereIn('status', ['open', 'active'])->latest()->limit(3)->get();
        $memberCount = $this->community->users()->count();

        return view('livewire.community-preview', compact('courses', 'challenges', 'memberCount'))
            ->layout('layouts.app', ['title' => $this->community->name]);
    }
}
