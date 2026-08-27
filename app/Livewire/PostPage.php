<?php

namespace App\Livewire;

use App\Models\Bookmark;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use App\Notifications\GenericNotification;
use App\Services\XpService;
use App\Support\PostContentRenderer;
use Illuminate\Contracts\View\View;
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

        abort_unless($this->post instanceof Post, 404);
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

        $post = $this->post;
        $actor = $this->currentUser();
        $this->validate(['newComment' => 'required|max:2000']);

        $recentCount = Comment::where('user_id', $actor->id)
            ->where('created_at', '>=', now()->subHour())
            ->count();

        if ($recentCount >= 20) {
            $this->addError('newComment', 'Bạn đã bình luận quá nhiều. Vui lòng đợi.');

            return;
        }

        $parentId = $this->replyToId
            ? $post->allComments()->whereKey($this->replyToId)->value('id')
            : null;

        $comment = $post->allComments()->create([
            'user_id' => $actor->id,
            'parent_id' => $parentId,
            'content' => $this->newComment,
        ]);

        $wonRune = false;
        if ($post->isRuneActive()) {
            $affected = Post::whereKey($post->id)
                ->whereNull('rune_first_comment_user_id')
                ->update(['rune_first_comment_user_id' => $actor->id]);
            $wonRune = $affected > 0;

            if ($wonRune) {
                $comment->update(['is_rune_winner' => true]);
            }
        }

        app(XpService::class)->award(
            $actor,
            'comment',
            $wonRune ? 2.0 : 1.0,
            $wonRune ? 'Phù văn 2x EXP' : null,
            $comment,
        );

        $owner = $post->user;
        abort_unless($owner instanceof User, 500);
        if ($owner->id !== $actor->id) {
            $alreadyCommented = $this->post->allComments()
                ->where('user_id', Auth::id())
                ->whereKeyNot($comment->id)
                ->exists();

            if (! $alreadyCommented) {
                app(XpService::class)->award(
                    $owner,
                    'post_commented',
                    1.0,
                    $actor->name.' bình luận bài viết',
                    $this->post,
                );
            }

            $owner->notify(new GenericNotification(
                '💬',
                $actor->name.' bình luận bài viết của bạn',
                null,
                $post->id,
            ));
        }

        $this->notifyMentions($comment->content, [$actor->id, $owner->id]);

        if (! is_string($post->slug)) {
            return;
        }

        $slug = $post->slug;
        $this->loadPost($slug);
    }

    /** @param array<int> $excludeIds */
    private function notifyMentions(string $content, array $excludeIds): void
    {
        $actor = $this->currentUser();
        $post = $this->post;
        abort_unless($post instanceof Post, 500);

        preg_match_all('/@([a-zA-Z0-9._-]+)/', $content, $matches);
        $usernames = array_unique($matches[1]);

        if ($usernames === []) {
            return;
        }

        $users = User::whereIn('username', $usernames)
            ->whereNotIn('id', $excludeIds)
            ->get();

        foreach ($users as $user) {
            $user->notify(new GenericNotification(
                '@',
                $actor->name.' đã nhắc đến bạn trong một bình luận',
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

        $post = $this->post;
        $actor = $this->currentUser();

        DB::transaction(function () use ($post, $actor): void {
            $like = Like::withTrashed()
                ->where('likeable_type', Post::class)
                ->where('likeable_id', $post->id)
                ->where('user_id', $actor->id)
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
                    'likeable_id' => $post->id,
                    'user_id' => $actor->id,
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

        $post = $this->post;
        $actor = $this->currentUser();
        $bookmark = Bookmark::query()
            ->where('user_id', $actor->id)
            ->where('post_id', $post->id)
            ->first();

        if ($bookmark) {
            $bookmark->delete();
            $this->isBookmarked = false;

            return;
        }

        Bookmark::create(['user_id' => $actor->id, 'post_id' => $post->id]);
        $this->isBookmarked = true;
    }

    public function renderedPostContent(): string
    {
        return $this->post ? app(PostContentRenderer::class)->renderPost($this->post) : '';
    }

    private function currentUser(): User
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 403);

        return $user;
    }

    public function render(): View
    {
        return view('livewire.post-page')
            ->layout('layouts.app', ['title' => ($this->post?->title ?: 'Bài viết').' — '.brand()->name]);
    }
}
