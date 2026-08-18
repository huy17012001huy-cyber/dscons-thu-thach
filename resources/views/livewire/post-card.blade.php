<div class="post-thread-row {{ $post->is_cot ? 'is-cot' : '' }} {{ $post->is_signal ? 'is-signal' : '' }}"
     id="post-{{ $post->id }}"
     wire:click="$dispatch('open-post', { postId: {{ $post->id }} })">
    {{-- Left: Author avatar --}}
    <img src="{{ $post->user->avatar_url }}" style="width:40px; height:40px; border-radius:50%; object-fit:cover; flex-shrink:0;" alt="">

    {{-- Middle: Content --}}
    <div style="flex:1; min-width:0;">
        {{-- Author line --}}
        <div style="display:flex; align-items:center; gap:6px; margin-bottom:2px; flex-wrap:wrap;">
            <span style="font-size:0.875rem; font-weight:600; color:var(--text, #1a1a1a);">{{ $post->user->name }}</span>
            <span class="badge badge-class-{{ $post->user->class_color }}" style="font-size:0.6rem;">{{ $post->user->class_emoji }}</span>
            <span class="level-badge">Lv.{{ $post->user->level }}</span>
            @if($post->is_cot)
            <span class="cot-badge" style="font-size:0.6rem;">★ CỐT</span>
            @endif
            <span style="font-size:0.75rem; color:#9ca3af;">{{ $post->created_at->diffForHumans() }}</span>
        </div>

        {{-- Title --}}
        @if($post->title)
        <h3 style="font-size:0.9375rem; font-weight:700; color:var(--text, #1a1a1a); margin-bottom:2px; line-height:1.3; overflow:hidden; text-overflow:ellipsis; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical;">{{ $post->title }}</h3>
        @endif

        {{-- Content preview (2 lines max) --}}
        <p style="font-size:0.8125rem; color:#6b7280; line-height:1.4; margin-bottom:6px; overflow:hidden; text-overflow:ellipsis; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical;">{{ Str::limit(strip_tags($post->content), 180) }}</p>

        {{-- Bottom: stats + topic --}}
        <div style="display:flex; align-items:center; gap:10px; font-size:0.75rem; color:#9ca3af;">
            @if($post->pillar)
            <span class="badge badge-pillar-{{ $post->pillar }}" style="font-size:0.6rem;">{{ $post->pillar_label }}</span>
            @endif
            @if($post->topic)
            <span>{{ $post->topic->emoji }} {{ $post->topic->name }}</span>
            @endif
            <span style="display:flex; align-items:center; gap:3px;">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                {{ $post->likes_count ?? 0 }}
            </span>
            <span style="display:flex; align-items:center; gap:3px;">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                {{ $post->all_comments_count ?? $post->allComments->count() }}
            </span>
        </div>
    </div>

    {{-- Right: Thumbnail --}}
    @php
        $thumbnail = null;
        $thumbRatio = 'square'; // square or wide
        // Check for uploaded images
        if ($post->images->count() > 0) {
            $thumbnail = asset('storage/' . $post->images->first()->path);
        }
        // Check for YouTube URL in content
        elseif (preg_match('#(?:youtube\.com/watch\?v=|youtu\.be/)([a-zA-Z0-9_-]{11})#i', $post->content, $ytMatch)) {
            $thumbnail = 'https://img.youtube.com/vi/' . $ytMatch[1] . '/mqdefault.jpg';
            $thumbRatio = 'wide';
        }
    @endphp
    @if($thumbnail)
    <div style="flex-shrink:0; width:{{ $thumbRatio === 'wide' ? '120px' : '72px' }}; height:72px; border-radius:8px; overflow:hidden;">
        <img src="{{ $thumbnail }}" alt="" style="width:100%; height:100%; object-fit:cover;">
    </div>
    @endif
</div>
