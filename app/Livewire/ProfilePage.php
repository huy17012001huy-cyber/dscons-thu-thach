<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Core\Gamification\XpService;
use App\Models\Bookmark;
use App\Models\Membership;
use App\Models\Post;
use App\Models\User;
use App\Models\XpTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;

class ProfilePage extends Component
{
    public User $profileUser;

    #[Url]
    public string $tab = 'posts';

    public function mount(string $username): void
    {
        $query = User::query()->where('username', $username);
        if (is_numeric($username)) {
            $query->orWhere('id', (int) $username);
        }
        $this->profileUser = $query->with(['daKhongCuc', 'powerSymbols', 'membership'])->firstOrFail();
    }

    public function setTab(string $tab): void
    {
        $this->tab = $tab;
    }

    public function render(): View
    {
        $xpService = app(XpService::class);
        $posts = $this->tab === 'posts'
            ? $this->profileUser->posts()->with(['user.daKhongCuc', 'images', 'topic'])->withCount(['likes', 'allComments'])->withExists(['likes' => fn ($query) => $query->where('user_id', auth()->id())])->withExists(['bookmarks' => fn ($query) => $query->where('user_id', auth()->id())])->latest()->paginate(10)
            : null;
        $cotPosts = $this->tab === 'cot'
            ? $this->profileUser->posts()->where('is_cot', true)->with(['user.daKhongCuc', 'images', 'topic'])->withCount(['likes', 'allComments'])->withExists(['likes' => fn ($query) => $query->where('user_id', auth()->id())])->withExists(['bookmarks' => fn ($query) => $query->where('user_id', auth()->id())])->latest()->paginate(10)
            : null;
        $bookmarkedPosts = ($this->tab === 'bookmarks' && auth()->id() === $this->profileUser->id)
            ? Post::whereIn('id', Bookmark::where('user_id', $this->profileUser->id)->pluck('post_id'))->with(['user.daKhongCuc', 'images', 'topic'])->withCount(['likes', 'allComments'])->withExists(['likes' => fn ($query) => $query->where('user_id', auth()->id())])->withExists(['bookmarks' => fn ($query) => $query->where('user_id', auth()->id())])->latest()->paginate(10)
            : null;
        $currentMembership = Membership::withoutGlobalScopes()->where('user_id', $this->profileUser->id)->where('brand_id', brand()->id)->latest()->first();
        $activeCommunities = $this->profileUser->brandRoles()->wherePivotIn('role', ['member', 'moderator', 'admin', 'owner'])->where('brands.status', 'active')->orderBy('brands.name')->get();
        $since = now()->subWeeks(52)->startOfWeek();
        $contributions = XpTransaction::where('user_id', $this->profileUser->id)->where('created_at', '>=', $since)->selectRaw('DATE(created_at) as date, SUM(amount) as total')->groupBy(DB::raw('DATE(created_at)'))->pluck('total', 'date')->toArray();

        return view('livewire.profile-page', [
            'xpProgress' => $xpService->expProgressPct($this->profileUser),
            'toNext' => $xpService->expToNextLevel($this->profileUser),
            'posts' => $posts,
            'cotPosts' => $cotPosts,
            'bookmarkedPosts' => $bookmarkedPosts,
            'symbols' => $this->profileUser->powerSymbols->keyBy('pillar'),
            'badges' => $this->profileUser->userBadges()->with('badge')->get(),
            'contributions' => $contributions,
            'contributionStart' => $since,
            'currentMembership' => $currentMembership,
            'activeCommunities' => $activeCommunities,
            'commentCount' => $this->profileUser->comments()->count(),
        ])->layout('layouts.app', ['title' => $this->profileUser->name.' — DSCons']);
    }
}
