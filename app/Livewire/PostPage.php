<?php

namespace App\Livewire;

use App\Models\Comment;
use App\Models\Bookmark;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use App\Notifications\GenericNotification;
use App\Services\XpService;
use App\Support\PostContentRenderer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

/**
 * Standalone post detail page.
 *
 * This intentionally does not extend PostModal. A post URL must render a
 * document page, so it must not inherit modal state or the open-post event
 * listener used by the legacy component.
 */
class PostPage extends Component
{
    public ?Post $post = null;

    public string $newComment = '';

    public ?int $replyToId = null;

    public ?string $replyToName = null;

    /** @var array<int, array{username:string,name:string,avatar_url:string}> */
    public array $mentionResults = [];

    public bool $isLiked = false;

    public bool $isBookmarked = false;

    public int $likesCount = 0;

    public function mount(string $slug): void
    {
        $this->loadPost($slug);
    }

    private function loadPost(string $slug): void
    {
        $this->post = Post::with([
            'user.daKhongCuc', 'topic', 'subject', 'postType', 'images',
            'allComments' => fn ($query) => $query
                ->with(['user.daKhongCuc', 'replies.user.daKhongCuc'])
                ->oldest(),
        ])
            ->withCount(['likes', 'allComments'])
            ->withExists(['likes' => fn ($query) => $query->where('user_id', Auth::id())])
            ->withExists(['bookmarks' => fn ($query) => $query->where('user_id', Auth::id())])
            ->where('brand_id', brand()->id)
            ->where('slug', $slug)
            ->first();

        abort_unless($this->post, 404);
        $this->likesCount = (int) $this->post->likes_count;
        $this->isLiked = (bool) $this->post->likes_exists;
        $this->isBookmarked = (bool) $this->post->bookmarks_exists;
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

    public function addComment(): void
    {
        if (! Auth::check() || ! $this->post || blank($this->newComment)) {
            return;
        }

        $this->validate(['newComment' => 'required|max:2000']);

        $recentCount = Comment::where('user_id', Auth::id())
            ->where('created_at', '>=', now()->subHour())
            ->count();

        if ($recentCount >= 20) {
            $this->addError('newComment', 'Bạn đã bình luận quá nhiều. Vui lòng đợi.');
            return;
        }

        $parentId = $this->replyToId
            ? $this->post->allComments()->whereKey($this->replyToId)->value('id')
            : null;

        $comment = $this->post->allComments()->create([
            'user_id' => Auth::id(),
            'parent_id' => $parentId,
            'content' => $this->newComment,
        ]);

        $wonRune = false;
        if ($this->post->isRuneActive()) {
            $affected = Post::whereKey($this->post->id)
                ->whereNull('rune_first_comment_user_id')
                ->update(['rune_first_comment_user_id' => Auth::id()]);
            $wonRune = $affected > 0;

            if ($wonRune) {
                $comment->update(['is_rune_winner' => true]);
            }
        }

        app(XpService::class)->award(
            Auth::user(),
            'comment',
            $wonRune ? 2.0 : 1.0,
            $wonRune ? 'Phù văn 2x EXP' : null,
            $comment,
        );

        $owner = $this->post->user;
        if ($owner->id !== Auth::id()) {
            $alreadyCommented = $this->post->allComments()
                ->where('user_id', Auth::id())
                ->whereKeyNot($comment->id)
                ->exists();

            if (! $alreadyCommented) {
                app(XpService::class)->award(
                    $owner,
                    'post_commented',
                    1.0,
                    Auth::user()->name.' bình luận bài viết',
                    $this->post,
                );
            }

            $owner->notify(new GenericNotification(
                '💬',
                Auth::user()->name.' bình luận bài viết của bạn',
                null,
                $this->post->id,
            ));
        }

        $this->notifyMentions($comment->content, [Auth::id(), $owner->id]);

        $slug = $this->post->slug;
        $this->loadPost($slug);
    }

    private function notifyMentions(string $content, array $excludeIds): void
    {
        preg_match_all('/@([a-zA-Z0-9._-]+)/', $content, $matches);
        $usernames = array_unique($matches[1] ?? []);

        if ($usernames === []) {
            return;
        }

        $users = User::whereIn('username', $usernames)
            ->whereNotIn('id', $excludeIds)
            ->get();

        foreach ($users as $user) {
            $user->notify(new GenericNotification(
                '@',
                Auth::user()->name.' đã nhắc đến bạn trong một bình luận',
                null,
                $this->post->id,
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

    public function toggleLike(): void
    {
        if (! Auth::check() || ! $this->post) {
            return;
        }

        DB::transaction(function (): void {
            $like = Like::withTrashed()
                ->where('likeable_type', Post::class)
                ->where('likeable_id', $this->post->id)
                ->where('user_id', Auth::id())
                ->lockForUpdate()
                ->first();

            if ($like && ! $like->trashed()) {
                $like->delete();
                $this->likesCount = max(0, $this->likesCount - 1);
                $this->isLiked = false;
                return;
            }

            if ($like) {
                $like->restore();
            } else {
                Like::create([
                    'likeable_type' => Post::class,
                    'likeable_id' => $this->post->id,
                    'user_id' => Auth::id(),
                ]);
            }

            $this->likesCount++;
            $this->isLiked = true;
        });
    }

    public function toggleBookmark(): void
    {
        if (! Auth::check() || ! $this->post) {
            return;
        }

        $bookmark = Bookmark::query()
            ->where('user_id', Auth::id())
            ->where('post_id', $this->post->id)
            ->first();

        if ($bookmark) {
            $bookmark->delete();
            $this->isBookmarked = false;
            return;
        }

        Bookmark::create(['user_id' => Auth::id(), 'post_id' => $this->post->id]);
        $this->isBookmarked = true;
    }

    public function renderedPostContent(): string
    {
        return $this->post ? app(PostContentRenderer::class)->renderPost($this->post) : '';
    }

    public function render()
    {
        return view('livewire.post-page')
            ->layout('layouts.app', ['title' => ($this->post?->title ?: 'Bài viết').' — '.brand()->name]);
    }
}
