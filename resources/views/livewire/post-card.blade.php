@php($postUrl = community_route('post.show', ['slug' => $post->slug]))

<article class="post-thread-row {{ $post->is_cot ? 'is-cot' : '' }}" id="post-{{ $post->id }}" wire:key="post-card-{{ $post->id }}" x-data x-on:click="if (!$event.target.closest('a,button')) Livewire.dispatch('open-post', { postId: {{ $post->id }} })">
    <div class="post-card-topline">
        <a href="{{ $postUrl }}" wire:click.prevent="$dispatch('open-post', { postId: {{ $post->id }} })" class="post-card-link" style="flex:1 1 auto;min-width:0;" aria-label="Đọc bài viết{{ $post->title ? ': '.$post->title : ' của '.$post->user->name }}">
            <header class="post-card-author">
                <img src="{{ $post->user->avatar_url }}" width="46" height="46" loading="lazy" alt="Ảnh đại diện của {{ $post->user->name }}">
                <div class="post-card-author-copy">
                    <div class="post-card-author-name">
                        <strong>{{ $post->user->name }}</strong>
                        <span class="level-badge level-{{ $post->user->levelBadgeTone() }}" style="--level-accent:{{ $post->user->levelBadgeColor() }};" title="{{ $post->user->job_stage }}" aria-label="Level {{ $post->user->level }} — {{ $post->user->job_stage }}">Lv.{{ $post->user->level }}</span>
                    </div>
                    <time datetime="{{ $post->created_at->toIso8601String() }}">{{ $post->created_at->diffForHumans() }}</time>
                </div>
            </header>
            @if($post->title)
                <h2 class="post-card-title">{{ $post->title }}</h2>
            @endif
        </a>

        <div class="post-card-taxonomy" style="flex:0 0 48%;width:48%;max-width:48%;margin-top:0;align-self:flex-start;justify-content:flex-end;" aria-label="Chủ đề bài viết">
            @if($post->subject)
                <span class="post-topic">{{ $post->subject->slug === 'tieu-chuan' ? 'Thiết kế' : $post->subject->name }}</span>
            @endif
        </div>
    </div>

    <div class="post-card-body" wire:click="$dispatch('open-post', { postId: {{ $post->id }} })" style="cursor:pointer;">
        <div class="post-card-content prose-post">{!! $this->renderContent(false) !!}</div>

        @if($post->images->count())
            <div class="post-card-images" aria-label="Ảnh đính kèm bài viết">
                @foreach($post->images as $image)
                    <img src="{{ asset('storage/'.$image->path) }}" width="720" height="420" loading="lazy" alt="Ảnh đính kèm bài viết">
                @endforeach
            </div>
        @endif

    </div>

    <footer class="post-card-actions" aria-label="Tương tác bài viết">
        <button type="button" wire:click.stop="toggleLike" class="post-card-action {{ $isLiked ? 'is-active' : '' }}" aria-pressed="{{ $isLiked ? 'true' : 'false' }}" aria-label="{{ $isLiked ? 'Bỏ thích' : 'Thích bài viết' }}">
            <x-icon name="heart" size="18" /><span class="post-card-action-label">Thích</span><span class="post-card-action-count">{{ $likesCount }}</span>
        </button>
        <a href="{{ $postUrl }}#comments-title" wire:click.prevent="$dispatch('open-post', { postId: {{ $post->id }} })" class="post-card-action" aria-label="Mở bình luận">
            <x-icon name="comment" size="18" /><span class="post-card-action-label">Bình luận</span><span class="post-card-action-count">{{ $post->all_comments_count ?? 0 }}</span>
        </a>
        <button type="button" wire:click.stop="toggleBookmark" class="post-card-action {{ $isBookmarked ? 'is-active' : '' }}" aria-pressed="{{ $isBookmarked ? 'true' : 'false' }}" aria-label="{{ $isBookmarked ? 'Bỏ lưu' : 'Lưu bài viết' }}">
            <x-icon name="bookmark" size="18" /><span class="post-card-action-label">Lưu</span>
        </button>
        <button type="button" class="post-card-action" x-data x-on:click="navigator.clipboard?.writeText(@js($postUrl)); $dispatch('toast', { message: 'Đã sao chép liên kết.', type: 'success' })" aria-label="Sao chép liên kết">
            <x-icon name="link" size="18" /><span class="post-card-action-label">Copy link</span>
        </button>
    </footer>
</article>
