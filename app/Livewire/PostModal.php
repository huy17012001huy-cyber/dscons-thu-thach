<?php

namespace App\Livewire;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Notifications\GenericNotification;
use App\Services\XpService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class PostModal extends Component
{
    public ?Post $post = null;

    public bool $show = false;

    public string $newComment = '';

    public ?int $replyToId = null;

    public ?string $replyToName = null;

    /** @var array<int, array{username:string,name:string,avatar_url:string}> */
    public array $mentionResults = [];

    #[On('open-post')]
    public function openPost(int $postId): void
    {
        // Performance: Optimized query to pre-fetch all counts and user-specific states
        // to avoid N+1 queries during modal render. Deep eager load for nested comments.
        $this->post = Post::with(['user.daKhongCuc', 'topic', 'images'])
            ->withCount(['likes', 'allComments'])
            ->withExists(['likes' => fn ($q) => $q->where('user_id', auth()->id())])
            ->withExists(['bookmarks' => fn ($q) => $q->where('user_id', auth()->id())])
            ->with([
                'allComments' => fn ($q) => $q->with(['user.daKhongCuc', 'replies.user.daKhongCuc'])
                    ->withCount('likes')
                    ->withExists(['likes' => fn ($q) => $q->where('user_id', auth()->id())])
                    ->oldest(),
            ])
            ->find($postId);
        $this->show = $this->post !== null;
        $this->resetComposer();
    }

    public function close(): void
    {
        $this->show = false;
        $this->post = null;
        $this->resetComposer();
    }

    public function replyTo(int $commentId, string $name): void
    {
        $this->replyToId = $commentId;
        $this->replyToName = $name;
    }

    public function cancelReply(): void
    {
        $this->replyToId = null;
        $this->replyToName = null;
    }

    public function searchMentions(string $query): void
    {
        $query = trim($query);
        if ($query === '') {
            $this->mentionResults = [];
            return;
        }

        $this->mentionResults = User::query()
            ->where(fn ($q) => $q
                ->where('username', 'ilike', $query.'%')
                ->orWhere('name', 'ilike', '%'.$query.'%'))
            ->whereKeyNot(Auth::id())
            ->orderByRaw("CASE WHEN username ILIKE ? THEN 0 ELSE 1 END", [$query.'%'])
            ->limit(5)
            ->get(['id', 'username', 'name', 'avatar'])
            ->map(fn ($u) => [
                'username' => $u->username,
                'name' => $u->name,
                'avatar_url' => $u->avatar_url,
            ])
            ->toArray();
    }

    public function clearMentions(): void
    {
        $this->mentionResults = [];
    }

    public function addComment(): void
    {
        if (! Auth::check() || ! $this->post || blank($this->newComment)) {
            return;
        }

        $this->validate(['newComment' => 'required|max:2000']);

        $recentCount = Comment::where('user_id', Auth::id())
            ->where('created_at', '>=', now()->subHour())->count();
        if ($recentCount >= 20) {
            $this->addError('newComment', 'Bạn đã bình luận quá nhiều. Vui lòng đợi.');
            return;
        }

        $comment = $this->post->allComments()->create([
            'user_id' => Auth::id(),
            'parent_id' => $this->replyToId,
            'content' => $this->newComment,
        ]);

        $wonRune = false;
        if ($this->post->isRuneActive()) {
            $affected = Post::where('id', $this->post->id)
                ->whereNull('rune_first_comment_user_id')
                ->update(['rune_first_comment_user_id' => Auth::id()]);
            $wonRune = $affected > 0;
            if ($wonRune) {
                $comment->update(['is_rune_winner' => true]);
            }
        }

        app(XpService::class)->award(
            Auth::user(), 'comment', $wonRune ? 2.0 : 1.0,
            $wonRune ? 'Phù văn 2x EXP' : null, $comment
        );

        $owner = $this->post->user;
        if ($owner->id !== Auth::id()) {
            $alreadyCommented = $this->post->allComments()
                ->where('user_id', Auth::id())
                ->where('id', '!=', $comment->id)
                ->exists();
            if (! $alreadyCommented) {
                app(XpService::class)->award($owner, 'post_commented', 1.0, Auth::user()->name.' bình luận bài viết', $this->post);
            }
            $owner->notify(new GenericNotification('💬', Auth::user()->name.' bình luận bài viết của bạn', null, $this->post->id));
        }

        $this->notifyMentions($comment->content, [Auth::id(), $owner->id]);

        $this->resetComposer();
        $this->openPost($this->post->id);
    }

    /**
     * Parse @username mentions, notify each unique tagged user (excluding already-notified IDs).
     *
     * @param  array<int>  $excludeIds
     */
    private function notifyMentions(string $content, array $excludeIds): void
    {
        preg_match_all('/@([a-zA-Z0-9._-]+)/', $content, $m);
        $usernames = array_unique($m[1] ?? []);
        if (empty($usernames)) return;

        $users = User::whereIn('username', $usernames)
            ->whereNotIn('id', $excludeIds)
            ->get();

        foreach ($users as $u) {
            $u->notify(new GenericNotification(
                '@',
                Auth::user()->name.' đã nhắc đến bạn trong một bình luận',
                null,
                $this->post->id
            ));
        }
    }

    private function resetComposer(): void
    {
        $this->newComment = '';
        $this->replyToId = null;
        $this->replyToName = null;
        $this->mentionResults = [];
    }

    public function render()
    {
        return view('livewire.post-modal');
    }
}
