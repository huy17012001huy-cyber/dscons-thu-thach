@push('head')
    <link rel="canonical" href="{{ community_route('post.show', ['slug' => $post->slug]) }}">
@endpush

<article class="post-page" wire:key="post-page-{{ $post->id }}">
    <style>
        .post-page { max-width: 860px; margin: 0 auto; padding: 1rem 0 4rem; }
        .post-page-shell { background: #FFFFFF; border: 1px solid #C9DFEA; border-radius: 18px; overflow: hidden; }
        .post-page-head { padding: 1.25rem 1.35rem 1rem; border-bottom: 1px solid #EEE9DF; }
        .post-page-author { display:flex; align-items:center; gap:.75rem; }
        .post-page-author img, .post-page-comment img { width:44px; height:44px; border-radius:50%; object-fit:cover; flex-shrink:0; }
        .post-page-meta { display:flex; align-items:center; flex-wrap:wrap; gap:.4rem; color:#61798A; font-size:.78rem; }
        .post-page-title { margin:1rem 0 .6rem; font-size:clamp(1.35rem, 3vw, 2rem); line-height:1.2; color:#102A3B; }
        .post-page-content { padding:1.35rem; color:#29485B; font-size:1rem; line-height:1.8; overflow-wrap:anywhere; }
        .post-page-content img { max-width:100%; border-radius:12px; }
        .post-page-actions { display:flex; gap:.5rem; align-items:center; padding:.75rem 1.35rem; border-top:1px solid #EEE9DF; border-bottom:1px solid #EEE9DF; }
        .post-page-action { min-height:42px; border:0; background:transparent; color:#61798A; border-radius:10px; padding:.45rem .7rem; display:inline-flex; align-items:center; gap:.35rem; cursor:pointer; }
        .post-page-action:hover, .post-page-action.is-active { background:#DCECF7; color:#1F77BE; }
        .post-page-comments { padding:1.25rem 1.35rem; }
        .post-page-comment { display:flex; gap:.65rem; padding:1rem 0; border-bottom:1px solid #EEE9DF; }
        .post-page-comment img { width:34px; height:34px; }
        .post-page-comment-body { min-width:0; flex:1; }
        .post-page-comment-bubble { background:#F1F7FA; border-radius:12px; padding:.65rem .8rem; }
        .post-page-comment-actions { display:flex; gap:.8rem; margin-top:.25rem; font-size:.75rem; color:#71808A; }
        .post-page-comment-actions button { border:0; background:transparent; color:inherit; cursor:pointer; padding:0; }
        .post-page-composer { display:flex; gap:.6rem; align-items:flex-start; margin-top:1rem; }
        .post-page-composer textarea { flex:1; min-height:46px; resize:vertical; border:1px solid #D7D0C3; border-radius:12px; padding:.7rem .8rem; font:inherit; }
        @media (max-width: 640px) { .post-page { padding:0 0 3rem; } .post-page-shell { border-radius:0; border-left:0; border-right:0; } .post-page-head, .post-page-content, .post-page-actions, .post-page-comments { padding-left:1rem; padding-right:1rem; } }
    </style>

    <div class="post-page-shell">
        <header class="post-page-head">
            <div class="post-page-author">
                <img src="{{ $post->user->avatar_url }}" alt="Ảnh đại diện của {{ $post->user->name }}">
                <div>
                    <a href="{{ route('profile', $post->user->username ?? $post->user->id) }}" style="font-weight:700;color:#125A96;text-decoration:none;">{{ $post->user->name }}</a>
                    <div class="post-page-meta">
                        <span>{{ $post->created_at->diffForHumans() }}</span>
                        @if($post->subject)<span>· {{ $post->subject->name }}</span>@endif
                        @if($post->postType)<span>· {{ $post->postType->name }}</span>@endif
                    </div>
                </div>
            </div>
            @if($post->title)<h1 class="post-page-title">{{ $post->title }}</h1>@endif
        </header>

        <div class="post-page-content prose-post">{!! $this->renderedPostContent() !!}</div>

        @if($post->images->count())
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:.6rem;padding:0 1.35rem 1.25rem;">
                @foreach($post->images as $image)
                    <img src="{{ asset('storage/'.$image->path) }}" alt="Ảnh trong bài viết" style="width:100%;max-height:360px;object-fit:cover;border-radius:12px;">
                @endforeach
            </div>
        @endif

        <div class="post-page-actions" aria-label="Tương tác bài viết">
            <button type="button" wire:click="toggleLike" class="post-page-action {{ $isLiked ? 'is-active' : '' }}" aria-pressed="{{ $isLiked ? 'true' : 'false' }}" aria-label="{{ $isLiked ? 'Bỏ thích' : 'Thích bài viết' }}">
                <x-icon name="heart" size="18" /> <span>{{ $likesCount }}</span>
            </button>
            <span class="post-page-action" aria-label="Số bình luận"><x-icon name="comment" size="18" /> {{ $post->all_comments_count }}</span>
            <button type="button" wire:click="toggleBookmark" class="post-page-action {{ $isBookmarked ? 'is-active' : '' }}" aria-pressed="{{ $isBookmarked ? 'true' : 'false' }}" aria-label="{{ $isBookmarked ? 'Bỏ lưu' : 'Lưu bài viết' }}"><x-icon name="bookmark" size="18" /></button>
            <button type="button" class="post-page-action" x-data x-on:click="navigator.clipboard?.writeText(window.location.href); $dispatch('toast', { message: 'Đã sao chép liên kết.', type: 'success' })" aria-label="Chia sẻ bài viết"><x-icon name="share" size="18" /> Chia sẻ</button>
        </div>

        <section class="post-page-comments" aria-labelledby="comments-title">
            <h2 id="comments-title" style="font-size:1.05rem;color:#173042;margin:0;">Bình luận ({{ $post->all_comments_count }})</h2>
            @foreach($post->allComments->whereNull('parent_id') as $comment)
                <div class="post-page-comment">
                    <img src="{{ $comment->user->avatar_url }}" alt="Ảnh đại diện của {{ $comment->user->name }}">
                    <div class="post-page-comment-body">
                        <div class="post-page-comment-bubble"><strong style="font-size:.85rem;">{{ $comment->user->name }}</strong><p style="margin:.2rem 0 0;white-space:pre-wrap;">{{ $comment->content }}</p></div>
                        <div class="post-page-comment-actions"><span>{{ $comment->created_at->diffForHumans() }}</span><button type="button" wire:click="replyTo({{ $comment->id }}, '{{ addslashes($comment->user->name) }}')">Trả lời</button></div>
                        @foreach($comment->replies as $reply)
                            <div class="post-page-comment" style="padding:.65rem 0 0;margin-left:1.25rem;border-bottom:0;">
                                <img src="{{ $reply->user->avatar_url }}" alt="Ảnh đại diện của {{ $reply->user->name }}">
                                <div class="post-page-comment-body"><div class="post-page-comment-bubble"><strong style="font-size:.82rem;">{{ $reply->user->name }}</strong><p style="margin:.2rem 0 0;white-space:pre-wrap;">{{ $reply->content }}</p></div></div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            @if($replyToName)<p style="font-size:.8rem;color:#5C6C76;">Đang trả lời {{ $replyToName }} <button type="button" wire:click="cancelReply" style="border:0;background:transparent;color:#B42318;cursor:pointer;">Huỷ</button></p>@endif
            <div class="post-page-composer">
                <img src="{{ auth()->user()->avatar_url }}" alt="Ảnh đại diện của {{ auth()->user()->name }}" style="width:34px;height:34px;border-radius:50%;object-fit:cover;">
                <textarea wire:model="newComment" placeholder="Viết bình luận..." aria-label="Viết bình luận"></textarea>
                <button type="button" wire:click="addComment" class="btn btn-primary">Gửi</button>
            </div>
            @error('newComment')<p class="compose-error" role="alert">{{ $message }}</p>@enderror
        </section>
    </div>
</article>
