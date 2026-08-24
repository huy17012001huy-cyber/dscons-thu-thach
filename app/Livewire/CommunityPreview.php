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

        $this->redirect(route('community.feed', ['community' => $this->community->slug]), navigate: true);
    }

    public function render()
    {
        $coursesQuery = Course::withoutGlobalScopes()->where('brand_id', $this->community->id)->where('is_published', true);
        $challengesQuery = Expedition::withoutGlobalScopes()->where('brand_id', $this->community->id)->whereIn('status', ['open', 'active']);
        $courses = (clone $coursesQuery)->withCount('modules')->latest()->limit(3)->get();
        $challenges = (clone $challengesQuery)->latest()->limit(3)->get();
        $memberCount = $this->community->users()->count();
        $adminCount = $this->community->users()->wherePivotIn('role', ['owner', 'admin'])->count();
        $courseCount = (clone $coursesQuery)->count();
        $challengeCount = (clone $challengesQuery)->count();
        $eventCount = \App\Models\Event::withoutGlobalScopes()->where('brand_id', $this->community->id)->whereIn('status', ['published', 'completed'])->count();
        $creator = $this->community->owner;
        if (!$creator) {
            $creator = $this->community->users()->wherePivot('role', 'owner')->first();
        }
        if (!$creator) {
            $creator = $this->community->users()->wherePivot('role', 'admin')->first();
        }
        $isMember = false;
        $canManage = Auth::check() && Auth::user()->isBrandAdmin($this->community->id);

        if (Auth::check()) {
            $isMember = Auth::user()->isCommunityParticipant($this->community->id);
        }

        return view('livewire.community-preview', compact('courses', 'challenges', 'memberCount', 'adminCount', 'courseCount', 'challengeCount', 'eventCount', 'creator', 'isMember', 'canManage'))
            ->layout('layouts.app', ['title' => $this->community->name]);
    }
}
