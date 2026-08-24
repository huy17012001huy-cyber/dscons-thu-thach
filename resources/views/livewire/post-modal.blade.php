<div>
    @if($show && $post)
        <div class="post-reader-backdrop" wire:key="post-modal-{{ $post->id }}" x-data x-on:keydown.escape.window="$wire.close()" wire:click.self="close">
            <section class="post-reader-dialog" role="dialog" aria-modal="true" aria-labelledby="post-reader-title" tabindex="-1" x-init="$nextTick(() => $el.focus())">
                <header class="post-reader-header">
                    <div>
                        <span class="post-reader-kicker">DSCons · Bài viết</span>
                        <h2 id="post-reader-title">Đọc bài viết</h2>
                    </div>
                    <button type="button" class="post-reader-close" wire:click="close" aria-label="Đóng bài viết">
                        <x-icon name="close" size="20" />
                    </button>
                </header>

                <div class="post-reader-scroll">
                    <div class="post-reader-author">
                        <img src="{{ $post->user->avatar_url }}" width="48" height="48" alt="Ảnh đại diện của {{ $post->user->name }}">
                        <div>
                            <a href="{{ route('profile', $post->user->username ?? $post->user->id) }}">{{ $post->user->name }}</a>
                            <div class="post-reader-meta">
                                <span>{{ $post->created_at->diffForHumans() }}</span>
                                @if($post->subject)<span>· {{ $post->subject->name }}</span>@endif
                                @if($post->postType)<span>· {{ $post->postType->name }}</span>@endif
                            </div>
                        </div>
                    </div>

                    @if($post->title)
                        <h1 class="post-reader-title">{{ $post->title }}</h1>
                    @endif

                    <div class="post-reader-content prose-post">{!! $this->renderedPostContent() !!}</div>

                    @if($post->images->count())
                        <div class="post-reader-images">
                            @foreach($post->images as $image)
                                <img src="{{ asset('storage/'.$image->path) }}" width="720" height="420" loading="lazy" alt="Ảnh trong bài viết">
                            @endforeach
                        </div>
                    @endif

                    <div class="post-reader-actions" aria-label="Tương tác bài viết">
                        <button type="button" wire:click="toggleLike" class="post-reader-action {{ $isLiked ? 'is-active' : '' }}" aria-pressed="{{ $isLiked ? 'true' : 'false' }}" aria-label="{{ $isLiked ? 'Bỏ thích' : 'Thích bài viết' }}">
                            <x-icon name="heart" size="18" /><span>{{ $likesCount }}</span>
                        </button>
                        <span class="post-reader-action" aria-label="Số bình luận"><x-icon name="comment" size="18" />{{ $post->all_comments_count }}</span>
                        <button type="button" wire:click="toggleBookmark" class="post-reader-action {{ $isBookmarked ? 'is-active' : '' }}" aria-pressed="{{ $isBookmarked ? 'true' : 'false' }}" aria-label="{{ $isBookmarked ? 'Bỏ lưu' : 'Lưu bài viết' }}"><x-icon name="bookmark" size="18" /></button>
                        <button type="button" class="post-reader-action" x-on:click="navigator.clipboard?.writeText(window.location.href); $dispatch('toast', { message: 'Đã sao chép liên kết.', type: 'success' })" aria-label="Sao chép liên kết"><x-icon name="link" size="18" /></button>
                    </div>

                    <section class="post-reader-comments" aria-labelledby="post-reader-comments-title">
                        <h3 id="post-reader-comments-title">Bình luận ({{ $post->all_comments_count }})</h3>
                        @forelse($post->allComments->whereNull('parent_id') as $comment)
                            <article class="post-reader-comment">
                                <img src="{{ $comment->user->avatar_url }}" width="36" height="36" alt="Ảnh đại diện của {{ $comment->user->name }}">
                                <div class="post-reader-comment-main">
                                    <div class="post-reader-bubble">
                                        <strong>{{ $comment->user->name }}</strong>
                                        <p>{{ $comment->content }}</p>
                                    </div>
                                    <div class="post-reader-comment-meta">
                                        <time>{{ $comment->created_at->diffForHumans() }}</time>
                                        <button type="button" wire:click="replyTo({{ $comment->id }}, @js($comment->user->name))">Trả lời</button>
                                    </div>
                                    @foreach($comment->replies as $reply)
                                        <div class="post-reader-reply">
                                            <img src="{{ $reply->user->avatar_url }}" width="30" height="30" alt="Ảnh đại diện của {{ $reply->user->name }}">
                                            <div class="post-reader-bubble">
                                                <strong>{{ $reply->user->name }}</strong>
                                                <p>{{ $reply->content }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </article>
                        @empty
                            <p class="post-reader-empty">Chưa có bình luận nào.</p>
                        @endforelse

                        @auth
                            @if($replyToName)
                                <div class="post-reader-replying">Đang trả lời <strong>{{ $replyToName }}</strong><button type="button" wire:click="cancelReply">Huỷ</button></div>
                            @endif
                            <form class="post-reader-composer" wire:submit="addComment">
                                <img src="{{ auth()->user()->avatar_url }}" width="36" height="36" alt="Ảnh đại diện của {{ auth()->user()->name }}">
                                <textarea wire:model="newComment" rows="1" aria-label="Viết bình luận" placeholder="Viết bình luận..."></textarea>
                                <button type="submit" class="post-reader-submit">Gửi</button>
                            </form>
                            @error('newComment')<p class="post-reader-error" role="alert">{{ $message }}</p>@enderror
                        @endauth
                    </section>
                </div>
            </section>
        </div>
    @endif

    <style>
        .post-reader-backdrop { position:fixed; inset:0; z-index:2000; display:grid; place-items:center; padding:24px; background:rgba(16,42,59,.48); }
        .post-reader-dialog { width:min(760px,100%); max-height:min(820px,calc(100dvh - 48px)); overflow:hidden; background:#FFFFFF; border:1px solid #C9DFEA; border-radius:20px; box-shadow:0 24px 80px rgba(18,59,89,.22); outline:none; }
        .post-reader-header { position:sticky; top:0; z-index:2; display:flex; align-items:center; justify-content:space-between; gap:1rem; padding:1rem 1.25rem; background:#FFFFFF; border-bottom:1px solid #E7EEF2; }
        .post-reader-kicker { display:block; color:#61798A; font-size:.7rem; font-weight:750; letter-spacing:.04em; text-transform:uppercase; }
        .post-reader-header h2 { margin:.15rem 0 0; color:#102A3B; font-size:1rem; }
        .post-reader-close { display:grid; place-items:center; width:42px; height:42px; border:1px solid #C9DFEA; border-radius:12px; background:transparent; color:#61798A; cursor:pointer; }
        .post-reader-close:hover { background:#DCECF7; color:#125A96; }
        .post-reader-close svg { color:inherit !important; }
        .post-reader-scroll { max-height:calc(min(820px,100dvh - 48px) - 70px); overflow-y:auto; padding:1.25rem; }
        .post-reader-author { display:flex; align-items:center; gap:.75rem; }
        .post-reader-author img, .post-reader-composer > img { border-radius:50%; object-fit:cover; }
        .post-reader-author a { color:#125A96; font-weight:800; text-decoration:none; }
        .post-reader-meta { display:flex; flex-wrap:wrap; gap:.4rem; margin-top:.2rem; color:#61798A; font-size:.75rem; }
        .post-reader-title { margin:1rem 0 .65rem; color:#102A3B; font-size:clamp(1.3rem,3vw,1.9rem); line-height:1.25; }
        .post-reader-content { color:#29485B; font-size:1rem; line-height:1.8; }
        .post-reader-content img { max-width:100%; border-radius:12px; }
        .post-reader-images { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:.6rem; margin-top:1rem; }
        .post-reader-images img { width:100%; height:220px; object-fit:cover; border-radius:12px; }
        .post-reader-actions { display:flex; align-items:center; gap:.25rem; margin-top:1.1rem; padding:.55rem 0; border-top:1px solid #C9DFEA; border-bottom:1px solid #C9DFEA; }
        .post-reader-action { display:inline-flex; align-items:center; gap:.35rem; min-height:42px; padding:.4rem .65rem; border:0; border-radius:10px; background:transparent; color:#61798A; cursor:pointer; }
        .post-reader-action:hover, .post-reader-action.is-active { background:#DCECF7; color:#1F77BE; }
        .post-reader-action svg { color:inherit !important; }
        .post-reader-comments { padding-top:1.2rem; }
        .post-reader-comments h3 { margin:0 0 .75rem; color:#102A3B; font-size:1rem; }
        .post-reader-comment { display:flex; gap:.65rem; padding:.75rem 0; }
        .post-reader-comment > img, .post-reader-reply > img { border-radius:50%; object-fit:cover; flex:0 0 auto; }
        .post-reader-comment-main { min-width:0; flex:1; }
        .post-reader-bubble { padding:.65rem .8rem; border-radius:13px; background:#F1F7FA; }
        .post-reader-bubble strong { display:block; color:#29485B; font-size:.82rem; }
        .post-reader-bubble p { margin:.2rem 0 0; color:#29485B; font-size:.84rem; line-height:1.55; white-space:pre-wrap; overflow-wrap:anywhere; }
        .post-reader-comment-meta { display:flex; gap:.8rem; margin-top:.25rem; padding-left:.3rem; color:#61798A; font-size:.72rem; }
        .post-reader-comment-meta button, .post-reader-replying button { border:0; background:transparent; color:#125A96; cursor:pointer; font:inherit; font-weight:750; }
        .post-reader-reply { display:flex; gap:.5rem; margin:.5rem 0 0 1.25rem; }
        .post-reader-empty { color:#61798A; font-size:.84rem; text-align:center; }
        .post-reader-replying { margin:.8rem 0 .4rem; color:#61798A; font-size:.78rem; }
        .post-reader-composer { display:flex; align-items:flex-end; gap:.55rem; margin-top:1rem; padding-top:1rem; border-top:1px solid #EEE9DF; }
        .post-reader-composer textarea { flex:1; min-height:42px; resize:vertical; border:1px solid #C9DFEA; border-radius:13px; padding:.65rem .75rem; background:#fff; color:#29485B; font:inherit; }
        .post-reader-composer textarea:focus-visible { outline:3px solid rgba(31,119,190,.22); border-color:#1F77BE; }
        .post-reader-submit { min-height:42px; padding:.5rem .9rem; border:1px solid #1F77BE; border-radius:11px; background:#1F77BE; color:#fff; font-weight:800; cursor:pointer; }
        .post-reader-submit:hover { background:#125A96; }
        .post-reader-error { margin:.35rem 0 0 2.6rem; color:#B42318; font-size:.76rem; }
        @media (max-width:640px) { .post-reader-backdrop { align-items:end; padding:0; } .post-reader-dialog { width:100%; max-height:calc(100dvh - 12px); border-radius:18px 18px 0 0; } .post-reader-scroll { max-height:calc(100dvh - 82px); padding:1rem; } }
        @media (prefers-reduced-motion:reduce) { .post-reader-backdrop, .post-reader-dialog { transition:none !important; } }
    </style>
</div>
